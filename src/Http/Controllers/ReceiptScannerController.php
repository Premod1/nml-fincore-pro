<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Enums\JvStatus;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\JournalEntry;
use Nml\FinCorePro\Services\ErpPostingEngine;
use Nml\FinCorePro\Services\ReceiptOcrService;

class ReceiptScannerController extends Controller
{
    public function index()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        $draftVouchers = JournalEntry::with('lines.account')
            ->where('status', JvStatus::DRAFT->value)
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('fincore-pro::pages.receipts.scanner', [
            'accounts'         => $accounts,
            'draftVouchers'    => $draftVouchers,
            'currency'         => config('accounting.currency', 'LKR'),
            'geminiConfigured' => !empty(config('fincore-pro.gemini.api_key')),
            'geminiModel'      => config('fincore-pro.gemini.model', 'gemini-3.8-flash'),
            'maxFileKb'        => (int) config('fincore-pro.gemini.max_file_size_kb', 10240),
        ]);
    }

    public function scan(Request $request, ReceiptOcrService $ocrService): JsonResponse
    {
        $maxKb = (int) config('fincore-pro.gemini.max_file_size_kb', 10240);

        $request->validate([
            'receipt' => "required|file|mimes:pdf,jpg,jpeg,png,webp,gif|max:{$maxKb}",
        ]);

        try {
            $extracted = $ocrService->scan($request->file('receipt'));

            return response()->json([
                'ok'   => true,
                'data' => $extracted,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function storeDraft(Request $request, ErpPostingEngine $engine)
    {
        $validated = $request->validate([
            'supplier_name'        => 'required|string|max:150',
            'invoice_number'       => 'nullable|string|max:100',
            'invoice_date'         => 'required|date',
            'net_amount'           => 'required|numeric|gt:0',
            'tax_amount'           => 'nullable|numeric|min:0',
            'total_amount'         => 'required|numeric|gt:0',
            'currency'             => 'nullable|string|size:3',
            'notes'                => 'nullable|string|max:255',
            'inventory_account_id' => 'nullable|integer',
            'action'               => 'nullable|in:draft,approve',
        ]);

        $net = round((float) $validated['net_amount'], 2);
        $tax = round((float) ($validated['tax_amount'] ?? 0), 2);
        $total = round((float) $validated['total_amount'], 2);

        if (abs(($net + $tax) - $total) > 0.01) {
            return back()->withInput()->with('fincore_error', 'Net Amount + Tax Amount must equal Total Amount.');
        }

        $supplier = trim($validated['supplier_name']);
        $invoiceNo = trim((string) ($validated['invoice_number'] ?? ''));
        $notes = trim((string) ($validated['notes'] ?? ''));

        $description = "Supplier Bill - {$supplier}";
        if ($invoiceNo !== '') {
            $description .= " ({$invoiceNo})";
        }
        if ($notes !== '') {
            $description .= " | {$notes}";
        }

        $autoPost = ($validated['action'] ?? 'draft') === 'approve';

        $payload = [
            'date'         => $validated['invoice_date'],
            'reference'    => $invoiceNo !== '' ? $invoiceNo : ('OCR-' . now()->format('YmdHis')),
            'description'  => mb_substr($description, 0, 255),
            'net_amount'   => $net,
            'tax_amount'   => $tax,
            'total_amount' => $total,
            'currency'     => strtoupper($validated['currency'] ?? config('accounting.currency', 'LKR')),
            'auto_post'    => $autoPost,
        ];

        if (!empty($validated['inventory_account_id'])) {
            $payload['inventory_account_id'] = (int) $validated['inventory_account_id'];
        }

        try {
            $entry = $engine->recordErpEvent('credit_purchase', $payload);

            $verb = $autoPost ? 'approved and posted to the General Ledger' : 'created as a Draft Voucher for review';

            return redirect()->route('fincore.receipts.scanner')
                ->with('fincore_success', "Voucher {$entry->entry_number} ({$supplier}) {$verb}.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('fincore_error', $e->getMessage());
        }
    }

    public function approve($id)
    {
        try {
            $entry = is_numeric($id)
                ? JournalEntry::findOrFail($id)
                : JournalEntry::where('entry_number', $id)->firstOrFail();

            $entry->post();

            return redirect()->route('fincore.receipts.scanner')
                ->with('fincore_success', "Draft Voucher {$entry->entry_number} approved and posted to General Ledger.");
        } catch (\Throwable $e) {
            return back()->with('fincore_error', $e->getMessage());
        }
    }
}
