<?php
// app/services/logic/GuiAnalyzer.php — baca node GUI → spesifikasi logic game
// (mata uang, katalog item, tombol aksi, input, setting) untuk GameLogicGenerator

class GuiAnalyzer
{
    /** Aksi server yang dikenali dari teks/nama/id tombol (urutan = prioritas) */
    private const ACTIONS = [
        'Unequip'     => '/\b(unequip|lepas)\b/u',
        'Purchase'    => '/\b(buy|purchase|beli|order|checkout)\b/u',
        'Sell'        => '/\b(sell|jual)\b/u',
        'Equip'       => '/\b(equip|wear|pakai)\b/u',
        'Claim'       => '/\b(claim|klaim|collect|reward|daily|ambil)\b/u',
        'Upgrade'     => '/\b(upgrade|level ?up|enhance|tingkatkan)\b/u',
        'Craft'       => '/\b(craft|forge|rakit)\b/u',
        'Redeem'      => '/\b(redeem|tukar kode|kode|code)\b/u',
        'Spin'        => '/\b(spin|roll|gacha|summon|putar)\b/u',
        'UseItem'     => '/\b(use|consume|gunakan|minum)\b/u',
        'AcceptQuest' => '/\b(accept|terima|mulai quest|start quest)\b/u',
        'DeclineQuest'=> '/\b(decline|tolak|reject)\b/u',
        'SaveSettings'=> '/\b(save|simpan|apply|terapkan)\b/u',
    ];

    /** Kata → nama mata uang (dicek duluan, lebih spesifik dari ikon) */
    private const CURRENCY_WORDS = [
        'Gold'   => '/\bgold\b|\bemas\b/u',
        'Gems'   => '/\bgems?\b|\bpermata\b|\bdiamonds?\b/u',
        'Coins'  => '/\bcoins?\b|\bkoin\b|\bcash\b|\bmoney\b|\buang\b/u',
        'Tokens' => '/\btokens?\b|\btiket\b/u',
    ];

    /** Ikon → nama mata uang */
    private const CURRENCY_ICONS = [
        'Gems'   => '/💎/u',
        'Coins'  => '/💰|🪙|\$/u',
        'Tokens' => '/🎟/u',
    ];

    /** Judul panel (bukan nama item) */
    private const HEADING_WORDS = '/\b(shop|store|toko|market|marketplace|inventory|inventori|backpack|bag|tas|settings?|pengaturan|menu|leaderboard|profile|profil|quests?|misi)\b/iu';

    private const RARITIES = ['common', 'uncommon', 'rare', 'epic', 'legendary', 'mythic', 'exotic', 'limited', 'special'];

    private array $nodes = [];
    private array $byId = [];
    private array $children = [];

