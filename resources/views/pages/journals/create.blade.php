@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Create Manual Journal Entry</h4>
            <small class="text-muted">Debits and Credits are validated in real-time before posting</small>
        </div>
        <a href="{{ route('fincore.journals.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Journals</a>
    </div>

    <div class="fc-card">
        <form action="{{ route('fincore.journals.store') }}" method="POST" id="fcJournalForm">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Reference #</label>
                    <input type="text" name="reference" class="form-control form-control-sm" value="{{ old('reference') }}" placeholder="e.g. ADJ-001">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Narration / Description</label>
                    <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description') }}" placeholder="Describe the journal entry..." required>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="fc-table" id="fcLinesTable">
                    <thead>
                        <tr>
                            <th style="width: 38%">Account</th>
                            <th style="width: 15%">Side (Dr/Cr)</th>
                            <th style="width: 20%">Amount ({{ $currency }})</th>
                            <th style="width: 22%">Line Memo</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody id="fcLinesBody">
                        <tr>
                            <td>
                                <select name="lines[0][account_id]" class="form-select form-select-sm" required>
                                    <option value="">-- Choose Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="lines[0][type]" class="form-select form-select-sm fc-line-type" onchange="recalcTotals()">
                                    <option value="debit" selected>Debit (Dr)</option>
                                    <option value="credit">Credit (Cr)</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="lines[0][amount]" class="form-control form-control-sm fc-line-amount" oninput="recalcTotals()" required>
                            </td>
                            <td>
                                <input type="text" name="lines[0][description]" class="form-control form-control-sm">
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <select name="lines[1][account_id]" class="form-select form-select-sm" required>
                                    <option value="">-- Choose Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="lines[1][type]" class="form-select form-select-sm fc-line-type" onchange="recalcTotals()">
                                    <option value="debit">Debit (Dr)</option>
                                    <option value="credit" selected>Credit (Cr)</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="lines[1][amount]" class="form-control form-control-sm fc-line-amount" oninput="recalcTotals()" required>
                            </td>
                            <td>
                                <input type="text" name="lines[1][description]" class="form-control form-control-sm">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center border-top pt-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addJournalLine()">
                    + Add Line
                </button>

                <div class="d-flex align-items-center gap-4">
                    <div>Total Debit: <strong id="fcTotalDebit">0.00</strong></div>
                    <div>Total Credit: <strong id="fcTotalCredit">0.00</strong></div>
                    <div id="fcBalanceBadge" class="fc-badge fc-badge-info">Balanced</div>
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 mt-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="auto_post" value="1" id="autoPostCheck" checked>
                    <label class="form-check-label small fw-semibold" for="autoPostCheck">
                        Immediately Post to General Ledger
                    </label>
                </div>
                <button type="submit" class="btn fc-btn-primary btn-sm px-4" id="fcSubmitBtn">
                    Save Journal Entry
                </button>
            </div>
        </form>
    </div>

    <script>
        let lineIndex = 2;
        const accountOptionsHtml = `@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ addslashes($acc->name) }}</option>@endforeach`;

        function addJournalLine() {
            const tbody = document.getElementById('fcLinesBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm" required>
                        <option value="">-- Choose Account --</option>
                        ${accountOptionsHtml}
                    </select>
                </td>
                <td>
                    <select name="lines[${lineIndex}][type]" class="form-select form-select-sm fc-line-type" onchange="recalcTotals()">
                        <option value="debit">Debit (Dr)</option>
                        <option value="credit">Credit (Cr)</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="lines[${lineIndex}][amount]" class="form-control form-control-sm fc-line-amount" oninput="recalcTotals()" required>
                </td>
                <td>
                    <input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotals();">&times;</button>
                </td>
            `;
            tbody.appendChild(tr);
            lineIndex++;
        }

        function recalcTotals() {
            let dr = 0, cr = 0;
            document.querySelectorAll('#fcLinesBody tr').forEach(row => {
                const type = row.querySelector('.fc-line-type')?.value;
                const amt = parseFloat(row.querySelector('.fc-line-amount')?.value || 0);
                if (type === 'debit') dr += amt;
                if (type === 'credit') cr += amt;
            });

            document.getElementById('fcTotalDebit').textContent = dr.toFixed(2);
            document.getElementById('fcTotalCredit').textContent = cr.toFixed(2);

            const diff = Math.abs(dr - cr);
            const badge = document.getElementById('fcBalanceBadge');
            const btn = document.getElementById('fcSubmitBtn');

            if (dr > 0 && diff < 0.005) {
                badge.className = 'fc-badge fc-badge-success';
                badge.textContent = '✓ Balanced';
                btn.disabled = false;
            } else {
                badge.className = 'fc-badge fc-badge-danger';
                badge.textContent = 'Diff: ' + diff.toFixed(2);
                btn.disabled = true;
            }
        }
    </script>
@endsection
