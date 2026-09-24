<?php
// app/helpers/LuaHelper.php — formatter nilai Lua (string, Color3, sequence, easing)

class LuaHelper
{
    public static function mapEasing($css) {
        $css = strtolower(trim($css));
        if (str_contains($css, 'cubic-bezier')) return 'Enum.EasingStyle.Back, Enum.EasingDirection.Out';
        return [
            'linear'      => 'Enum.EasingStyle.Linear',
            'ease'        => 'Enum.EasingStyle.Quad, Enum.EasingDirection.Out',
            'ease-in'     => 'Enum.EasingStyle.Quad, Enum.EasingDirection.In',
            'ease-out'    => 'Enum.EasingStyle.Quad, Enum.EasingDirection.Out',
            'ease-in-out' => 'Enum.EasingStyle.Quad, Enum.EasingDirection.InOut',
        ][$css] ?? 'Enum.EasingStyle.Quad, Enum.EasingDirection.Out';
    }

    public static function colorSeq($kps) {
        $parts = [];
        foreach ($kps as $kp) {
            $t = max(0, min(1, $kp['pos']));
            $parts[] = sprintf('ColorSequenceKeypoint.new(%.3f, Color3.fromRGB(%d, %d, %d))',
                $t,
                round($kp['color']['r'] * 255),
                round($kp['color']['g'] * 255),
                round($kp['color']['b'] * 255));
        }
        return 'ColorSequence.new({' . implode(', ', $parts) . '})';
    }

    public static function numSeq($kps) {
        $parts = [];
        foreach ($kps as $kp) {
            $t = max(0, min(1, $kp['pos']));
            $a = 1 - $kp['color']['a'];
            $parts[] = sprintf('NumberSequenceKeypoint.new(%.3f, %.3f)', $t, $a);
        }
        return 'NumberSequence.new({' . implode(', ', $parts) . '})';
    }

    public static function q($s) { return '"' . addcslashes($s, "\\\"\n") . '"'; }
    public static function c3($c) { return sprintf('%.3f, %.3f, %.3f', $c['r'], $c['g'], $c['b']); }
}
