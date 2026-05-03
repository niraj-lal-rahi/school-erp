<?php

namespace App\Services\Security\Scanning;

use App\Contracts\Storage\FileScanInterface;
use App\Contracts\Storage\FileScanResult;
use Illuminate\Http\UploadedFile;

class NullFileScanner implements FileScanInterface
{
    public function scan(UploadedFile $file, array $context = []): FileScanResult
    {
        return FileScanResult::clean([
            'scanner' => 'null',
            'note' => 'No external virus scanner is configured for this environment.',
        ]);
    }
}
