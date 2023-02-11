<?php

namespace Orolyn\Console;

class HexViewerFormat
{
    /**
     * @param array<string, string> $literalColorMap
     */
    public function __construct(
        public readonly ?string $rowNumberColor = null,
        public readonly ?string $rowNumberLeadingZerosColor = null,
        public readonly int $hexColumnCount = 4,
        public readonly int $hexColumnWidth = 4,
        public readonly ?string $hexColumnColor = null,
        public readonly ?string $literalColor = null,
        public readonly ?array $literalColorMap = null
    ) {}

    public static function createFancy(
        ?string $rowNumberColor = null,
        ?string $rowNumberLeadingZerosColor = null,
        int $hexColumnCount = 4,
        int $hexColumnWidth = 4,
        ?string $hexColumnColor = null,
        array $literalColor = null,
        array $literalColorMap = [],
    ): HexViewerFormat {
        return new HexViewerFormat(
            $rowNumberColor,
            $rowNumberLeadingZerosColor,
            $hexColumnCount,
            $hexColumnWidth,
            $hexColumnColor,
            $literalColor,
            $literalColorMap,
        );
    }
}
