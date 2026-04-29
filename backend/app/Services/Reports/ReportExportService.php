<?php

namespace App\Services\Reports;

use App\Models\Reports\ReportExport;
use App\Models\Reports\ReportRun;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ReportExportService
{
    public function export(ReportRun $reportRun, array $data, string $fileType): ReportExport
    {
        $fileType = strtolower($fileType);

        if (! in_array($fileType, ['csv', 'xlsx', 'pdf', 'json'], true)) {
            throw ValidationException::withMessages([
                'file_type' => ['Unsupported export file type.'],
            ]);
        }

        $relativeDirectory = 'reports/'.$reportRun->school_id.'/'.now()->format('Y/m/d');
        $fileBase = Str::slug($reportRun->reportDefinition?->code ?: 'report-run-'.$reportRun->id).'-'.$reportRun->id;
        $fileName = $fileBase.'.'.$fileType;
        $relativePath = $relativeDirectory.'/'.$fileName;

        $content = match ($fileType) {
            'csv' => $this->buildCsv($data),
            'xlsx' => $this->buildXlsx($data),
            'pdf' => $this->buildPdf($data, $reportRun->reportDefinition?->name ?: 'Report'),
            default => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        };

        Storage::disk('local')->put($relativePath, $content);

        $export = ReportExport::query()->create([
            'school_id' => $reportRun->school_id,
            'report_run_id' => $reportRun->id,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'file_size' => Storage::disk('local')->size($relativePath),
            'mime_type' => $this->mimeTypeFor($fileType),
            'downloaded_count' => 0,
            'last_downloaded_at' => null,
        ]);

        $reportRun->update([
            'file_path' => $relativePath,
            'file_type' => $fileType,
        ]);

        return $export->fresh();
    }

    protected function buildCsv(array $data): string
    {
        $stream = fopen('php://temp', 'r+');
        [$summaryRows, $tables] = $this->normalizeDataForExport($data);

        if ($summaryRows !== []) {
            fputcsv($stream, ['Metric', 'Value']);
            foreach ($summaryRows as [$metric, $value]) {
                fputcsv($stream, [$metric, $value]);
            }
            fputcsv($stream, []);
        }

        foreach ($tables as $table) {
            fputcsv($stream, [$table['title']]);

            $headers = $table['headers'];
            if ($headers !== []) {
                fputcsv($stream, $headers);
                foreach ($table['rows'] as $row) {
                    fputcsv($stream, array_map(fn ($header) => $row[$header] ?? '', $headers));
                }
            }

            fputcsv($stream, []);
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $content;
    }

    protected function buildXlsx(array $data): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages([
                'file_type' => ['The ZipArchive extension is required to generate XLSX exports.'],
            ]);
        }

        [$summaryRows, $tables] = $this->normalizeDataForExport($data);
        $sheetRows = [];

        if ($summaryRows !== []) {
            $sheetRows[] = ['Metric', 'Value'];
            foreach ($summaryRows as [$metric, $value]) {
                $sheetRows[] = [$metric, $value];
            }
            $sheetRows[] = [];
        }

        foreach ($tables as $table) {
            $sheetRows[] = [$table['title']];
            if ($table['headers'] !== []) {
                $sheetRows[] = $table['headers'];
                foreach ($table['rows'] as $row) {
                    $sheetRows[] = array_map(fn ($header) => $row[$header] ?? '', $table['headers']);
                }
            }
            $sheetRows[] = [];
        }

        $sheetXml = $this->buildWorksheetXml($sheetRows);
        $tempFile = tempnam(sys_get_temp_dir(), 'report_xlsx_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
        $binary = file_get_contents($tempFile) ?: '';
        @unlink($tempFile);

        return $binary;
    }

    protected function buildPdf(array $data, string $title): string
    {
        [$summaryRows, $tables] = $this->normalizeDataForExport($data);
        $lines = [$title, 'Generated: '.now()->toDateTimeString(), ''];

        foreach ($summaryRows as [$metric, $value]) {
            $lines[] = $metric.': '.$value;
        }

        foreach ($tables as $table) {
            $lines[] = '';
            $lines[] = strtoupper($table['title']);

            if ($table['headers'] !== []) {
                $lines[] = implode(' | ', $table['headers']);
                foreach ($table['rows'] as $row) {
                    $lines[] = implode(' | ', array_map(fn ($header) => (string) ($row[$header] ?? ''), $table['headers']));
                }
            }
        }

        $content = "BT\n/F1 10 Tf\n40 800 Td\n";
        foreach ($lines as $index => $line) {
            $escaped = $this->pdfEscape(Str::limit($line, 180, ''));
            $content .= ($index === 0 ? '' : "T*\n")."({$escaped}) Tj\n";
        }
        $content .= "ET";

        return $this->wrapPdfDocument($content);
    }

    protected function normalizeDataForExport(array $data): array
    {
        $summaryRows = [];
        $tables = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && $this->isListOfAssocRows($value)) {
                $tables[] = [
                    'title' => Str::headline((string) $key),
                    'headers' => $this->collectHeaders($value),
                    'rows' => array_map(fn ($row) => $this->normalizeRow($row), $value),
                ];
                continue;
            }

            if (is_array($value) && ! array_is_list($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if (is_scalar($nestedValue) || $nestedValue === null) {
                        $summaryRows[] = [Str::headline((string) $key).' '.Str::headline((string) $nestedKey), $nestedValue];
                    } elseif (is_array($nestedValue) && $this->isListOfAssocRows($nestedValue)) {
                        $tables[] = [
                            'title' => Str::headline((string) $key).' '.Str::headline((string) $nestedKey),
                            'headers' => $this->collectHeaders($nestedValue),
                            'rows' => array_map(fn ($row) => $this->normalizeRow($row), $nestedValue),
                        ];
                    }
                }
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $summaryRows[] = [Str::headline((string) $key), $value];
            }
        }

        return [$summaryRows, $tables];
    }

    protected function normalizeRow(array $row): array
    {
        return collect($row)->map(function ($value) {
            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            return $value;
        })->all();
    }

    protected function collectHeaders(array $rows): array
    {
        $headers = [];
        foreach ($rows as $row) {
            $headers = array_values(array_unique(array_merge($headers, array_keys($row))));
        }

        return $headers;
    }

    protected function isListOfAssocRows(array $value): bool
    {
        return array_is_list($value) && $value !== [] && is_array($value[0]);
    }

    protected function mimeTypeFor(string $fileType): string
    {
        return match ($fileType) {
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            default => 'application/json',
        };
    }

    protected function buildWorksheetXml(array $rows): string
    {
        $xmlRows = [];
        foreach ($rows as $rowIndex => $row) {
            $cells = [];
            foreach (array_values($row) as $columnIndex => $value) {
                $cellRef = $this->excelColumnName($columnIndex + 1).($rowIndex + 1);

                if ($value === null || $value === '') {
                    $cells[] = '<c r="'.$cellRef.'" t="inlineStr"><is><t></t></is></c>';
                    continue;
                }

                if (is_numeric($value)) {
                    $cells[] = '<c r="'.$cellRef.'"><v>'.$value.'</v></c>';
                    continue;
                }

                $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cells[] = '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.$text.'</t></is></c>';
            }

            $xmlRows[] = '<row r="'.($rowIndex + 1).'">'.implode('', $cells).'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.implode('', $xmlRows).'</sheetData>'
            .'</worksheet>';
    }

    protected function xlsxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    protected function xlsxRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    protected function xlsxWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    protected function xlsxWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>';
    }

    protected function excelColumnName(int $columnNumber): string
    {
        $name = '';
        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $name = chr(65 + $modulo).$name;
            $columnNumber = (int) floor(($columnNumber - $modulo) / 26);
        }

        return $name;
    }

    protected function pdfEscape(string $value): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\(', '\)'],
            $value,
        );
    }

    protected function wrapPdfDocument(string $contentStream): string
    {
        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 5 0 R /Resources << /Font << /F1 4 0 R >> >> >> endobj';
        $objects[] = '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[] = '5 0 obj << /Length '.strlen($contentStream).' >> stream'."\n".$contentStream."\n".'endstream endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref'."\n";
        $pdf .= '0 '.(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= 'startxref'."\n";
        $pdf .= $xrefPosition."\n";
        $pdf .= '%%EOF';

        return $pdf;
    }
}
