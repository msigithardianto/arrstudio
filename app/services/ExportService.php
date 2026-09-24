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
        return [
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
