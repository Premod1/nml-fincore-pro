<?php

namespace Nml\FinCorePro\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReceiptOcrService
{
    private const SUPPORTED_IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/heic',
        'image/heif',
    ];

    private const PDF_MIME = 'application/pdf';

    /**
     * Extract structured supplier invoice / receipt metadata and line items using Gemini Vision API.
     *
     * @return array{
     *     supplier_name: string,
     *     invoice_number: string,
     *     invoice_date: string,
     *     currency: string,
     *     net_amount: float,
     *     tax_amount: float,
     *     total_amount: float,
     *     notes: string,
     *     line_items: array<int, array{description: string, quantity: float, unit_price: float, line_total: float}>
     * }
     */
    public function scan(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream';
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Unable to read the uploaded receipt or invoice file.');
        }

        return $this->scanFromBase64(base64_encode($contents), $mimeType);
    }

    /**
     * Extract structured receipt/invoice fields from a base64-encoded image or PDF payload.
     */
    public function scanFromBase64(string $base64Data, string $mimeType): array
    {
        $apiKey = (string) config('fincore-pro.gemini.api_key', '');
        if ($apiKey === '') {
            throw new RuntimeException('Gemini API key is not configured. Set GEMINI_API_KEY in your environment.');
        }

        $inputPart = $this->buildMediaPart($base64Data, strtolower($mimeType));
        $endpoint = (string) config('fincore-pro.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/interactions');
        $model = (string) config('fincore-pro.gemini.model', 'gemini-3.8-flash');
        $timeout = (int) config('fincore-pro.gemini.timeout', 45);

        $response = Http::timeout($timeout)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Api-Revision'   => '2026-05-20',
                'Accept'         => 'application/json',
            ])
            ->post($endpoint, [
                'model' => $model,
                'input' => [
                    $inputPart,
                    [
                        'type' => 'text',
                        'text' => $this->extractionPrompt(),
                    ],
                ],
                'response_format' => [
                    'type'      => 'text',
                    'mime_type' => 'application/json',
                    'schema'    => $this->responseSchema(),
                ],
            ]);

        if ($response->failed()) {
            $errorMsg = $response->json('error.message')
                ?? $response->json('message')
                ?? ('HTTP ' . $response->status());

            throw new RuntimeException("Gemini Vision API request failed: {$errorMsg}");
        }

        $rawText = $this->extractTextFromResponse($response->json() ?? []);
        $decoded = $this->decodeJsonPayload($rawText);

        return $this->normalizeExtractedData($decoded);
    }

    /**
     * Build the Gemini Interactions API media part for an image or PDF document.
     */
    protected function buildMediaPart(string $base64Data, string $mimeType): array
    {
        if ($mimeType === self::PDF_MIME) {
            return [
                'type'      => 'document',
                'data'      => $base64Data,
                'mime_type' => self::PDF_MIME,
            ];
        }

        if (in_array($mimeType, self::SUPPORTED_IMAGE_MIMES, true)) {
            return [
                'type'      => 'image',
                'data'      => $base64Data,
                'mime_type' => $mimeType === 'image/jpg' ? 'image/jpeg' : $mimeType,
            ];
        }

        throw new RuntimeException("Unsupported file type [{$mimeType}]. Upload a PDF, JPG, PNG, or WEBP receipt/invoice.");
    }

    protected function extractionPrompt(): string
    {
        return implode(' ', [
            'You are an expert accounting OCR engine.',
            'Extract all supplier invoice or receipt details accurately.',
            'Return supplier_name, invoice_number, invoice_date (YYYY-MM-DD), currency (3-letter ISO code if visible, otherwise empty string),',
            'net_amount (subtotal before tax), tax_amount (total VAT/GST/sales tax, 0 if none), total_amount (grand total),',
            'a concise notes summary, and all line_items (description, quantity, unit_price, line_total).',
            'Ensure net_amount + tax_amount equals total_amount whenever possible.',
        ]);
    }

    protected function responseSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'supplier_name' => [
                    'type'        => 'string',
                    'description' => 'Legal or trading name of the supplier / vendor.',
                ],
                'invoice_number' => [
                    'type'        => 'string',
                    'description' => 'Invoice, bill, or receipt reference number.',
                ],
                'invoice_date' => [
                    'type'        => 'string',
                    'description' => 'Invoice or receipt date formatted as YYYY-MM-DD.',
                ],
                'currency' => [
                    'type'        => 'string',
                    'description' => '3-letter ISO currency code such as USD, LKR, EUR, GBP.',
                ],
                'net_amount' => [
                    'type'        => 'number',
                    'description' => 'Subtotal amount before tax.',
                ],
                'tax_amount' => [
                    'type'        => 'number',
                    'description' => 'Total tax / VAT / GST amount (0 if no tax).',
                ],
                'total_amount' => [
                    'type'        => 'number',
                    'description' => 'Grand total amount payable.',
                ],
                'notes' => [
                    'type'        => 'string',
                    'description' => 'Short accounting narration summarizing the purchase.',
                ],
                'line_items' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'description' => ['type' => 'string'],
                            'quantity'    => ['type' => 'number'],
                            'unit_price'  => ['type' => 'number'],
                            'line_total'  => ['type' => 'number'],
                        ],
                        'required' => ['description', 'quantity', 'unit_price', 'line_total'],
                    ],
                ],
            ],
            'required' => [
                'supplier_name',
                'invoice_number',
                'invoice_date',
                'net_amount',
                'tax_amount',
                'total_amount',
                'line_items',
            ],
        ];
    }

    /**
     * Extract text output from Gemini Interactions REST response (with fallback for outputs/candidates).
     */
    protected function extractTextFromResponse(array $payload): string
    {
        if (!empty($payload['steps']) && is_array($payload['steps'])) {
            foreach ($payload['steps'] as $step) {
                if (($step['type'] ?? null) !== 'model_output') {
                    continue;
                }
                foreach (($step['content'] ?? []) as $part) {
                    if (($part['type'] ?? null) === 'text' && isset($part['text'])) {
                        return (string) $part['text'];
                    }
                }
            }
        }

        if (!empty($payload['outputs']) && is_array($payload['outputs'])) {
            foreach ($payload['outputs'] as $output) {
                if (($output['type'] ?? null) === 'text' && isset($output['text'])) {
                    return (string) $output['text'];
                }
            }
        }

        if (!empty($payload['candidates'][0]['content']['parts'][0]['text'])) {
            return (string) $payload['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new RuntimeException('Gemini Vision API returned an empty or unrecognized response structure.');
    }

    protected function decodeJsonPayload(string $rawText): array
    {
        $trimmed = trim($rawText);
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $trimmed) ?? $trimmed;
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Failed to parse structured JSON from Gemini Vision API response.');
        }

        return $decoded;
    }

    protected function normalizeExtractedData(array $data): array
    {
        $tax = round(max(0.0, (float) ($data['tax_amount'] ?? 0)), 2);
        $net = round(max(0.0, (float) ($data['net_amount'] ?? 0)), 2);
        $total = round(max(0.0, (float) ($data['total_amount'] ?? 0)), 2);

        if ($net <= 0 && $total > 0) {
            $net = round(max(0.0, $total - $tax), 2);
        }
        if ($total <= 0 && $net > 0) {
            $total = round($net + $tax, 2);
        }

        $rawDate = trim((string) ($data['invoice_date'] ?? ''));
        $timestamp = $rawDate !== '' ? strtotime($rawDate) : false;
        $invoiceDate = $timestamp !== false ? date('Y-m-d', $timestamp) : now()->format('Y-m-d');

        $currency = strtoupper(trim((string) ($data['currency'] ?? '')));
        if (strlen($currency) !== 3) {
            $currency = (string) config('accounting.currency', 'LKR');
        }

        $lineItems = [];
        foreach (($data['line_items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $qty = round((float) ($item['quantity'] ?? 1), 4);
            $unitPrice = round((float) ($item['unit_price'] ?? 0), 2);
            $lineTotal = round((float) ($item['line_total'] ?? ($qty * $unitPrice)), 2);

            $lineItems[] = [
                'description' => trim((string) ($item['description'] ?? 'Item')),
                'quantity'    => $qty > 0 ? $qty : 1.0,
                'unit_price'  => $unitPrice,
                'line_total'  => $lineTotal,
            ];
        }

        return [
            'supplier_name'  => trim((string) ($data['supplier_name'] ?? 'Unknown Supplier')),
            'invoice_number' => trim((string) ($data['invoice_number'] ?? '')),
            'invoice_date'   => $invoiceDate,
            'currency'       => $currency,
            'net_amount'     => $net,
            'tax_amount'     => $tax,
            'total_amount'   => $total,
            'notes'          => trim((string) ($data['notes'] ?? '')),
            'line_items'     => $lineItems,
        ];
    }
}
