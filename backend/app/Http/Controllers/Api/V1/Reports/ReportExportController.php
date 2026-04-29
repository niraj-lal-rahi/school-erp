<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Reports\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function download(Request $request, ReportExport $reportExport): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($reportExport->file_path), 404);

        $reportExport->forceFill([
            'downloaded_count' => (int) $reportExport->downloaded_count + 1,
            'last_downloaded_at' => now(),
        ])->save();

        return Storage::disk('local')->download(
            $reportExport->file_path,
            $reportExport->file_name,
            array_filter([
                'Content-Type' => $reportExport->mime_type,
            ]),
        );
    }
}