    public function analyze(array $nodes): array
    {
        $this->nodes = $nodes;
        foreach ($nodes as $n) {
            $this->byId[$n['id']] = $n;
            $this->children[$n['parentId'] ?? 0][] = $n['id'];
        }

        $cards      = $this->findItemCards();
        $cardOf     = $this->mapNodeToCard($cards);
        $balances   = $this->findBalances($cardOf);
        $bindings   = [];
        $items      = [];
        $defaultCur = $balances ? $balances[0]['currency'] : 'Coins';

        foreach ($cards as $card) {
            $items[$card['itemId']] = [
                'DisplayName' => $card['name'],
                'Price'       => $card['price'],
                'Currency'    => $card['currency'] ?? $defaultCur,
                'Rarity'      => $card['rarity'],
            ];
        }

        // === Tombol aksi ===
        $cardsWithButton = [];
        foreach ($this->nodes as $n) {
            // Tombol, atau label pendek berisi kata aksi ("Save Changes", "Claim")
            $shortActionLabel = $n['robloxClass'] === 'TextLabel'
                && mb_strlen(trim($n['text'] ?? '')) <= 24
                && !in_array($n['tag'] ?? '', ['h1', 'h2', 'h3', 'p', 'li'], true);
            if (!$this->isClickable($n) && !$shortActionLabel) continue;
            if (isset($cardOf[$n['id']]) && !$this->isClickable($n)) continue;
            // Label di dalam tombol yang sudah ditangani → skip
            if (!$this->isClickable($n) && $this->hasClickableAncestor($n)) continue;
            $action = $this->classify($n);
            if ($action === null) continue;

            $binding = ['target' => $n['name'], 'action' => $action, 'kind' => 'button', 'label' => $n['text'] ?? ''];
            $card = $cardOf[$n['id']] ?? null;
            if ($card !== null && in_array($action, ['Purchase', 'Sell', 'Equip', 'Unequip', 'Upgrade', 'UseItem', 'Craft'], true)) {
                $binding['itemId'] = $card['itemId'];
                $cardsWithButton[$card['itemId']] = true;
            }
            if ($action === 'Redeem') {
                $binding['input'] = $this->nearestTextBox($n);
            }
            if (in_array($action, ['AcceptQuest', 'DeclineQuest'], true)) {
                $binding['questId'] = $this->questIdFor($n);
            }
            $bindings[] = $binding;
        }

        // === Pilihan dialog: >= 2 tombol bersaudara berisi kalimat ===
        $bound = array_flip(array_column($bindings, 'target'));
        foreach ($this->children as $pid => $kids) {
            $choices = array_values(array_filter(array_map(fn($id) => $this->byId[$id], $kids), function ($k) use ($bound) {
                if (!$this->isClickable($k) || isset($bound[$k['name']])) return false;
                $text = trim(($k['text'] ?? '') . ' ' . implode(' ', array_column($this->descendants($k['id'], 2), 'text')));
                return mb_strlen($text) > 15;
            }));
            if (count($choices) < 2) continue;
            // Harus berupa kalimat (dialog), bukan daftar menu/playlist
            $sentences = array_filter($choices, fn($c) => preg_match('/[.?!…]\s*$/u',
                trim(($c['text'] ?? '') . ' ' . implode(' ', array_column($this->descendants($c['id'], 2), 'text')))));
            if (count($sentences) < 2) continue;

            $dialogueId = $this->questIdFor($choices[0]);
            foreach ($choices as $i => $c) {
                $bindings[] = [
                    'target' => $c['name'], 'action' => 'DialogueChoice', 'kind' => 'button',
                    'dialogueId' => $dialogueId, 'choice' => $i + 1,
                    'label' => mb_substr(preg_replace('/^\d+\s+/', '', trim(implode(' ', array_filter(array_column($this->descendants($c['id'], 2), 'text')))) ?: ($c['text'] ?? '')), 0, 60),
                ];
            }
        }

        // === Card item tanpa tombol → card-nya sendiri bisa diklik untuk beli ===
        foreach ($cards as $card) {
            if (isset($cardsWithButton[$card['itemId']])) continue;
            $bindings[] = [
                'target' => $card['node'], 'action' => 'Purchase', 'kind' => 'card',
                'itemId' => $card['itemId'], 'label' => $card['name'],
            ];
        }

        // === Toggle switch → setting per pemain ===
        $settings = [];
        foreach ($this->nodes as $n) {
            if (empty($n['isToggleSwitch']) && !$this->looksLikeSwitch($n)) continue;
            $key = LuaHelper::ident($this->labelNear($n) ?: $n['name'], 'Setting');
            // Posisi knob di kanan = ON
            $knobId = ($this->children[$n['id']] ?? [])[0] ?? null;
            $knob = $knobId ? $this->byId[$knobId] : null;
            $settings[$key] = $knob ? ($knob['x'] + $knob['w'] / 2) > ($n['w'] / 2) : false;
            $bindings[] = ['target' => $n['name'], 'action' => 'SetSetting', 'kind' => 'toggle', 'setting' => $key];
        }

        $actions = array_values(array_unique(array_column($bindings, 'action')));
        sort($actions);

        // Saldo awal = angka yang tampil di GUI (biar hasil di game sama dengan desain)
        $currencies = [];
        foreach ($balances as $b) $currencies[$b['currency']] ??= $b['amount'];
        foreach ($items as $it) $currencies[$it['Currency']] ??= 0;
        if (!$currencies && array_intersect($actions, ['Claim', 'Spin', 'Upgrade', 'Redeem', 'Craft'])) {
            $currencies['Coins'] = 0;
        }

        return [
            'currencies' => $currencies,
            'items'      => $items,
            'balances'   => $balances,
            'bindings'   => $bindings,
            'settings'   => $settings,
            'actions'    => $actions,
        ];
    }

    // ============================================================
    // DETEKSI
    // ============================================================

