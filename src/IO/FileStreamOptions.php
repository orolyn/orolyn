<?php

namespace Orolyn\IO;

class FileStreamOptions
{
    public function __construct(
        public FileMode $fileMode = FileMode::Open,
        public FileAccess $fileAccess = FileAccess::Read,
        public bool $hasEOF = true
    ) {
    }
}
