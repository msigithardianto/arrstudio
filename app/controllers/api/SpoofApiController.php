<?php
// app/controllers/api/SpoofApiController.php — Auto Spoof: re-upload aset ke akun/grup sendiri
//
// POST JSON  {action:"reupload", apiKey, creatorType, creatorId, assetId, name?}
// POST JSON  {action:"status",   apiKey, operationId}
// POST form  action=upload, apiKey, creatorType, creatorId, name?, file (multipart)
//
// Balasan sukses: {assetId} atau {operationId} (belum selesai → JS panggil "status")

class SpoofApiController extends ApiController
{
    public function handle(): void
    {
        if (!Auth::check()) {
            $this->error('Login dulu untuk memakai Auto Spoof.', ['login_url' => url('login')], 401);
        }

        $isMultipart = str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
        $input       = $isMultipart ? $_POST : (Request::json() ?? []);
        $action      = (string)($input['action'] ?? '');

        $apiKey = trim((string)($input['apiKey'] ?? ''));
        if ($apiKey === '' || strlen($apiKey) > 2000) {
            $this->error('API key Roblox wajib diisi.');
        }

        set_time_limit(90);
        $service = new RobloxAssetService($apiKey);

        try {
            switch ($action) {
                case 'status':
                    $opId = (string)($input['operationId'] ?? '');
                    if (!preg_match('/^[A-Za-z0-9_-]{1,128}$/', $opId)) {
                        $this->error('operationId tidak valid');
                    }
                    $this->json($service->operation($opId));

                case 'reupload':
                    [$creatorType, $creatorId] = $this->creator($input);
                    $assetId = (string)($input['assetId'] ?? '');
                    if (!preg_match('/^\d{1,20}$/', $assetId)) {
                        $this->error('Asset ID tidak valid: ' . $assetId);
                    }
                    $file   = $service->download($assetId);
                    $name   = trim((string)($input['name'] ?? '')) ?: 'Asset ' . $assetId;
                    $result = $service->uploadAndWait($file['bytes'], $name, $creatorType, $creatorId);
                    $this->json($result + ['sourceId' => $file['sourceId']]);

                case 'upload':
                    [$creatorType, $creatorId] = $this->creator($input);
                    $upload = $_FILES['file'] ?? null;
                    if (!$upload || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                        $this->error('File gagal diterima server (cek upload_max_filesize di php.ini).');
                    }
                    if ($upload['size'] > RobloxAssetService::MAX_BYTES) {
                        $this->error('File terlalu besar (maks 20MB)');
                    }
                    $bytes  = (string)file_get_contents($upload['tmp_name']);
                    $name   = trim((string)($input['name'] ?? '')) ?: pathinfo((string)$upload['name'], PATHINFO_FILENAME);
                    $result = $service->uploadAndWait($bytes, $name, $creatorType, $creatorId);
                    $this->json($result);

                default:
                    $this->error('Action tidak dikenal');
            }
        } catch (Throwable $e) {
            // Pesan saja — jangan kirim file/line (dan jangan pernah log API key)
            $this->error($e->getMessage());
        }
    }

    /** @return array{0:string,1:string} */
    private function creator(array $input): array
    {
        $type = ($input['creatorType'] ?? 'user') === 'group' ? 'group' : 'user';
        $id   = (string)($input['creatorId'] ?? '');
        if (!preg_match('/^\d{1,20}$/', $id)) {
            $this->error('User ID / Group ID kamu wajib diisi (angka).');
        }
        return [$type, $id];
    }
}
