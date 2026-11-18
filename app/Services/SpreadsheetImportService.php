<?php

namespace App\Services;

use App\Imports\AttendanceImport;
use App\Imports\EmployeeImport;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetImportService
{
    /**
     * Allowed spreadsheet and data file extensions.
     */
    public const ALLOWED_EXTENSIONS = [
        'xlsx', 'xls', 'csv', 'tsv', 'txt', 'html', 'htm', 'xml',
    ];

    /**
     * Permissive list of MIME types for spreadsheets, text files, and HTML/XML tables.
     */
    public const ALLOWED_MIMES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/msexcel',
        'application/x-msexcel',
        'application/x-ms-excel',
        'application/x-excel',
        'application/x-dos_ms_excel',
        'application/xls',
        'application/x-xls',
        'application/octet-stream',
        'application/x-ole-storage',
        'application/vnd.ms-office',
        'application/zip',
        'text/csv',
        'text/plain',
        'text/tab-separated-values',
        'text/tsv',
        'text/html',
        'application/xhtml+xml',
        'application/xml',
        'text/xml',
    ];

    /**
     * Validate an uploaded spreadsheet file from the request.
     *
     * @throws ValidationException
     */
    public function validateSpreadsheet(Request $request, string $field = 'file'): UploadedFile
    {
        $request->validate([
            $field => [
                'required',
                'file',
                'max:51200', // 50MB max
                function ($attribute, $value, $fail) {
                    if (!$value instanceof UploadedFile) {
                        return $fail('A valid file must be uploaded.');
                    }

                    $ext = strtolower($value->getClientOriginalExtension());
                    if (in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                        return;
                    }

                    $mime = $value->getMimeType();
                    if (in_array($mime, self::ALLOWED_MIMES, true)) {
                        return;
                    }

                    $fail('The file must be an Excel spreadsheet (.xlsx, .xls), CSV, TSV, or biometric attendance export.');
                },
            ],
        ]);

        return $request->file($field);
    }

    /**
     * Import attendance records from an uploaded file or path.
     */
    public function importAttendance($file): AttendanceImport
    {
        $import = new AttendanceImport();
        $this->executeImport($import, $file);
        return $import;
    }

    /**
     * Import employees from an uploaded file or path.
     */
    public function importEmployees($file): EmployeeImport
    {
        $import = new EmployeeImport();
        $this->executeImport($import, $file);
        return $import;
    }

    /**
     * Execute an import with multi-engine reader detection and automated fallback.
     */
    protected function executeImport(object $import, $file): void
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : (string) $file;
        $clientExt = $file instanceof UploadedFile ? strtolower($file->getClientOriginalExtension()) : strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $primaryReader = $this->detectReaderType($filePath, $clientExt);

        // Build candidate list with primary reader first, followed by fallbacks
        $readers = array_unique(array_filter([
            $primaryReader,
            Excel::HTML,
            Excel::CSV,
            Excel::XLS,
            Excel::XLSX,
            Excel::XML,
        ]));

        $lastException = null;

        foreach ($readers as $reader) {
            try {
                ExcelFacade::import($import, $file, null, $reader);
                return; // Successful import
            } catch (\Throwable $e) {
                $lastException = $e;
                continue;
            }
        }

        throw new \RuntimeException(
            'Unable to read attendance spreadsheet. ' . ($lastException ? $lastException->getMessage() : 'Please check file format.'),
            0,
            $lastException
        );
    }

    /**
     * Detect best reader type by inspecting magic headers and PhpSpreadsheet identification.
     */
    public function detectReaderType(string $filePath, ?string $clientExt = null): string
    {
        // 1. Quick content probe: check for HTML or XML headers
        if (file_exists($filePath) && is_readable($filePath)) {
            $handle = @fopen($filePath, 'r');
            if ($handle) {
                $sample = fread($handle, 2048);
                fclose($handle);

                if ($sample !== false) {
                    $lowerSample = strtolower($sample);
                    if (str_contains($lowerSample, '<html') || str_contains($lowerSample, '<table') || str_contains($lowerSample, '<!doctype html')) {
                        return Excel::HTML;
                    }
                    if (str_contains($lowerSample, '<?xml') && str_contains($lowerSample, 'spreadsheet')) {
                        return Excel::XML;
                    }
                }
            }
        }

        // 2. Use PhpSpreadsheet IOFactory to identify
        try {
            $identified = IOFactory::identify($filePath);
            $upper = strtoupper($identified);
            if (defined(Excel::class . "::$upper")) {
                return constant(Excel::class . "::$upper");
            }
        } catch (\Throwable $e) {
            // Fall through to extension check
        }

        // 3. Fallback based on extension
        return match ($clientExt) {
            'xlsx' => Excel::XLSX,
            'xls' => Excel::XLS,
            'csv', 'tsv', 'txt' => Excel::CSV,
            'html', 'htm' => Excel::HTML,
            'xml' => Excel::XML,
            default => Excel::CSV,
        };
    }
}
