<?php

namespace App\Helpers;

class BarcodeGenerator
{
    /**
     * Generate Code 128 barcode image with proper encoding
     */
    public static function generateCode128($text, $width = 2, $height = 50)
    {
        if (!function_exists('imagecreate')) {
            return null;
        }

        // Code 128 B character set (most common)
        $code128B = [
            ' ' => '11011001100', '!' => '11001101100', '"' => '11001100110', '#' => '10010011000',
            '$' => '10010001100', '%' => '10001001100', '&' => '10011001000', "'" => '10011000100',
            '(' => '10001100100', ')' => '11001001000', '*' => '11001000100', '+' => '11000100100',
            ',' => '10110011100', '-' => '10011011100', '.' => '10011001110', '/' => '10111001100',
            '0' => '10011101100', '1' => '10011100110', '2' => '11001110010', '3' => '11001011100',
            '4' => '11001001110', '5' => '11011100100', '6' => '11001110100', '7' => '11101101110',
            '8' => '11101001100', '9' => '11100101100', ':' => '11100100110', ';' => '11101100100',
            '<' => '11100110100', '=' => '11100110010', '>' => '11011011000', '?' => '11011000110',
            '@' => '11000110110', 'A' => '10100011000', 'B' => '10001011000', 'C' => '10001000110',
            'D' => '10110001000', 'E' => '10001101000', 'F' => '10001100010', 'G' => '11010001000',
            'H' => '11000101000', 'I' => '11000100010', 'J' => '10110111000', 'K' => '10110001110',
            'L' => '10001101110', 'M' => '10111011000', 'N' => '10111000110', 'O' => '10001110110',
            'P' => '11101110110', 'Q' => '11010001110', 'R' => '11000101110', 'S' => '11011101000',
            'T' => '11011100010', 'U' => '11011101110', 'V' => '11101011000', 'W' => '11101000110',
            'X' => '11100010110', 'Y' => '11101101000', 'Z' => '11101100010'
        ];

        // Start code B
        $startCode = '11010010000';
        // Stop code
        $stopCode = '1100011101011';

        // Build barcode pattern
        $pattern = $startCode;
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (isset($code128B[$char])) {
                $pattern .= $code128B[$char];
            }
        }
        $pattern .= $stopCode;

        // Calculate width
        $totalWidth = strlen($pattern) * $width;
        $quietZone = 10 * $width;
        $imageWidth = $totalWidth + ($quietZone * 2);

        // Create image
        $img = imagecreate($imageWidth, $height);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        // Fill with white
        imagefill($img, 0, 0, $white);

        // Draw barcode starting after quiet zone
        $x = $quietZone;
        for ($i = 0; $i < strlen($pattern); $i++) {
            $bit = $pattern[$i];
            if ($bit == '1') {
                imagefilledrectangle($img, $x, 0, $x + $width - 1, $height - 1, $black);
            }
            $x += $width;
        }

        // Output as base64
        ob_start();
        imagepng($img);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($img);

        return base64_encode($imageData);
    }

    /**
     * Generate simple barcode using bars (fallback)
     */
    public static function generateSimpleBarcode($text, $width = 2, $height = 50)
    {
        if (!function_exists('imagecreate')) {
            return null;
        }

        // Use Code 128 if possible
        return self::generateCode128($text, $width, $height);
    }
}
