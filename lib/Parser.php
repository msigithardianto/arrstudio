<?php
/**
 * Parser.php — Hybrid HTML → node tree
 *
 * FIX v5:
 *  - Skip <option> (nggak punya rect)
 *  - <select> → TextLabel
 *  - Deteksi toggle switch UI
 *  - Skip knob dari toggle switch (biar nggak jadi node terpisah)
 *  - Fallback size toggle switch
 *  - Reset selfHidden kalau parent hidden
 */
class Parser {
    private $W;
    private $H;

    private $supported = [
        'div','span','p','button','input','img','h1','h2','h3','h4','h5','h6',
        'a','label','ul','ol','li','br','hr','section','header','footer','main',
        'nav','aside','article','textarea','select'
    ];

    public function __construct($w = 800, $h = 600) {
        $this->W = $w;
        $this->H = $h;
    }

    public function parse($html, $rectMap = []) {
        $wrapped = "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>{$html}</body></html>";

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $nodes = [];
        $idx = 0;
        $rectQueue = array_values($rectMap);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) return [];

        $defaultColor = ['r'=>0, 'g'=>0, 'b'=>0, 'a'=>1];

        foreach ($body->childNodes as $child) {
            $this->walk($child, null, $nodes, $idx, false, $rectQueue, $defaultColor, false);
        }

