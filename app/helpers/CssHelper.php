<?php
// app/helpers/CssHelper.php — parser nilai CSS (style inline, warna, gradient, transition)

class CssHelper
{
    public static function parseInlineStyle($css) {
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

    public static function parsePx($val) {
        if ($val === null || $val === '') return null;
        if (is_int($val) || is_float($val)) return (int)$val;
        if (preg_match('/^(-?[\d.]+)\s*(px|pt)?$/i', trim($val), $m)) return (int)round((float)$m[1]);
        if (preg_match('/^(-?[\d.]+)\s*em$/i', trim($val), $m)) return (int)round((float)$m[1] * 16);
        return null;
    }

    public static function firstPad($val) {
        if (!$val) return null;
        $parts = preg_split('/\s+/', trim($val));
        return $parts[0] ?? null;
    }

    public static function parseColor($css) {
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
            return self::hslToRgb($h, $s, $l, $a);
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

    public static function hslToRgb($h, $s, $l, $a = 1) {
        $r = $l; $g = $l; $b = $l;
        if ($s > 0) {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = self::hue2rgb($p, $q, $h + 1/3);
            $g = self::hue2rgb($p, $q, $h);
            $b = self::hue2rgb($p, $q, $h - 1/3);
        }
        return ['r'=>$r, 'g'=>$g, 'b'=>$b, 'a'=>$a];
    }
    private static function hue2rgb($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    public static function parseGradient($bg) {
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
            $keypoints[] = ['color' => self::parseColor($s), 'pos' => $pos];
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

    public static function parseTransition($css) {
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
}
