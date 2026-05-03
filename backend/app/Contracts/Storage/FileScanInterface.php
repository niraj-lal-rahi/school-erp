<?php

namespace App\Contracts\Storage;

use Illuminate\Http\UploadedFile;

interface FileScanInterface
{
    public function scan(UploadedFile $file, array $context = []): FileScanResult;
}