    /** Card item = container terkecil yang punya 1 harga + 1 nama */
    private function findItemCards(): array
    {
        $cards = [];
        $used  = [];
        foreach ($this->nodes as $n) {
            if (!in_array($n['robloxClass'], ['Frame', 'TextButton', 'ImageButton', 'ScrollingFrame'], true)) continue;

            $desc   = $this->descendants($n['id'], 3);
            $prices = array_values(array_filter($desc, fn($d) => $this->parsePrice($d['text'] ?? '') !== null));
            if (count($prices) !== 1) continue;

            $name = null;
            $rarity = null;
            foreach ($desc as $d) {
                $t = trim($d['text'] ?? '');
                if ($t === '' || $d['id'] === $prices[0]['id']) continue;
                if (in_array(strtolower($t), self::RARITIES, true)) { $rarity ??= ucfirst(strtolower($t)); continue; }
                if ($this->isIconOnly($t) || $this->classifyText($t) !== null || preg_match('/^\d/', $t)) continue;
                if (mb_strlen($t) > 40) continue;
                $name ??= $t;
            }
            if ($name === null || preg_match(self::HEADING_WORDS, $name)) continue;

            // Card di dalam card lain yang sudah terdeteksi → ambil yang terkecil (paling dalam)
            $price = $this->parsePrice($prices[0]['text']);
            $cards[$n['id']] = [
                'node'     => $n['name'],
                'id'       => $n['id'],
                'name'     => $name,
                'itemId'   => LuaHelper::ident($name),
                'price'    => $price['amount'],
                'currency' => $price['currency'],
                'rarity'   => $rarity,
            ];
            $used[$n['id']] = true;
        }

        // Buang card yang punya card lain di dalamnya
        foreach ($cards as $id => $card) {
            foreach ($cards as $other) {
                if ($other['id'] !== $id && $this->isAncestor($id, $other['id'])) {
                    unset($cards[$id]);
                    break;
                }
            }
        }

        // itemId unik
        $seen = [];
        foreach ($cards as &$card) {
            $base = $card['itemId'];
            for ($i = 2; isset($seen[$card['itemId']]); $i++) $card['itemId'] = $base . $i;
            $seen[$card['itemId']] = true;
        }
        unset($card);

        return array_values($cards);
    }

    private function mapNodeToCard(array $cards): array
    {
        $map = [];
        foreach ($cards as $card) {
            $map[$card['id']] = $card;
            foreach ($this->descendants($card['id'], 99) as $d) $map[$d['id']] = $card;
        }
        return $map;
    }

    /** Label saldo: "💎 12,450", "Gold: 1,240" (di luar card item) */
    private function findBalances(array $cardOf): array
    {
        $out = [];
        foreach ($this->nodes as $n) {
            if (isset($cardOf[$n['id']]) || !in_array($n['robloxClass'], ['TextLabel', 'TextButton'], true)) continue;
            $text = $n['text'] ?? '';
            $price = $this->parsePrice(preg_replace('/\s*[·|•].*$/u', '', $text), 32);
            if ($price === null || $price['currency'] === null) continue;

            // Template tampilan: angka diganti {n} → client update teks tanpa merusak format
            $template = preg_replace('/\d[\d.,]*(?:\s?[kKmM]\b)?/u', '{n}', $text, 1);
            $out[] = [
                'target'   => $n['name'],
                'currency' => $price['currency'],
                'amount'   => $price['amount'],
                'template' => $template,
            ];
        }
        return $out;
    }

    private function classify(array $n): ?string
    {
        $action = strtolower($n['action'] ?? '');
        // Aksi navigasi UI biasa → urusan behavior script, bukan server
        if (in_array($action, ['toggle', 'close', 'tab', 'switch', 'play'], true)) return null;

        $text = $n['text'] ?? '';
        if ($text === '') {
            // Tombol berisi label (mis. <button><span>Buy</span></button>)
            $text = implode(' ', array_column($this->descendants($n['id'], 2), 'text'));
        }
        if (mb_strlen($text) > 40) $text = '';  // kalimat panjang (dialog) bukan nama aksi

        return $this->classifyText(implode(' ', [
            $action, $text, $n['explicitName'] ?? '', $n['sourceId'] ?? '', $n['name'] ?? '',
        ]));
    }