        $this->relativizePositions($nodes);
        return $nodes;
    }

    private function relativizePositions(&$nodes) {
        $absX = []; $absY = [];
        foreach ($nodes as $n) {
            $absX[$n['id']] = $n['x'];
            $absY[$n['id']] = $n['y'];
        }
        foreach ($nodes as &$n) {
            if ($n['parentId'] === null) continue;
            $pid = $n['parentId'];
            if (!isset($absX[$pid])) continue;
            $n['x'] = $absX[$n['id']] - $absX[$pid];
            $n['y'] = $absY[$n['id']] - $absY[$pid];
        }
        unset($n);
    }

    private function walk($el, $parentId, &$nodes, &$idx, $parentHidden, &$rectQueue, $inheritedColor, $parentIsToggleSwitch = false) {
        if (!($el instanceof DOMElement)) return;

        $tag = strtolower($el->tagName);

        // FIX: skip <option>
        if ($tag === 'option') return;

        if (!in_array($tag, $this->supported, true)) {
            foreach ($el->childNodes as $c) {
                $this->walk($c, $parentId, $nodes, $idx, $parentHidden, $rectQueue, $inheritedColor, $parentIsToggleSwitch);
            }
            return;
        }

        $rect = array_shift($rectQueue);
        if (!is_array($rect)) {
            $rect = ['x'=>0, 'y'=>0, 'w'=>0, 'h'=>0, 'selfHidden'=>false];
        }

        $style = $this->parseInlineStyle($el->getAttribute('style'));

        $x = (int)($rect['x'] ?? 0);
        $y = (int)($rect['y'] ?? 0);
        $w = (int)($rect['w'] ?? 0);
        $h = (int)($rect['h'] ?? 0);
        $isSelfHidden = !empty($rect['selfHidden']);

        if ($isSelfHidden && $parentHidden) {
            $isSelfHidden = false;
        }

        $text = '';
        foreach ($el->childNodes as $c) {
            if ($c instanceof DOMText) $text .= $c->textContent;
        }
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if ($isSelfHidden && ($w === 0 || $h === 0)) {
            $fontPx = $this->parsePx($style['font-size'] ?? null) ?? 16;
            if (in_array($tag, ['div','span','p','h1','h2','h3','h4','h5','h6','label','li']) && $text !== '') {
                if ($w === 0) $w = max(50, (int)(strlen($text) * $fontPx * 0.6));
                if ($h === 0) $h = max(14, $fontPx);
            }
        }

        $bg = $this->parseColor($style['background'] ?? $style['background-color'] ?? 'transparent');
        $gradient = $this->parseGradient($style['background'] ?? $style['background-image'] ?? '');

        if ($gradient && !empty($gradient['keypoints'])) {
            if ($bg['a'] < 0.01 || ($bg['r'] < 0.01 && $bg['g'] < 0.01 && $bg['b'] < 0.01)) {
                $first = $gradient['keypoints'][0]['color'];
                $avg = 0;
                $n = count($gradient['keypoints']);
                foreach ($gradient['keypoints'] as $kp) $avg += $kp['color']['a'];
                $avg /= $n;
                $bg = ['r'=>$first['r'], 'g'=>$first['g'], 'b'=>$first['b'], 'a'=>max(0.01, $avg)];
            }
        }

        if (isset($style['color']) && $style['color'] !== '') {
            $fg = $this->parseColor($style['color']);
        } else {
            $fg = $inheritedColor;
        }

        $border = $this->parseColor($style['border-color'] ?? $style['border-top-color'] ?? 'transparent');

        $radii = [
            'TL' => $this->parsePx($style['border-top-left-radius']     ?? $style['border-radius'] ?? null) ?? 0,
            'TR' => $this->parsePx($style['border-top-right-radius']    ?? $style['border-radius'] ?? null) ?? 0,
            'BR' => $this->parsePx($style['border-bottom-right-radius'] ?? $style['border-radius'] ?? null) ?? 0,
            'BL' => $this->parsePx($style['border-bottom-left-radius']  ?? $style['border-radius'] ?? null) ?? 0,
        ];

        $positionMode = $style['position'] ?? 'static';

        $explicitName =
            $el->getAttribute('data-name') ?:
            $el->getAttribute('aria-label') ?:
            $el->getAttribute('id') ?:
            $el->getAttribute('name') ?: '';

        $id = $el->getAttribute('id');
        $class = $el->getAttribute('class');

        $dataAction = strtolower($el->getAttribute('data-action'));
        $dataTarget = $el->getAttribute('data-target');

        $isToggleSwitch = $this->isToggleSwitch($el, $style);

        // FIX: skip knob dari toggle switch — biar nggak jadi node terpisah
        if ($parentIsToggleSwitch) {
            return;
        }

        // FIX: fallback size toggle switch
        if ($isToggleSwitch && ($w === 0 || $h === 0)) {
            $w = $w ?: 42;
            $h = $h ?: 24;
        }

        $node = [
            'id' => ++$idx,
            'parentId' => $parentId,
            'tag' => $tag,
            'robloxClass' => $this->mapClass($tag, $style, $el),
            'name' => '',
            'explicitName' => $explicitName,
            'sourceId' => $id,
            'alt' => $tag === 'img' ? $el->getAttribute('alt') : '',

            'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h,
            'positionMode' => $positionMode,

            'visible' => !$isSelfHidden,
            'selfHidden' => $isSelfHidden,
            'parentHidden' => $parentHidden,

            'bg' => $bg,
            'gradient' => $gradient,
            'fg' => $fg,
            'borderColor' => $border,
            'borderW' => $this->parsePx($style['border-width'] ?? $style['border-top-width'] ?? null) ?? 0,

            'radius' => max($radii),
            'radiusUniform' => count(array_unique($radii)) === 1,
            'radiusCorners' => $radii,

            'fontSize' => $this->parsePx($style['font-size'] ?? null) ?? 16,
            'fontWeight' => $style['font-weight'] ?? '400',
            'textAlign' => $style['text-align'] ?? 'left',

            'padL' => $this->parsePx($style['padding-left']   ?? $this->firstPad($style['padding'] ?? null) ?? null) ?? 0,
            'padR' => $this->parsePx($style['padding-right']  ?? $this->firstPad($style['padding'] ?? null) ?? null) ?? 0,
            'padT' => $this->parsePx($style['padding-top']    ?? $this->firstPad($style['padding'] ?? null) ?? null) ?? 0,
            'padB' => $this->parsePx($style['padding-bottom'] ?? $this->firstPad($style['padding'] ?? null) ?? null) ?? 0,

            'opacity' => (float)($style['opacity'] ?? 1),
            'transition' => $this->parseTransition($style['transition'] ?? ''),
            'cursor' => $style['cursor'] ?? 'default',
            'isButtonLike' => $this->isButtonLike($tag, $style, $el),
            'isToggleSwitch' => $isToggleSwitch,
            'unsupported' => $this->detectUnsupported($style),

            'action' => $dataAction,
            'target' => $dataTarget,

            'text' => $text,
            'value' => $el->getAttribute('value'),
            'placeholder' => $el->getAttribute('placeholder'),
            'src' => $el->getAttribute('src'),
            'children' => [],
        ];

        $this->inferAction($node, $id, $class);

        $nodes[] = $node;
        $newId = $node['id'];

        foreach ($el->childNodes as $c) {
            $this->walk($c, $newId, $nodes, $idx, $isSelfHidden || $parentHidden, $rectQueue, $fg, $isToggleSwitch);
        }
    }

    private function isToggleSwitch(DOMElement $el, array $style): bool {
        $class = strtolower($el->getAttribute('class'));
        if (preg_match('/\bsetting[-_]?toggle\b|\btoggle[-_]?switch\b|\bswitch\b/i', $class)) {
            return true;
        }

        if (($style['cursor'] ?? '') === 'pointer') {
            $w = $this->parsePx($style['width'] ?? null) ?? 0;
            $h = $this->parsePx($style['height'] ?? null) ?? 0;
            if ($w > 0 && $h > 0 && $w <= 60 && $h <= 30) {
                $childCount = 0;
                foreach ($el->childNodes as $c) {
                    if ($c instanceof DOMElement) $childCount++;
                }
                if ($childCount >= 1) return true;
            }
        }
        return false;
    }

    private function inferAction(array &$node, string $id, string $class): void {
        if (!empty($node['action'])) return;

        if (!empty($node['isToggleSwitch'])) {
            $node['action'] = 'switch';
            $node['target'] = '';
            return;
        }

        $hay = strtolower($id . ' ' . $class);
        if (trim($hay) === '') return;

        if (preg_match('/\b(close|dismiss|cancel|backdrop|overlay)\b/', $hay)) {
            $node['action'] = 'close';
            if (preg_match('/^([a-z0-9]+)[-_](close|dismiss|backdrop|overlay)/i', $id, $m)) {
                $node['target'] = $m[1] . '-panel';
            }
            return;
        }

        $isToggle = false;
        if (preg_match('/\b(toggle|hamburger|burger|gear|cog)\b/', $hay)) {
            $isToggle = true;
        } elseif (preg_match('/[-_](toggle|btn|button|icon|menu|nav)$/i', $id)) {
            $isToggle = true;
        }

        if ($isToggle) {
            $node['action'] = 'toggle';
            if (preg_match('/^([a-z0-9]+)[-_](toggle|open|btn|button|menu|nav|gear|cog)/i', $id, $m)) {
                $node['target'] = $m[1] . '-panel';
            } elseif (preg_match('/([a-z0-9]+)[-_]toggle/i', $id, $m)) {
                $node['target'] = $m[1] . '-panel';
            }
            return;
        }

        if (preg_match('/\btab\b/', $hay)) {
            $node['action'] = 'tab';
            if (preg_match('/tab[-_]?([a-z0-9]+)/i', $id, $m)) {
                $node['target'] = 'content-' . $m[1];
            }
            return;
        }

        if (preg_match('/\b(select|pick|choose)\b/', $hay)) $node['action'] = 'select';
        if (preg_match('/\b(play|pause)\b/', $hay))           $node['action'] = 'play';
    }

    private function parseInlineStyle($css) {
        $out = [];
        if (!$css) return $out;
        foreach (explode(';', $css) as $rule) {
            if (strpos($rule, ':') === false) continue;
            $parts = explode(':', $rule, 2);
            $k = trim(strtolower($parts[0]));
            $v = trim($parts[1]);
            if ($k === '') continue;
            $out[$k] = $v;
        }
        return $out;
    }

    private function parsePx($val) {
        if ($val === null || $val === '') return null;
        if (is_int($val) || is_float($val)) return (int)$val;
        if (preg_match('/^(-?[\d.]+)\s*(px|pt)?$/i', trim($val), $m)) return (int)round((float)$m[1]);
        if (preg_match('/^(-?[\d.]+)\s*em$/i', trim($val), $m)) return (int)round((float)$m[1] * 16);
        return null;
    }

    private function firstPad($val) {
        if (!$val) return null;
        $parts = preg_split('/\s+/', trim($val));
        return $parts[0] ?? null;
    }

    private function parseColor($css) {
        if (!$css || $css === 'transparent' || $css === 'none') {
            return ['r'=>0,'g'=>0,'b'=>0,'a'=>0];
        }
        $css = trim($css);

        if (preg_match('/rgba?\(([^)]+)\)/i', $css, $m)) {
            $p = array_map('trim', explode(',', $m[1]));
            if (count($p) === 1 && strpos($p[0], ' ') !== false) $p = preg_split('/\s+/', trim($p[0]));
            return [
                'r' => ((float)($p[0] ?? 0)) / 255,
                'g' => ((float)($p[1] ?? 0)) / 255,
                'b' => ((float)($p[2] ?? 0)) / 255,
                'a' => isset($p[3]) ? (float)$p[3] : 1,
            ];
        }

        if (preg_match('/^#([0-9a-f]{3,8})$/i', $css, $m)) {
            $h = $m[1];
            if (strlen($h) === 3) $h = $h[0].$h[0] . $h[1].$h[1] . $h[2].$h[2];
            if (strlen($h) === 6) {
                return [
                    'r' => hexdec(substr($h, 0, 2)) / 255,
                    'g' => hexdec(substr($h, 2, 2)) / 255,
                    'b' => hexdec(substr($h, 4, 2)) / 255,
                    'a' => 1,
                ];
            }
            if (strlen($h) === 8) {
                return [
                    'r' => hexdec(substr($h, 0, 2)) / 255,
                    'g' => hexdec(substr($h, 2, 2)) / 255,
                    'b' => hexdec(substr($h, 4, 2)) / 255,
                    'a' => hexdec(substr($h, 6, 2)) / 255,
                ];
            }
        }

        if (preg_match('/hsla?\(([^)]+)\)/i', $css, $m)) {
            $p = array_map('trim', explode(',', $m[1]));
            $h = ((float)$p[0]) / 360;
            $s = ((float)($p[1] ?? 0)) / 100;
            $l = ((float)($p[2] ?? 0)) / 100;
            $a = isset($p[3]) ? (float)$p[3] : 1;
            return $this->hslToRgb($h, $s, $l, $a);
        }

        $named = [
            'white' => [1,1,1], 'black' => [0,0,0], 'red' => [1,0,0],
            'green' => [0,0.5,0], 'blue' => [0,0,1], 'yellow' => [1,1,0],
            'gray' => [0.5,0.5,0.5], 'grey' => [0.5,0.5,0.5],
        ];
        $lower = strtolower($css);
        if (isset($named[$lower])) {
            [$r, $g, $b] = $named[$lower];
            return ['r'=>$r, 'g'=>$g, 'b'=>$b, 'a'=>1];
        }

        return ['r'=>0, 'g'=>0, 'b'=>0, 'a'=>1];
    }

    private function hslToRgb($h, $s, $l, $a = 1) {
        $r = $l; $g = $l; $b = $l;
        if ($s > 0) {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hue2rgb($p, $q, $h + 1/3);
            $g = $this->hue2rgb($p, $q, $h);
            $b = $this->hue2rgb($p, $q, $h - 1/3);
        }
        return ['r'=>$r, 'g'=>$g, 'b'=>$b, 'a'=>$a];
    }
    private function hue2rgb($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    private function parseGradient($bg) {
        if (!$bg || stripos($bg, 'linear-gradient') === false) return null;
        if (!preg_match('/linear-gradient\s*\(\s*([^,]+?)\s*,\s*(.+)\)\s*$/is', $bg, $m)) return null;

        $head = trim($m[1]);
        $stopsRaw = trim($m[2]);

        $rotCss = 180;
        if (preg_match('/(-?[\d.]+)deg/i', $head, $d)) $rotCss = (float)$d[1];
        elseif (preg_match('/^to\s+top\s+right/i', $head)) $rotCss = 45;
        elseif (preg_match('/^to\s+bottom\s+right/i', $head)) $rotCss = 135;
        elseif (preg_match('/^to\s+bottom\s+left/i', $head)) $rotCss = 225;
        elseif (preg_match('/^to\s+top\s+left/i', $head)) $rotCss = 315;
        elseif (preg_match('/^to\s+top/i', $head)) $rotCss = 0;
        elseif (preg_match('/^to\s+right/i', $head)) $rotCss = 90;
        elseif (preg_match('/^to\s+bottom/i', $head)) $rotCss = 180;
        elseif (preg_match('/^to\s+left/i', $head)) $rotCss = 270;

        $rotRoblox = (($rotCss - 90) % 360 + 360) % 360;

        $stops = [];
        $buf = '';
        $depth = 0;
        $len = strlen($stopsRaw);
        for ($i = 0; $i < $len; $i++) {
            $c = $stopsRaw[$i];
            if ($c === '(') $depth++;
            elseif ($c === ')') $depth--;
            if ($c === ',' && $depth === 0) {
                $stops[] = trim($buf);
                $buf = '';
            } else {
                $buf .= $c;
            }
        }
        if (trim($buf) !== '') $stops[] = trim($buf);

        $keypoints = [];
        foreach ($stops as $s) {
            $pos = null;
            if (preg_match('/\s+(-?[\d.]+)(%|px)?\s*$/', $s, $pm)) {
                $s = trim(substr($s, 0, -strlen($pm[0])));
                if (($pm[2] ?? '') === '%') $pos = ((float)$pm[1]) / 100;
                elseif (($pm[2] ?? '') === 'px') $pos = null;
                else $pos = (float)$pm[1];
            }
            $keypoints[] = ['color' => $this->parseColor($s), 'pos' => $pos];
        }

        $n = count($keypoints);
        if ($n === 0) return null;

        foreach ($keypoints as $i => &$kp) {
            if ($kp['pos'] === null) $kp['pos'] = $n === 1 ? 0 : $i / ($n - 1);
        }
        unset($kp);

        usort($keypoints, function($a, $b) { return $a['pos'] <=> $b['pos']; });

        return [
            'rotation' => $rotRoblox,
            'rotationCss' => $rotCss,
            'keypoints' => $keypoints,
        ];
    }

    private function parseTransition($css) {
        if (!$css || $css === 'none') return null;
        if (preg_match('/([\d.]+)(m?s)/', $css, $m)) {
            $sec = (float)$m[1];
            if ($m[2] === 'ms') $sec /= 1000;
            if ($sec <= 0) return null;
            $ease = 'ease';
            if (preg_match('/(cubic-bezier\([^)]+\)|ease-in-out|ease-in|ease-out|linear|ease)/i', $css, $em)) $ease = $em[1];
            return ['duration' => $sec, 'easing' => $ease];
        }
        return null;
    }

    private function isButtonLike($tag, $style, $el) {
        if (in_array($tag, ['button', 'a'], true)) return true;
        if (($style['cursor'] ?? '') === 'pointer') return true;
        $role = strtolower($el->getAttribute('role'));
        if (in_array($role, ['button', 'link'], true)) return true;
        if ($el->hasAttribute('onclick')) return true;
        $idClass = strtolower($el->getAttribute('id') . ' ' . $el->getAttribute('class') . ' ' . $el->getAttribute('name'));
        if (preg_match('/\b(button|btn|toggle|clickable|action|submit|close|backdrop)\b/', $idClass)) return true;
        if ($el->hasAttribute('data-action')) return true;
        return false;
    }

    private function mapClass($tag, $style, $el) {
        // FIX: overflow-y auto/scroll → ScrollingFrame
        $overflowY = $style['overflow-y'] ?? $style['overflow'] ?? '';
        if (stripos($overflowY, 'auto') !== false || stripos($overflowY, 'scroll') !== false) {
            return 'ScrollingFrame';
        }

        if ($tag === 'select') return 'TextLabel';
        if ($this->isButtonLike($tag, $style, $el)) return 'TextButton';
        switch ($tag) {
            case 'button': case 'a': return 'TextButton';
            case 'input': case 'textarea': return 'TextBox';
            case 'img': return 'ImageLabel';
            case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
            case 'p': case 'span': case 'label': case 'li': return 'TextLabel';
            case 'div':
                $hasText = false;
                foreach ($el->childNodes as $c) {
                    if ($c instanceof DOMText && trim($c->textContent) !== '') { $hasText = true; break; }
                }
                $childElements = 0;
                foreach ($el->childNodes as $c) {
                    if ($c instanceof DOMElement) $childElements++;
                }
                if ($hasText && $childElements === 0) return 'TextLabel';
                return 'Frame';
            default: return 'Frame';
        }
    }

    private function detectUnsupported($style) {
        $issues = [];
        $map = [
            'backdrop-filter' => 'Roblox tidak punya backdrop blur per-UI.',
            'box-shadow'      => 'Roblox tidak punya drop-shadow native.',
            'filter'          => 'CSS filter tidak ada di Roblox.',
            'clip-path'       => 'Tidak ada equivalent.',
            'text-shadow'     => 'Pakai UIStroke + Transparency.',
            'mix-blend-mode'  => 'Tidak ada blend mode di Roblox UI.',
        ];
        foreach ($map as $prop => $reason) {
            if (!empty($style[$prop]) && $style[$prop] !== 'none') {
                $issues[] = ['label' => $prop, 'value' => $style[$prop], 'reason' => $reason];
            }
        }
        $bg = $style['background'] ?? $style['background-image'] ?? '';
        if (stripos($bg, 'radial-gradient') !== false) $issues[] = ['label' => 'radial-gradient', 'value' => substr($bg, 0, 60).'…', 'reason' => 'UIGradient hanya linear.'];
        if (stripos($bg, 'conic-gradient') !== false) $issues[] = ['label' => 'conic-gradient', 'value' => substr($bg, 0, 60).'…', 'reason' => 'Tidak ada conic di UIGradient.'];
        return $issues;
    }
}