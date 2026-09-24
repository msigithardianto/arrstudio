<?php
// app/services/converter/NodeNamer.php — kasih nama node yang rapi (PascalCase + role)
class NodeNamer {
    private const ROLE_SUFFIX = [
        'div' => 'Panel', 'span' => 'Label', 'p' => 'Text', 'button' => 'Button',
        'input' => 'Input', 'textarea' => 'InputArea', 'img' => 'Image',
        'ul' => 'List', 'ol' => 'List', 'li' => 'ListItem',
        'a' => 'Link', 'label' => 'FormLabel', 'nav' => 'Nav', 'header' => 'Header',
        'footer' => 'Footer', 'main' => 'MainContent', 'section' => 'Section',
        'aside' => 'Sidebar', 'article' => 'Article',
        'h1' => 'TitleLabel', 'h2' => 'SubtitleLabel', 'h3' => 'HeadingLabel',
        'h4' => 'HeadingLabel', 'h5' => 'HeadingLabel', 'h6' => 'HeadingLabel',
    ];

    public static function assignProfessionalNames(&$nodes) {
        $used = [];
        $idToNode = [];
        foreach ($nodes as &$n) $idToNode[$n['id']] = &$n;

        foreach ($nodes as &$n) {
            $base = '';
            if (!empty($n['explicitName'])) {
                $base = self::safeName($n['explicitName'], '');
            } elseif ($n['tag'] === 'img' && !empty($n['alt'])) {
                $base = self::safeName($n['alt'], '');
            } elseif (in_array($n['tag'], ['button','a','h1','h2','h3','h4','h5','h6','label','li','span','p'])) {
                if (!empty($n['text']) && strlen($n['text']) <= 40) {
                    $base = self::safeName($n['text'], '');
                }
            }
            if (!$base) continue;

            $hasSuffix = (bool)preg_match('/Button$|Toggle$|Icon$|Btn$|Panel$/i', $base);
            if ($n['robloxClass'] === 'TextButton' && !$hasSuffix) $base .= 'Button';
            if ($n['robloxClass'] === 'Frame' && !$hasSuffix) $base .= 'Panel';

            $name = $base; $i = 2;
            while (isset($used[$name])) $name = $base . ($i++);
            $used[$name] = true;
            $n['name'] = $name;
        }
        unset($n);

        foreach ($nodes as &$n) {
            if (!empty($n['name'])) continue;
            $ctx = '';
            if ($n['parentId'] !== null && isset($idToNode[$n['parentId']])) {
                $parentName = $idToNode[$n['parentId']]['name'] ?? '';
                $ctx = preg_replace('/(Panel|Card|Container|Frame|Label|Text|Button|Input|Image|List|Section|Nav)$/', '', $parentName);
            }
            $suffix = self::ROLE_SUFFIX[$n['tag']] ?? $n['robloxClass'];
            $base = ($ctx ?: '') . $suffix;
            if (!$base) $base = $n['robloxClass'];
            if (preg_match('/^\d/', $base)) $base = 'N' . $base;

            $name = $base; $i = 2;
            while (isset($used[$name])) $name = $base . ($i++);
            $used[$name] = true;
            $n['name'] = $name;
        }
        unset($n);
    }

    private static function pascalCase($str) {
        $parts = preg_split('/[^a-zA-Z0-9]+/', $str, -1, PREG_SPLIT_NO_EMPTY);
        return implode('', array_map(fn($w) => ucfirst(strtolower($w)), $parts));
    }

    private static function safeName($str, $fallback) {
        $n = self::pascalCase($str);
        if (!$n) $n = $fallback;
        if (preg_match('/^\d/', $n)) $n = 'N' . $n;
        return substr($n, 0, 40);
    }
}