    private function classifyText(string $hay): ?string
    {
        $hay = strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $hay));
        $hay = str_replace(['-', '_'], ' ', $hay);
        foreach (self::ACTIONS as $action => $re) {
            if (preg_match($re, $hay)) return $action;
        }
        return null;
    }

    private function currencyIn(string $text): ?string
    {
        $lower = strtolower($text);
        foreach ([self::CURRENCY_WORDS, self::CURRENCY_ICONS] as $group) {
            foreach ($group as $name => $re) {
                if (preg_match($re, $lower)) return $name;
            }
        }
        return null;
    }

    /** "2,500 💎" / "$4.99" / "1.2k Gold" → ['amount' => int, 'currency' => ?string] */
    private function parsePrice(string $text, int $maxLen = 24): ?array
    {
        $text = trim($text);
        if ($text === '' || mb_strlen($text) > $maxLen) return null;
        if (!preg_match('/(\d[\d.,]*)\s*([kKmM])?/u', $text, $m)) return null;

        $rest = trim(str_replace($m[0], '', $text));
        $currency = $this->currencyIn($text);
        // Tanpa mata uang, sisa teks harus pendek (mis. "Price: 100")
        if ($currency === null && !preg_match('/^(price|harga|cost)\s*:?$/i', $rest)) return null;

        $num = str_replace([',', ' '], '', $m[1]);
        // "1.200" (format Indonesia) vs "1.5" (desimal)
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $num)) $num = str_replace('.', '', $num);
        $amount = (float)$num;
        $mult = strtolower($m[2] ?? '');
        if ($mult === 'k') $amount *= 1000;
        if ($mult === 'm') $amount *= 1000000;

        return ['amount' => (int)round($amount), 'currency' => $currency];
    }

    // ============================================================
    // UTIL
    // ============================================================

    private function isClickable(array $n): bool
    {
        return in_array($n['robloxClass'], ['TextButton', 'ImageButton'], true) || !empty($n['isButtonLike']);
    }

    /** Bentuk switch: pill kecil (w ≥ 1.4h, radius penuh) berisi 1 knob bulat */
    private function looksLikeSwitch(array $n): bool
    {
        $w = $n['w'] ?? 0;
        $h = $n['h'] ?? 0;
        if ($w < 28 || $w > 72 || $h < 12 || $h > 36 || $w < $h * 1.4) return false;
        if (($n['radius'] ?? 0) < $h / 2 - 2) return false;

        $kids = $this->children[$n['id']] ?? [];
        if (count($kids) !== 1) return false;
        $knob = $this->byId[$kids[0]];
        return abs(($knob['w'] ?? 0) - ($knob['h'] ?? 0)) <= 3 && ($knob['h'] ?? 0) <= $h;
    }

    private function hasClickableAncestor(array $n): bool
    {
        for ($p = $n['parentId'] ?? null; $p; $p = $this->byId[$p]['parentId'] ?? null) {
            if ($this->isClickable($this->byId[$p])) return true;
        }
        return false;
    }

    private function isIconOnly(string $t): bool
    {
        return (bool)preg_match('/[^\x00-\x7F]/u', $t) && mb_strlen($t) <= 3;
    }

    private function descendants(int $id, int $depth): array
    {
        $out = [];
        if ($depth <= 0) return $out;
        foreach ($this->children[$id] ?? [] as $cid) {
            $out[] = $this->byId[$cid];
            foreach ($this->descendants($cid, $depth - 1) as $d) $out[] = $d;
        }
        return $out;
    }

    private function isAncestor(int $ancestorId, int $id): bool
    {
        for ($p = $this->byId[$id]['parentId'] ?? null; $p; $p = $this->byId[$p]['parentId'] ?? null) {
            if ($p === $ancestorId) return true;
        }
        return false;
    }

    /** TextBox terdekat (saudara / sepupu) — untuk input kode redeem dsb. */
    private function nearestTextBox(array $n): ?string
    {
        for ($p = $n['parentId'] ?? null, $level = 0; $p && $level < 3; $p = $this->byId[$p]['parentId'] ?? null, $level++) {
            foreach ($this->descendants($p, 3) as $d) {
                if ($d['robloxClass'] === 'TextBox') return $d['name'];
            }
        }
        return null;
    }

    /** Label teks di dekat toggle (mis. "Music" di sebelah switch) */
    private function labelNear(array $n): ?string
    {
        $pid = $n['parentId'] ?? 0;
        foreach ($this->descendants($pid, 3) as $d) {
            $t = trim($d['text'] ?? '');
            if ($d['id'] !== $n['id'] && $t !== '' && !$this->isIconOnly($t) && mb_strlen($t) <= 30) return $t;
        }
        return null;
    }

    /** Judul panel terdekat sebagai id quest */
    private function questIdFor(array $n): string
    {
        for ($p = $n['parentId'] ?? null; $p; $p = $this->byId[$p]['parentId'] ?? null) {
            foreach ($this->descendants($p, 2) as $d) {
                $t = trim($d['text'] ?? '');
                if ($t === '' || mb_strlen($t) > 40 || $this->isIconOnly($t)) continue;
                // "⚔️ Quest: The Missing Sword" → TheMissingSword
                if (preg_match('/\b(?:quest|misi)\s*:\s*(.+)$/iu', $t, $m)) return LuaHelper::ident($m[1], 'Quest');
                if (in_array($d['tag'] ?? '', ['h1', 'h2', 'h3', 'h4'], true)) return LuaHelper::ident($t, 'Quest');
            }
        }
        return 'MainQuest';
    }
}
