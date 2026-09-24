<?php
// app/services/roblox/RobloxAssetService.php — download & upload aset Roblox lewat Open Cloud
//
// Dipakai fitur Auto Spoof (re-upload massal gambar / audio ke akun atau grup sendiri).
// API key TIDAK pernah disimpan / di-log — hanya diteruskan ke apis.roblox.com.
//
// Scope API key yang dibutuhkan:
//   - asset:read + asset:write      → upload & cek status operasi
//   - legacy-assets:manage          → download aset dari asset ID (asset-delivery-api)

class RobloxAssetService
{
    private const UPLOAD_URL    = 'https://apis.roblox.com/assets/v1/assets';
    private const OPERATION_URL = 'https://apis.roblox.com/assets/v1/operations/';
    private const DELIVERY_URL  = 'https://apis.roblox.com/asset-delivery-api/v1/assetId/';
    private const LEGACY_URL    = 'https://assetdelivery.roblox.com/v2/assetId/';

    /** Batas ukuran file yang di-download / di-upload (byte) */
    public const MAX_BYTES = 20 * 1024 * 1024;

    /** Host yang boleh di-download (cegah SSRF lewat field "location") */
    private const ALLOWED_HOSTS = ['roblox.com', 'rbxcdn.com'];

    /** Signature file → [kind, assetType Open Cloud, content-type, ekstensi] */
    private const SIGNATURES = [
        ["\x89PNG",  'image', 'Decal', 'image/png',  'png'],
        ["\xFF\xD8\xFF", 'image', 'Decal', 'image/jpeg', 'jpg'],
        ['BM',       'image', 'Decal', 'image/bmp',  'bmp'],
        ['OggS',     'audio', 'Audio', 'audio/ogg',  'ogg'],
        ['fLaC',     'audio', 'Audio', 'audio/flac', 'flac'],
        ['RIFF',     'audio', 'Audio', 'audio/wav',  'wav'],
        ['ID3',      'audio', 'Audio', 'audio/mpeg', 'mp3'],
        ["\xFF\xFB", 'audio', 'Audio', 'audio/mpeg', 'mp3'],
        ["\xFF\xF3", 'audio', 'Audio', 'audio/mpeg', 'mp3'],
        ["\xFF\xF2", 'audio', 'Audio', 'audio/mpeg', 'mp3'],
    ];

    public function __construct(private string $apiKey)
    {
    }

    // ============================================================
    // DOWNLOAD
    // ============================================================

    /**
     * Download isi aset dari asset ID. Decal otomatis di-resolve ke gambar aslinya.
     * @return array{bytes:string, sourceId:string}
     */
    public function download(string $assetId, int $depth = 0): array
    {
        $location = $this->resolveLocation($assetId);
        $bytes    = $this->fetchLocation($location);

        // Decal / model XML → ambil ID gambar / audio di dalamnya lalu download itu
        if ($depth === 0 && self::detect($bytes) === null) {
            $innerId = self::extractInnerAssetId($bytes);
            if ($innerId !== null && $innerId !== $assetId) {
                return $this->download($innerId, 1);
            }
        }

        return ['bytes' => $bytes, 'sourceId' => $assetId];
    }

    private function resolveLocation(string $assetId): string
    {
        // 1. Open Cloud (pakai API key → bisa akses aset privat milik kamu / grup kamu)
        $res  = $this->request('GET', self::DELIVERY_URL . $assetId, ['x-api-key: ' . $this->apiKey]);
        $json = json_decode($res['body'], true);
        if ($res['status'] === 200 && !empty($json['location'])) {
            return (string)$json['location'];
        }

        // 2. Fallback asset delivery publik (gambar publik tetap bisa diambil)
        $legacy = $this->request('GET', self::LEGACY_URL . $assetId, ['Accept: application/json']);
        $ljson  = json_decode($legacy['body'], true);
        if ($legacy['status'] === 200 && !empty($ljson['locations'][0]['location'])) {
            return (string)$ljson['locations'][0]['location'];
        }

        $reason = $json['message'] ?? $json['errors'][0]['message'] ?? $ljson['errors'][0]['message'] ?? null;
        throw new RuntimeException(
            'Gagal download aset ' . $assetId . ' (HTTP ' . $res['status'] . ')'
            . ($reason ? ': ' . $reason : '')
            . '. Pastikan API key punya scope legacy-assets:manage dan kamu punya akses ke aset ini.'
        );
    }

    private function fetchLocation(string $url): string
    {
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $ok   = false;
        foreach (self::ALLOWED_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                $ok = true;
                break;
            }
        }
        if (!$ok || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('Lokasi download tidak valid: ' . $host);
        }

