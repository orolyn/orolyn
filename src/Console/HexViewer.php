<?php

namespace Orolyn\Console;

use Orolyn\IO\Binary;
use Orolyn\IO\IOutputStream;

class HexViewer
{
    public static function dump(string $data, HexViewerFormat $format = new HexViewerFormat()): void
    {
        if (0 === Binary::getLength($data)) {
            return;
        }

        $output = '';

        $bytesPerLine = $format->hexColumnWidth * $format->hexColumnCount;
        $lines = str_split($data, $bytesPerLine);

        $lineNo = 0;

        foreach ($lines as $line) {
            $output .= str_pad(strtoupper(dechex($lineNo)), 8, '0', STR_PAD_LEFT) . ' | ';
            $lineNo += $bytesPerLine;

            $output .= str_pad(
                implode(
                    ' ',
                    array_map(
                        fn ($group) => str_pad(strtoupper(bin2hex($group)), 2, '0', STR_PAD_LEFT),
                        str_split($line, $format->hexColumnWidth)
                    )
                ),
                ($bytesPerLine * 2) + ($format->hexColumnCount - 1)
            );

            $output .= ' | ';

            $actualLineSize = Binary::getLength($line);

            for ($i = 0; $i < $actualLineSize; $i++) {
                $ord = ord($line[$i]);
                $output .= $ord >= 32 && $ord <= 126 ? $line[$i] : '.';
            }

            $output .= "\n";
        }

        echo $output;
    }
}
