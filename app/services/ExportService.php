<?php
// app/services/ExportService.php — gabungkan semua output generator untuk 1 set node

class ExportService
{
    /** Default config BillboardGui kalau frontend nggak kirim */
    private const DEFAULT_BILLBOARD = [
        'name'        => 'Player',
        'role'        => 'Member',
        'level'       => 1,
        'offsetY'     => 3.5,
        'size'        => [220, 70],
        'maxDistance' => 120,
        'theme'       => 'gold',
    ];

    public function build(array $nodes, int $width, int $height, ?array $billboard = null): array
    {
        $logic = GameLogicGenerator::generate($nodes);

        return [
            'module'       => $logic['module'],
            'server'       => $logic['server'],
            'client'       => $logic['client'],
            'logicSummary' => $this->logicSummary($logic['spec']),
            'script'     => LuaGenerator::generateBehaviorScript($nodes),
            'fullscript' => $this->withAutoScale(FullScriptGenerator::generateFullScript($nodes, $width, $height)),
            'tree'       => LuaGenerator::renderTreeText($nodes),
            'rbxmx'      => RbxmxGenerator::generate($nodes, $width, $height),
            'plugin'     => PluginGenerator::generate(),
            'billboard'  => BillboardGenerator::generate($billboard ?? self::DEFAULT_BILLBOARD),
            'report'     => LuaGenerator::renderReport($nodes),
        ];
    }

    /**
     * Ringkasan singkat hasil analisis GUI (ditampilkan di tab Game Logic)
     */
    private function logicSummary(array $spec): string
    {
        $parts = [];
        if ($spec['items'])      $parts[] = count($spec['items']) . ' item';
        if ($spec['currencies']) $parts[] = implode('/', array_keys($spec['currencies']));
        if ($spec['settings'])   $parts[] = count($spec['settings']) . ' setting';
        if ($spec['actions'])    $parts[] = implode(', ', $spec['actions']);
        return $parts ? implode(' · ', $parts) : 'Tidak ada aksi server terdeteksi — template dasar';
    }

    /**
     * Sisipkan UIScale supaya canvas menyesuaikan ukuran layar
     * (tepat setelah baris `canvas.Parent = ...`)
     */
    private function withAutoScale(string $script): string
    {
        $patch = <<<'LUA'

-- ============================================================
--  AUTO-SCALE
-- ============================================================
do
	local Camera = workspace.CurrentCamera
	local uiScale = Instance.new("UIScale")
	uiScale.Name = "AutoScale"
	uiScale.Parent = canvas

	local function updateScale()
		if not Camera then return end
		local vp = Camera.ViewportSize
		local scaleX = (vp.X * 0.9) / 800
		local scaleY = (vp.Y * 0.9) / 600
		local s = math.min(scaleX, scaleY, 1)
		uiScale.Scale = s
		canvas.Position = UDim2.new(0.5, 0, 0.5, 0)
		canvas.AnchorPoint = Vector2.new(0.5, 0.5)
	end

	updateScale()
	if Camera then
		Camera:GetPropertyChangedSignal("ViewportSize"):Connect(updateScale)
	end
end
LUA;

        return preg_replace(
            '/(canvas\.Parent\s*=\s*(screenGui|playerGui))/',
            '$1' . "\n" . $patch,
            $script,
            1
        );
    }
}
