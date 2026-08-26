<?php

namespace App\Services;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamingTabularExport
{
    public function download(string $format, string $basename, array $headers, iterable $rows): Response
    {
        abort_unless(in_array($format, ['csv', 'excel'], true), 404);

        return $format === 'csv'
            ? $this->csv($basename, $headers, $rows)
            : $this->xlsx($basename, $headers, $rows);
    }

    private function csv(string $basename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($output, array_map([$this, 'safeCsvValue'], (array) $row), ';');
            }
            fclose($output);
        }, $basename.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function xlsx(string $basename, array $headers, iterable $rows): Response
    {
        $safeValue = fn (mixed $value): mixed => $this->safeSpreadsheetValue($value);
        $export = new class($headers, $rows, $safeValue) implements FromGenerator, WithHeadings
        {
            public function __construct(
                private readonly array $headers,
                private readonly iterable $rows,
                private readonly \Closure $safeValue,
            ) {}

            public function headings(): array
            {
                return $this->headers;
            }

            public function generator(): Generator
            {
                foreach ($this->rows as $row) {
                    yield array_map($this->safeValue, (array) $row);
                }
            }
        };

        return Excel::download($export, $basename.'.xlsx', ExcelFormat::XLSX, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }

    private function safeSpreadsheetValue(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
