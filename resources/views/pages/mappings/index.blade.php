@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">⚡ ERP Auto-Posting Rules &amp; Account Mapping</h4>
            <small class="text-muted">
                Map your ERP business events (Sales, Purchases, Payments) to Ledger Accounts — zero accounting knowledge needed in ERP controllers!
            </small>
        </div>
        <form action="{{ route('fincore.mappings.sync') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                ↻ Reset / Sync Default ERP Rules
            </button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            @foreach($rules as $rule)
                <div class="fc-card">
                    <form action="{{ route('fincore.mappings.update', $rule->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fc-badge fc-badge-info me-2">Event Key: <code>{{ $rule->event_key }}</code></span>
                                <h6 class="fw-bold d-inline mb-0">{{ $rule->name }}</h6>
                                <p class="text-muted small mb-2 mt-1">{{ $rule->description }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="auto_post" value="1"
                                       id="autopost_{{ $rule->id }}" {{ $rule->auto_post ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="autopost_{{ $rule->id }}">
                                    Auto-Post to Ledger
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="fc-table">
                                <thead>
                                    <tr>
                                        <th>Double-Entry Side</th>
                                        <th>Role / Line Description</th>
                                        <th>Payload Amount Key</th>
                                        <th>Mapped General Ledger Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rule->lines as $line)
                                        <tr>
                                            <td>
                                                @if($line->type === 'debit')
                                                    <span class="fc-badge fc-badge-info">DEBIT (Dr)</span>
                                                @else
                                                    <span class="fc-badge fc-badge-warning">CREDIT (Cr)</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $line->label }}</span>
                                                @if($line->is_optional)
                                                    <span class="text-muted small">(Optional)</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $line->amount_key }}</code></td>
                                            <td>
                                                <select name="lines[{{ $line->id }}]" class="form-select form-select-sm">
                                                    <option value="">-- Select Account --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}"
                                                            {{ ($line->account_id == $acc->id || (!$line->account_id && $line->default_code === $acc->code)) ? 'selected' : '' }}>
                                                            {{ $acc->code }} - {{ $acc->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                ERP Controller Call: <code>FinCorePro::recordErpEvent('{{ $rule->event_key }}', [...])</code>
                            </small>
                            <button type="submit" class="btn fc-btn-primary btn-sm">
                                Save Mapping
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="col-lg-4">
            <div class="fc-card sticky-top" style="top: 1rem;">
                <h6 class="fw-bold mb-2">🧪 Test / Simulate ERP Transaction</h6>
                <p class="text-muted small mb-3">
                    Simulate an Invoice or Payment from your ERP to verify how it automatically splits into Debit &amp; Credit ledger entries.
                </p>

                <form action="{{ route('fincore.mappings.test') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">ERP Business Event</label>
                        <select name="event_key" class="form-select form-select-sm" required>
                            @foreach($rules as $rule)
                                <option value="{{ $rule->event_key }}">{{ $rule->name }} ({{ $rule->event_key }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Net Amount (Sub-Total)</label>
                        <input type="number" step="0.01" name="net_amount" class="form-control form-control-sm" value="10000.00" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax / VAT Amount (Optional)</label>
                        <input type="number" step="0.01" name="tax_amount" class="form-control form-control-sm" value="1800.00">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cost of Goods Sold / COGS (Optional)</label>
                        <input type="number" step="0.01" name="cogs_amount" class="form-control form-control-sm" value="6500.00">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">ERP Reference #</label>
                        <input type="text" name="reference" class="form-control form-control-sm" placeholder="e.g. INV-2026-0042">
                    </div>

                    <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold">
                        ⚡ Fire ERP Event &amp; Generate Ledger Entry
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
