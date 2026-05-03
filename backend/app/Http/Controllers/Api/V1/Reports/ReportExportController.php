<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Reports\ReportExport;
use App\Services\Security\SecureFileDownloadService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        protected SecureFileDownloadService $downloads,
    ) {
    }

    public function download(Request $request, ReportExport $reportExport): StreamedResponse
    {
        $this->authorize('export', $reportExport);

        $reportExport->forceFill([
            'downloaded_count' => (int) $reportExport->downloaded_count + 1,
            'last_downloaded_at' => now(),
        ])->save();

        return $this->downloads->downloadReportExport($reportExport, $request);
    }
}
