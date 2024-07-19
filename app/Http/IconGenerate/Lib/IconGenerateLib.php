<?php

namespace App\Http\IconGenerate\Lib;

class IconGenerateLib
{
    const Debug = false;

    static function generate($backColor, $txtColor, $size, $padding, $space, $text, $type = 'en', $bold = true)
    {
        if (self::Debug) {
            $backColor = '#ffffff';
            $txtColor = '#ff0000';
//
//            $text = '我爱你附加额外i发';
//            $type = 'zh';
        }
        $img = imagecreate($size, $size);
        $backColor = self::hex2ARGB($backColor);
        $txtColor = self::hex2ARGB($txtColor);
        $backColor = imagecolorallocatealpha($img, $backColor['r'], $backColor['g'], $backColor['b'], $backColor['a'] / 2);
        $txtColor = imagecolorallocate($img, $txtColor['r'], $txtColor['g'], $txtColor['b']);
        $rectColor = imagecolorallocate($img, 0, 255, 0); // for debug
        $lineColor = imagecolorallocate($img, 0, 0, 255); // for debug

        $text = trim($text);
        $strLen = mb_strlen($text);
        $ratio = 2;
        $fontName = 'ubun';
        if ($type == 'zh') {
            $ratio = 1;
            $fontName = 'yahei';
        } else if ($bold) {
            $fontName = 'ubunbold';
        }
        $countPerRow = ceil(sqrt($strLen * 1.0 / $ratio));
        $rowCount = ceil($strLen * 1.0 / $countPerRow / $ratio);
        $unitWidth = floor((($size - $padding * 2.0) - ($countPerRow * $ratio - 1) * $space) / $countPerRow / $ratio) * $ratio;
        $fontSize = ceil($type == 'en' ? ($unitWidth / 5 * 4) : ($unitWidth / 5 * 4));

        $charList = [];
        for ($j = 0; $j < $rowCount; $j++) {
            $row = [];
            for ($i = 0; $i < $countPerRow; $i++) {
                for ($k = 0; $k < $ratio; $k++) {
                    $index = (int)($j * $countPerRow * $ratio + $i * $ratio + $k);
                    if ($index < $strLen) {
                        $row[] = mb_substr($text, $index, 1);
                    }
                }
            }
            $charList[] = $row;
        }

        if ($rowCount == 1) {
            $spaceX = ceil(($size - ($unitWidth * 1.0 / $ratio) * count($charList[0])) / (count($charList[0]) + 1));
            $y = ceil(($size - $unitWidth) * 1.0 / 2);
            $index = 0;
            foreach ($charList[0] as $item) {
                $x = ceil($spaceX * ($index + 1) + ($unitWidth * 1.0 / $ratio) * $index);
                self::drawText($img, $fontName, $fontSize, $unitWidth, $ratio, $x, $y, $txtColor, $item, $rectColor, $lineColor);
                $index++;
            }
        } else {
            $spaceY = ceil(($size - $unitWidth * $rowCount) * 1.0 / ($rowCount + 1));
            $rIndex = 0;
            foreach ($charList as $row) {
                $spaceX = ceil(($size - ($unitWidth * 1.0 / $ratio) * count($row)) / (count($row) + 1));
                $y = $spaceY * ($rIndex + 1) + $unitWidth * $rIndex;
                $index = 0;
                foreach ($row as $item) {
                    $x = ceil($spaceX * ($index + 1) + ($unitWidth * 1.0 / $ratio) * $index);
                    self::drawText($img, $fontName, $fontSize, $unitWidth, $ratio, $x, $y, $txtColor, $item, $rectColor, $lineColor);
                    $index++;
                }
                $rIndex++;
            }
        }

        imagepng($img, storage_path('icongenerate/' . time() . '.png'));
        imagedestroy($img);

    }

    private static function drawText($img, $fontName, $fontSize, $unitWidth, $ratio, $x, $y, $color, $text, $rectColor, $lineColor)
    {
        if (self::Debug) {
            imagefilledrectangle($img, $x, $y, ($x + $unitWidth * 1.0 / $ratio), $y + $unitWidth, $rectColor);
        }
        $pos = imagettfbbox($fontSize, 0, $fontName, $text);
        $w = $pos[2] - $pos[0];
        $h = $pos[1] - $pos[5];
        $ax = $x - $pos[0] - ($w - $unitWidth * 1.0 / $ratio) / 2; // 中点对齐
        if ($ratio == 2) {
            $ay = $y + $unitWidth - $pos[1] - $unitWidth / 6; // 英文：底部对齐，向上偏移1/5
        } else {
            $ay = $y + $unitWidth - $pos[1] - ($unitWidth * 1.0 - $h) / 2; // 中文：居中对齐
        }
        imagettftext($img, $fontSize, 0, $ax, $ay, $color, $fontName, $text);
        if (self::Debug) {
            $ax = $ax + $pos[0];
            $ay = $ay + $pos[1];
            imageline($img, $ax, $ay, $ax + $w, $ay, $lineColor);
            imageline($img, $ax + $w, $ay, $ax + $w, $ay - $h, $lineColor);
            imageline($img, $ax + $w, $ay - $h, $ax, $ay - $h, $lineColor);
            imageline($img, $ax, $ay - $h, $ax, $ay, $lineColor);
            imageline($img, $ax, $ay, $ax + $w, $ay - $h, $lineColor);
            imageline($img, $ax, $ay - $h, $ax + $w, $ay, $lineColor);
        }
    }

    private static function hex2ARGB($hexColor)
    {
        $color = str_replace('#', '', $hexColor);

        if (strlen($color) == 6) {
            $rgb = array(
                'a' => 0,
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else if (strlen($color) == 8) {
            $rgb = array(
                'a' => hexdec(substr($color, 0, 2)),
                'r' => hexdec(substr($color, 2, 2)),
                'g' => hexdec(substr($color, 4, 2)),
                'b' => hexdec(substr($color, 6, 2))
            );
        } else {
            $rgb = array('a' => 0, 'r' => 0, 'g' => 0, 'b' => 0);
        }
        return $rgb;
    }
}