        $res = $this->request('GET', $url, [], null, true);
        if ($res['status'] !== 200 || $res['body'] === '') {
            throw new RuntimeException('Gagal download file aset (HTTP ' . $res['status'] . ')');
        }
        return $res['body'];
    }

    /** ID aset di dalam XML decal/model: <url>http://www.roblox.com/asset/?id=123</url> */
    public static function extractInnerAssetId(string $bytes): ?string
    {
        if (preg_match('#(?:asset/?\?id=|rbxassetid://)(\d+)#i', $bytes, $m)) {
            return $m[1];
        }
        return null;
    }

    // ============================================================
    // UPLOAD
    // ============================================================

    /**
     * Upload file ke Roblox. Balikin ['assetId'=>..] kalau langsung selesai,
     * atau ['operationId'=>..] kalau masih diproses (cek lagi pakai operation()).
     */
    public function upload(string $bytes, string $name, string $creatorType, string $creatorId, string $description = ''): array
    {
        $info = self::detect($bytes);
        if ($info === null) {
            throw new RuntimeException('Format file tidak didukung (hanya PNG/JPG/BMP atau MP3/OGG/WAV/FLAC)');
        }
        if (strlen($bytes) > self::MAX_BYTES) {
            throw new RuntimeException('File terlalu besar (maks 20MB)');
        }

        $creator = $creatorType === 'group' ? ['groupId' => $creatorId] : ['userId' => $creatorId];
        $request = [
            'assetType'       => $info['assetType'],
            'displayName'     => self::cleanName($name),
            'description'     => $description !== '' ? mb_substr($description, 0, 1000) : 'Uploaded via ARRR Studio',
            'creationContext' => ['creator' => $creator],
        ];

        $boundary = '----arrr' . bin2hex(random_bytes(8));
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"request\"\r\n"
            . "Content-Type: application/json\r\n\r\n"
            . json_encode($request) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"fileContent\"; filename=\"asset.{$info['ext']}\"\r\n"
            . "Content-Type: {$info['mime']}\r\n\r\n"
            . $bytes . "\r\n"
            . "--{$boundary}--\r\n";

        $res = $this->request('POST', self::UPLOAD_URL, [
            'x-api-key: ' . $this->apiKey,
            'Content-Type: multipart/form-data; boundary=' . $boundary,
        ], $body);

        $json = json_decode($res['body'], true) ?: [];
        if ($res['status'] < 200 || $res['status'] >= 300) {
            throw new RuntimeException('Upload ditolak Roblox (HTTP ' . $res['status'] . '): ' . self::errorMessage($json, $res['body']));
        }

        return self::operationResult($json) + ['kind' => $info['kind']];
    }

    /** Cek status operasi upload */
    public function operation(string $operationId): array
    {
        $res  = $this->request('GET', self::OPERATION_URL . rawurlencode($operationId), ['x-api-key: ' . $this->apiKey]);
        $json = json_decode($res['body'], true) ?: [];
        if ($res['status'] !== 200) {
            throw new RuntimeException('Gagal cek status (HTTP ' . $res['status'] . '): ' . self::errorMessage($json, $res['body']));
        }
        return self::operationResult($json);
    }

    /** Upload lalu tunggu sebentar sampai dapat asset ID */
    public function uploadAndWait(string $bytes, string $name, string $creatorType, string $creatorId, int $maxWaitSec = 12): array
    {
        $result   = $this->upload($bytes, $name, $creatorType, $creatorId);
        $deadline = microtime(true) + $maxWaitSec;

        while (empty($result['assetId']) && !empty($result['operationId']) && microtime(true) < $deadline) {
            usleep(1_500_000);
            $result = $this->operation($result['operationId']) + ['kind' => $result['kind'] ?? null];
        }
        return $result;
    }

    private static function operationResult(array $json): array
    {
        if (!empty($json['error'])) {
            throw new RuntimeException('Upload gagal: ' . self::errorMessage($json['error'], ''));
        }
        $assetId = $json['response']['assetId'] ?? null;
        if (!empty($json['done']) && $assetId) {
            return ['assetId' => (string)$assetId];
        }

        $opId = $json['operationId'] ?? null;
        if (!$opId && !empty($json['path'])) {
            $opId = basename((string)$json['path']);
        }
        if (!$opId) {
            throw new RuntimeException('Respons Roblox tidak dikenali: ' . substr(json_encode($json), 0, 200));
        }
        return ['operationId' => (string)$opId];
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /** Deteksi jenis file dari magic bytes */
    public static function detect(string $bytes): ?array
    {
        foreach (self::SIGNATURES as [$sig, $kind, $assetType, $mime, $ext]) {
            if (str_starts_with($bytes, $sig)) {
                if ($sig === 'RIFF' && substr($bytes, 8, 4) !== 'WAVE') {
                    continue;
                }
                return compact('kind', 'assetType', 'mime', 'ext');
            }
        }
        return null;
    }

    private static function cleanName(string $name): string
    {
        $name = trim(preg_replace('/[^\p{L}\p{N} _.\-()]/u', '', $name) ?? '');
        return mb_substr($name !== '' ? $name : 'Asset', 0, 50);
    }

    private static function errorMessage($json, string $raw): string
    {
        if (is_array($json)) {
            $msg = $json['message'] ?? $json['errors'][0]['message'] ?? null;
            if ($msg) {
                return (string)$msg;
            }
        }
        return $raw !== '' ? substr(strip_tags($raw), 0, 200) : 'tanpa pesan';
    }

    /** @return array{status:int, body:string} */
    private function request(string $method, string $url, array $headers = [], ?string $body = null, bool $binary = false): array
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_ENCODING       => '',
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = $body ?? '';
        }
        if ($binary) {
            // Stop kalau file lebih besar dari batas
            $opts[CURLOPT_NOPROGRESS]       = false;
            $opts[CURLOPT_PROGRESSFUNCTION] = fn($c, $dlTotal, $dlNow) => $dlNow > self::MAX_BYTES ? 1 : 0;
        }
        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $status   = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Koneksi ke Roblox gagal: ' . $error);
        }
        return ['status' => $status, 'body' => (string)$response];
    }
}
