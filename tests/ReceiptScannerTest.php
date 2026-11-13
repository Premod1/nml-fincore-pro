<?php

namespace Nml\FinCorePro\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Nml\FinCore\Enums\JvStatus;
use Nml\FinCore\Models\JournalEntry;
use Nml\FinCorePro\Services\ReceiptOcrService;
use RuntimeException;

class ReceiptScannerTest extends TestCase
{
    public function test_scanner_page_renders_successfully(): void
    {
        $response = $this->get(route('fincore.receipts.scanner'));

        $response->assertOk();
        $response->assertSee('AI Receipt');
        $response->assertSee('Pending Draft Purchase Vouchers');
    }

    public function test_ocr_service_extracts_invoice_from_image_and_pdf(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'status' => 'completed',
                'steps'  => [
                    [
                        'type'    => 'model_output',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => json_encode([
                                    'supplier_name'  => 'Ceylon Office Supplies Ltd',
                                    'invoice_number' => 'INV-2026-8841',
                                    'invoice_date'   => '2026-11-10',
                                    'currency'       => 'LKR',
                                    'net_amount'     => 10000.00,
                                    'tax_amount'     => 1800.00,
                                    'total_amount'   => 11800.00,
                                    'notes'          => 'A4 paper & toner cartridges',
                                    'line_items'     => [
                                        [
                                            'description' => 'A4 Paper Ream',
                                            'quantity'    => 10,
                                            'unit_price'  => 1000.00,
                                            'line_total'  => 10000.00,
                                        ],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = $this->app->make(ReceiptOcrService::class);

        $pdfFile = UploadedFile::fake()->create('invoice.pdf', 120, 'application/pdf');
        $result = $service->scan($pdfFile);

        $this->assertSame('Ceylon Office Supplies Ltd', $result['supplier_name']);
        $this->assertSame('INV-2026-8841', $result['invoice_number']);
        $this->assertSame('2026-11-10', $result['invoice_date']);
        $this->assertSame(10000.00, $result['net_amount']);
        $this->assertSame(1800.00, $result['tax_amount']);
        $this->assertSame(11800.00, $result['total_amount']);
        $this->assertCount(1, $result['line_items']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $request->hasHeader('x-goog-api-key', 'test-gemini-key')
                && ($body['input'][0]['type'] ?? null) === 'document'
                && ($body['input'][0]['mime_type'] ?? null) === 'application/pdf';
        });
    }

    public function test_ocr_service_throws_when_api_key_is_missing(): void
    {
        config()->set('fincore-pro.gemini.api_key', '');
        $service = $this->app->make(ReceiptOcrService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Gemini API key is not configured');

        $service->scanFromBase64(base64_encode('img'), 'image/png');
    }

    public function test_scan_endpoint_returns_extracted_json_for_uploaded_receipt(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'steps' => [
                    [
                        'type'    => 'model_output',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => json_encode([
                                    'supplier_name'  => 'Metro Hardware',
                                    'invoice_number' => 'MH-501',
                                    'invoice_date'   => '2026-11-12',
                                    'currency'       => 'USD',
                                    'net_amount'     => 250.00,
                                    'tax_amount'     => 25.00,
                                    'total_amount'   => 275.00,
                                    'notes'          => 'Workshop tools',
                                    'line_items'     => [],
                                ]),
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->postJson(route('fincore.receipts.scan'), [
            'receipt' => $file,
        ]);

        $response->assertOk()
            ->assertJson([
                'ok'   => true,
                'data' => [
                    'supplier_name'  => 'Metro Hardware',
                    'invoice_number' => 'MH-501',
                    'net_amount'     => 250.0,
                    'tax_amount'     => 25.0,
                    'total_amount'   => 275.0,
                ],
            ]);
    }

    public function test_store_draft_creates_draft_credit_purchase_voucher_and_one_click_approve_posts_it(): void
    {
        $response = $this->post(route('fincore.receipts.draft'), [
            'supplier_name'  => 'Apex Distributors',
            'invoice_number' => 'APX-2026-99',
            'invoice_date'   => '2026-11-11',
            'net_amount'     => '5000.00',
            'tax_amount'     => '900.00',
            'total_amount'   => '5900.00',
            'currency'       => 'LKR',
            'notes'          => 'Stock replenishment',
            'action'         => 'draft',
        ]);

        $response->assertRedirect(route('fincore.receipts.scanner'));

        $entry = JournalEntry::with('lines')->where('reference', 'APX-2026-99')->first();
        $this->assertNotNull($entry);

        $status = $entry->status instanceof \BackedEnum ? $entry->status->value : $entry->status;
        $this->assertSame(JvStatus::DRAFT->value, $status);
        $this->assertEquals(5900.00, $entry->getDebitSum());
        $this->assertEquals(5900.00, $entry->getCreditSum());
        $this->assertCount(3, $entry->lines);

        // One-click review & approval by accountant
        $approveResponse = $this->post(route('fincore.receipts.approve', $entry->id));
        $approveResponse->assertRedirect(route('fincore.receipts.scanner'));

        $entry->refresh();
        $postedStatus = $entry->status instanceof \BackedEnum ? $entry->status->value : $entry->status;
        $this->assertSame(JvStatus::POSTED->value, $postedStatus);
    }

    public function test_store_draft_rejects_unbalanced_amounts(): void
    {
        $response = $this->post(route('fincore.receipts.draft'), [
            'supplier_name'  => 'Unbalanced Vendor',
            'invoice_number' => 'BAD-1',
            'invoice_date'   => '2026-11-11',
            'net_amount'     => '100.00',
            'tax_amount'     => '10.00',
            'total_amount'   => '150.00',
            'action'         => 'draft',
        ]);

        $response->assertSessionHas('fincore_error');
        $this->assertNull(JournalEntry::where('reference', 'BAD-1')->first());
    }
}
