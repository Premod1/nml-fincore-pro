<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Enums\JvStatus;
use Nml\FinCore\Models\Account;
use Nml\FinCore\Models\JournalEntry;
use Nml\FinCore\Services\LedgerEngine;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with('lines.account')->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->query('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->query('to_date'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', $search)
                  ->orWhere('reference', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        return view('fincore-pro::pages.journals.index', [
            'entries'  => $entries,
            'currency' => config('accounting.currency', 'LKR'),
            'statuses' => JvStatus::cases(),
        ]);
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('fincore-pro::pages.journals.create', [
            'accounts' => $accounts,
            'currency' => config('accounting.currency', 'LKR'),
        ]);
    }

    public function store(Request $request, LedgerEngine $ledger)
    {
        $validated = $request->validate([
            'date'                 => 'required|date',
            'reference'            => 'nullable|string|max:100',
            'description'          => 'required|string|max:255',
            'auto_post'            => 'nullable|boolean',
            'lines'                => 'required|array|min:2',
            'lines.*.account_id'   => 'required|integer',
            'lines.*.type'         => 'required|in:debit,credit',
            'lines.*.amount'       => 'required|numeric|gt:0',
            'lines.*.description'  => 'nullable|string|max:255',
        ]);

        try {
            $entry = $ledger->createJournalEntry([
                'date'        => $validated['date'],
                'reference'   => $validated['reference'] ?? null,
                'description' => $validated['description'],
                'lines'       => $validated['lines'],
                'created_by'  => auth()->check() ? auth()->id() : null,
            ]);

            if ($request->boolean('auto_post')) {
                $entry->post();
            }

            return redirect()->route('fincore.journals.show', $entry->id)
                ->with('fincore_success', "Journal Entry {$entry->entry_number} saved successfully.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('fincore_error', $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $entry = JournalEntry::with('lines.account')->findOrFail($id);

        return view('fincore-pro::pages.journals.show', [
            'entry'    => $entry,
            'currency' => config('accounting.currency', 'LKR'),
        ]);
    }

    public function post(int $id)
    {
        try {
            $entry = JournalEntry::findOrFail($id);
            $entry->post();

            return back()->with('fincore_success', "Journal Entry {$entry->entry_number} posted to General Ledger.");
        } catch (\Throwable $e) {
            return back()->with('fincore_error', $e->getMessage());
        }
    }

    public function void(int $id)
    {
        try {
            $entry = JournalEntry::findOrFail($id);
            $entry->void();

            return back()->with('fincore_success', "Journal Entry {$entry->entry_number} has been voided.");
        } catch (\Throwable $e) {
            return back()->with('fincore_error', $e->getMessage());
        }
    }
}
