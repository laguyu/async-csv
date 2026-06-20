<?php

namespace App\Contracts;

interface DataProcessorInterface
{
    public function process(string $filePath, int $importId): void;
}
