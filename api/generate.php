<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'error' => 'PHP Fatal: ' . $err['message'],
            'file' => $err['file'],
            'line' => $err['line'],
        ]);
    }
});

require_once __DIR__ . '/../lib/LuaGen.php';
require_once __DIR__ . '/../lib/FullGen.php';
require_once __DIR__ . '/../lib/RbxmxGen.php';
require_once __DIR__ . '/../lib/PluginGen.php';
require_once __DIR__ . '/../lib/BillboardGen.php';   // ← BARU

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['nodes'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$nodes = $input['nodes'];
$W = $input['canvasW'] ?? 800;
$H = $input['canvasH'] ?? 600;

// Config untuk billboard (dari frontend)
$bbConfig = $input['billboard'] ?? [
    'name'        => 'Player',
    'role'        => 'Member',
    'level'       => 1,
    'offsetY'     => 3.5,
    'size'        => [220, 70],
    'maxDistance' => 120,
    'theme'       => 'gold',
];

try {
    $fullScript = FullGen::generateFullScript($nodes, $W, $H);

    // Inject UIScale patch
    $uiScalePatch = <<<'LUA'

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

    $fullScript = preg_replace(
        '/(canvas\.Parent\s*=\s*(screenGui|playerGui))/',
        '$1' . "\n" . $uiScalePatch,
        $fullScript,
        1
    );

    echo json_encode([
        'script'     => LuaGen::generateBehaviorScript($nodes),
        'fullscript' => $fullScript,
        'tree'       => LuaGen::renderTreeText($nodes),
        'rbxmx'      => RbxmxGen::generate($nodes, $W, $H),
        'plugin'     => PluginGen::generate(),
        'billboard'  => BillboardGen::generate($bbConfig),  // ← BARU
        'report'     => LuaGen::renderReport($nodes),
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}