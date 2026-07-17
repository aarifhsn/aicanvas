<?php

namespace App\Services\AI;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentProcessor
{
    protected int $chunkSize = 800;
    protected int $chunkOverlap = 100;

    public function extractText(UploadedFile $file): string
    {
        return match ($file->getClientOriginalExtension()) {
            'pdf' => $this->extractFromPdf($file),
            'txt', 'md' => file_get_contents($file->getRealPath()),
            default => throw new \InvalidArgumentException('Unsupported file type. Upload a PDF or .txt file.'),
        };
    }

    protected function extractFromPdf(UploadedFile $file): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($file->getRealPath());

        return $pdf->getText();
    }

    /**
     * @return array<string>
     */
    public function chunk(string $text): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if ($text === '') {
            return [];
        }

        $chunks = [];
        $length = strlen($text);
        $start = 0;

        while ($start < $length) {
            $end = min($start + $this->chunkSize, $length);
            $chunks[] = trim(substr($text, $start, $end - $start));
            $start += ($this->chunkSize - $this->chunkOverlap);
        }

        return array_values(array_filter($chunks, fn($c) => $c !== ''));
    }
}