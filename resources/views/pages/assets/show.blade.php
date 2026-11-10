@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('fincore.assets.index') }}" class="text-decoration-none text-muted small">← Fixed Assets</a>
                <span class="text-muted small">/</span>
                <h4 class="fw-bold mb-0">{{ $asset->name }} ({{ $asset->code }})</h4>
            </div>
            <small class="text-muted">
                Purchased: <strong>{{ $asset->purchase_date?->format('Y-m-d') }}</strong> | Status: <span class="fc-badge fc-badge-success">{{ strtoupper($asset->status) }}</span>
            </small>
        </div>
        <div>
            @if($bookValue > ($asset->salvage_value ?? 0))
                <form action="{{ route('fincore.assets.depreciate') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    <input type="hidden" name="date" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                    <button type="submit" class="btn fc-btn-primary btn-sm px-3">
                        ⚡ Run Next Month's Depreciation
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Asset Valuation Ribbon --}}
    <div class="row g-2 mb-4">
        <div class="col-md-3 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Original Cost</div>
                <div class="fw-bold font-monospace mt-1">{{ number_format($asset->purchase_cost, 2) }} {{ $currency }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Accumulated Depreciation</div>
                <div class="fw-bold font-monospace text-danger mt-1">-{{ number_format($accumulated, 2) }} {{ $currency }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="fc-card text-center p-2 bg-success-subtle border-success">
                <div class="text-muted small" style="font-size:0.75rem;">Net Book Value (NBV)</div>
                <div class="fw-bold font-monospace text-success mt-1" style="font-size: 1.1rem;">{{ number_format($bookValue, 2) }} {{ $currency }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Estimated Monthly Dep.</div>
                <div class="fw-bold font-monospace text-primary mt-1">{{ number_format($nextMonthDepreciation, 2) }} {{ $currency }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Asset Ledger Account Configurations --}}
        <div class="col-md-4">
            <div class="fc-card">
                <h6 class="fw-bold mb-3">Asset Ledger Mapping</h6>
                <table class="fc-table">
                    <tbody>
                        <tr>
                            <td class="text-muted small">Asset Account</td>
                            <td class="fw-bold small text-end">{{ $asset->assetAccount?->code }} - {{ $asset->assetAccount?->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Accumulated Dep.</td>
                            <td class="fw-bold small text-end">{{ $asset->accumulatedDepreciationAccount?->code }} - {{ $asset->accumulatedDepreciationAccount?->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Depreciation Expense</td>
                            <td class="fw-bold small text-end">{{ $asset->depreciationExpenseAccount?->code }} - {{ $asset->depreciationExpenseAccount?->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Method</td>
                            <td class="small text-end fw-semibold">{{ $asset->depreciation_method === 'straight_line' ? 'Straight Line (SLM)' : 'Reducing Balance (WDV)' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Useful Life</td>
                            <td class="small text-end font-monospace">{{ $asset->useful_life_years }} Years</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Annual Dep. Rate</td>
                            <td class="small text-end font-monospace">{{ $asset->depreciation_rate }}%</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Salvage / Scrap Value</td>
                            <td class="small text-end font-monospace">{{ number_format($asset->salvage_value, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Depreciation Posting Logs --}}
        <div class="col-md-8">
            <div class="fc-card p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Depreciation Journal History ({{ $asset->depreciationLogs->count() }})</h6>
                    <span class="text-muted small">Auto-generated double-entry vouchers</span>
                </div>
                <div class="table-responsive">
                    <table class="fc-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Journal Voucher</th>
                                <th>Description</th>
                                <th class="text-end">Amount ({{ $currency }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asset->depreciationLogs->sortByDesc('depreciation_date') as $log)
                                <tr>
                                    <td>{{ $log->depreciation_date?->format('Y-m-d') }}</td>
                                    <td>
                                        @if($log->journalEntry)
                                            <a href="{{ route('fincore.journals.show', $log->journal_entry_id) }}" class="font-monospace fw-bold text-decoration-none small">
                                                {{ $log->journalEntry->entry_number }}
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $log->journalEntry?->description ?: 'Monthly depreciation' }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-danger">
                                        {{ number_format($log->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 small">
                                        No depreciation logs recorded yet. Click "⚡ Run Next Month's Depreciation" above to calculate and post.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
