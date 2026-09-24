<?php
// app/services/generators/RbxmxGenerator.php — model .rbxmx siap import
class RbxmxGenerator {

    public static function generate($nodes, $W = 800, $H = 600) {
        $refCounter = 0;
        $newRef = function() use (&$refCounter) {
            return 'RBX' . strtoupper(base_convert(++$refCounter, 10, 36));
        };

        $behavior = LuaGenerator::generateBehaviorScript($nodes);

        $out = [];
        $out[] = '<?xml version="1.0" encoding="utf-8"?>';
        $out[] = '<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" version="4">';

        $out[] = '  <Item class="Folder" referent="' . $newRef() . '">';
        $out[] = '    <Properties>';
        $out[] = '      <string name="Name">GeneratedUIPack</string>';
        $out[] = '    </Properties>';

        $out[] = '    <Item class="ScreenGui" referent="' . $newRef() . '">';
        $out[] = '      <Properties>';
        $out[] = '        <string name="Name">GeneratedUI</string>';
        $out[] = '        <bool name="ResetOnSpawn">false</bool>';
        $out[] = '        <bool name="IgnoreGuiInset">true</bool>';
        $out[] = '        <token name="ZIndexBehavior">0</token>';
        $out[] = '      </Properties>';

        $out[] = '      <Item class="Script" referent="' . $newRef() . '">';
        $out[] = '        <Properties>';
        $out[] = '          <string name="Name">GeneratedUI_Behavior</string>';
        $out[] = '          <bool name="Disabled">false</bool>';
        $out[] = '          <token name="RunContext">2</token>';
        $out[] = '          <ProtectedString name="Source"><![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $behavior) . ']]></ProtectedString>';
        $out[] = '        </Properties>';
        $out[] = '      </Item>';

        $out[] = '      <Item class="Frame" referent="' . $newRef() . '">';
        $out[] = '        <Properties>';
        $out[] = '          <string name="Name">Canvas</string>';
        $out[] = '          <float name="BackgroundTransparency">1</float>';
        $out[] = "          <UDim2 name=\"Size\"><XS>0</XS><XO>{$W}</XO><YS>0</YS><YO>{$H}</YO></UDim2>";
        $out[] = "          <UDim2 name=\"Position\"><XS>0.5</XS><XO>-" . ($W/2) . "</XO><YS>0.5</YS><YO>-" . ($H/2) . "</YO></UDim2>";
        $out[] = '          <int name="BorderSizePixel">0</int>';
        $out[] = '          <bool name="Active">false</bool>';
        $out[] = '          <bool name="ClipsDescendants">false</bool>';
        $out[] = '        </Properties>';

        $out[] = '        <Item class="UIScale" referent="' . $newRef() . '">';
        $out[] = '          <Properties>';
        $out[] = '            <string name="Name">UIScale</string>';
        $out[] = '            <float name="Scale">1</float>';
        $out[] = '          </Properties>';
        $out[] = '        </Item>';

        $byParent = [];
        foreach ($nodes as $n) {
            $key = $n['parentId'] === null ? 'root' : (string)$n['parentId'];
            $byParent[$key][] = $n;
        }

        $writeNode = function($n, $indent) use (&$writeNode, &$byParent, &$out, &$newRef) {
            $pad = str_repeat('  ', $indent);

            $out[] = "{$pad}<Item class=\"{$n['robloxClass']}\" referent=\"" . $newRef() . '">';
            $out[] = "{$pad}  <Properties>";
            $out[] = "{$pad}    <string name=\"Name\">" . htmlspecialchars($n['name']) . "</string>";
            $out[] = "{$pad}    <UDim2 name=\"Position\"><XS>0</XS><XO>{$n['x']}</XO><YS>0</YS><YO>{$n['y']}</YO></UDim2>";
            $out[] = "{$pad}    <UDim2 name=\"Size\"><XS>0</XS><XO>{$n['w']}</XO><YS>0</YS><YO>{$n['h']}</YO></UDim2>";
            $out[] = "{$pad}    <Color3 name=\"BackgroundColor3\"><R>" . round($n['bg']['r'], 4) . "</R><G>" . round($n['bg']['g'], 4) . "</G><B>" . round($n['bg']['b'], 4) . "</B></Color3>";
            $out[] = "{$pad}    <float name=\"BackgroundTransparency\">" . round(1 - $n['bg']['a'], 4) . "</float>";
            $out[] = "{$pad}    <int name=\"BorderSizePixel\">" . ($n['borderW'] > 0 ? max(1, (int)$n['borderW']) : 0) . "</int>";

            if (!empty($n['selfHidden'])) {
                $out[] = "{$pad}    <bool name=\"Visible\">false</bool>";
            }
            if ($n['borderW'] > 0) {
                $out[] = "{$pad}    <Color3 name=\"BorderColor3\"><R>" . round($n['borderColor']['r'], 4) . "</R><G>" . round($n['borderColor']['g'], 4) . "</G><B>" . round($n['borderColor']['b'], 4) . "</B></Color3>";
            }

            if (in_array($n['robloxClass'], ['TextLabel','TextButton','TextBox'])) {
                $txt = $n['text'] ?: ($n['value'] ?: ($n['placeholder'] ?: ''));
                $out[] = "{$pad}    <string name=\"Text\">" . htmlspecialchars($txt) . "</string>";
                $out[] = "{$pad}    <Color3 name=\"TextColor3\"><R>" . round($n['fg']['r'], 4) . "</R><G>" . round($n['fg']['g'], 4) . "</G><B>" . round($n['fg']['b'], 4) . "</B></Color3>";
                $out[] = "{$pad}    <float name=\"TextTransparency\">" . round(1 - $n['fg']['a'], 4) . "</float>";
                $out[] = "{$pad}    <int name=\"TextSize\">" . max(11, (int)$n['fontSize']) . "</int>";
                $txtForFont = $n['text'] ?: ($n['value'] ?: ($n['placeholder'] ?: ''));
                $isIconChar = (preg_match('/[^\x00-\x7F]/u', $txtForFont) !== 0) && (mb_strlen($txtForFont) <= 3);
                $fontToken = $isIconChar ? 1 : 16;
                $out[] = "{$pad}    <token name=\"Font\">{$fontToken}</token>";
                $xa = $n['textAlign'] === 'center' ? 1 : (($n['textAlign'] === 'right' || $n['textAlign'] === 'end') ? 2 : 0);
                $out[] = "{$pad}    <token name=\"TextXAlignment\">{$xa}</token>";
                $out[] = "{$pad}    <token name=\"TextYAlignment\">1</token>";
                $out[] = "{$pad}    <bool name=\"TextWrapped\">true</bool>";
                $out[] = "{$pad}    <bool name=\"TextScaled\">false</bool>";
                if ($n['robloxClass'] === 'TextButton') {
                    $out[] = "{$pad}    <bool name=\"AutoButtonColor\">false</bool>";
                }
                if ($n['robloxClass'] === 'TextBox') {
                    $out[] = "{$pad}    <string name=\"PlaceholderText\">" . htmlspecialchars($n['placeholder'] ?: '') . "</string>";
                    $out[] = "{$pad}    <bool name=\"ClearTextOnFocus\">false</bool>";
                }
            }
            if ($n['robloxClass'] === 'ImageLabel') {
                $out[] = "{$pad}    <Content name=\"Image\"><url>" . htmlspecialchars($n['src']) . "</url></Content>";
                $out[] = "{$pad}    <token name=\"ScaleType\">0</token>";
            }

            $out[] = "{$pad}  </Properties>";

            if ($n['radius'] > 0) {
                $out[] = "{$pad}  <Item class=\"UICorner\" referent=\"" . $newRef() . '">';
                $out[] = "{$pad}    <Properties>";
                $out[] = "{$pad}      <string name=\"Name\">UICorner</string>";
                $out[] = "{$pad}      <UDim name=\"CornerRadius\"><S>0</S><O>{$n['radius']}</O></UDim>";
                $out[] = "{$pad}    </Properties>";
                $out[] = "{$pad}  </Item>";
            }

            if (!empty($n['gradient'])) {
                $out[] = "{$pad}  <Item class=\"UIGradient\" referent=\"" . $newRef() . '">';
                $out[] = "{$pad}    <Properties>";
                $out[] = "{$pad}      <string name=\"Name\">UIGradient</string>";
                $out[] = "{$pad}      <float name=\"Rotation\">" . round($n['gradient']['rotation'], 1) . "</float>";
                $out[] = "{$pad}      <ColorSequence name=\"Color\">";
                foreach ($n['gradient']['keypoints'] as $kp) {
                    $t = max(0, min(1, $kp['pos']));
                    $out[] = "{$pad}        <ColorSequenceKeypoint>";
                    $out[] = "{$pad}          <float name=\"Time\">" . round($t, 4) . "</float>";
                    $out[] = "{$pad}          <Color3 name=\"Value\"><R>" . round($kp['color']['r'], 4) . "</R><G>" . round($kp['color']['g'], 4) . "</G><B>" . round($kp['color']['b'], 4) . "</B></Color3>";
                    $out[] = "{$pad}        </ColorSequenceKeypoint>";
                }
                $out[] = "{$pad}      </ColorSequence>";
                $out[] = "{$pad}      <NumberSequence name=\"Transparency\">";
                foreach ($n['gradient']['keypoints'] as $kp) {
                    $t = max(0, min(1, $kp['pos']));
                    $a = round(1 - $kp['color']['a'], 4);
                    $out[] = "{$pad}        <NumberSequenceKeypoint>";
                    $out[] = "{$pad}          <float name=\"Time\">" . round($t, 4) . "</float>";
                    $out[] = "{$pad}          <float name=\"Value\">{$a}</float>";
                    $out[] = "{$pad}        </NumberSequenceKeypoint>";
                }
                $out[] = "{$pad}      </NumberSequence>";
                $out[] = "{$pad}    </Properties>";
                $out[] = "{$pad}  </Item>";
            }

            if (($n['padL'] || $n['padR'] || $n['padT'] || $n['padB']) && in_array($n['robloxClass'], ['TextLabel','TextButton','TextBox'])) {
                $out[] = "{$pad}  <Item class=\"UIPadding\" referent=\"" . $newRef() . '">';
                $out[] = "{$pad}    <Properties>";
                $out[] = "{$pad}      <string name=\"Name\">UIPadding</string>";
                $out[] = "{$pad}      <UDim name=\"PaddingLeft\"><S>0</S><O>{$n['padL']}</O></UDim>";
                $out[] = "{$pad}      <UDim name=\"PaddingRight\"><S>0</S><O>{$n['padR']}</O></UDim>";
                $out[] = "{$pad}      <UDim name=\"PaddingTop\"><S>0</S><O>{$n['padT']}</O></UDim>";
                $out[] = "{$pad}      <UDim name=\"PaddingBottom\"><S>0</S><O>{$n['padB']}</O></UDim>";
                $out[] = "{$pad}    </Properties>";
                $out[] = "{$pad}  </Item>";
            }

            $children = $byParent[(string)$n['id']] ?? [];
            foreach ($children as $c) $writeNode($c, $indent + 1);

            $out[] = "{$pad}</Item>";
        };

        $rootChildren = $byParent['root'] ?? [];
        foreach ($rootChildren as $c) $writeNode($c, 6);

        $out[] = '      </Item>';
        $out[] = '    </Item>';
        $out[] = '  </Item>';
        $out[] = '</roblox>';

        return implode("\n", $out);
    }
}