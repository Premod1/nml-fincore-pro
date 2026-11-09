@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Trial Balance</h4>
            <small class="text-muted">Summary of All General Ledger Balances as of {{ $endDate }} ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2 fc-no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨️ Print / Export PDF</button>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.trial-balance') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Start Date (Optional)</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">As of Date (End)</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">SBU / Branch Code (Optional)</label>
                <input type="text" name="sbu_code" class="form-control form-control-sm" value="{{ $sbuCode }}" placeholder="Consolidated (All Branches)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Run Report</button>
            </div>
        </form>
    </div>

    {{-- QBO Spreadsheet Grid --}}
    <div class="fc-card">
        <div class="text-center mb-3">
            <h5 class="fw-bold mb-1">{{ config('fincore-pro.theme.brand_name', 'FinCore Accounting') }}</h5>
            <h6 class="text-muted mb-0">TRIAL BALANCE</h6>
            <small class="text-muted">As of {{ date('F d, Y', strtotime($endDate)) }}</small>
        </div>

        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Account #</th>
                        <th style="width: 44%;">Account Description</th>
                        <th style="width: 14%;">Classification</th>
                        <th style="width: 15%;" class="text-end">Debit ({{ $currency }})</th>
                        <th style="width: 15%;" class="text-end">Credit ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['accounts'] as $item)
                        @php
                            $acc = $item['account'];
                            $typeVal = $acc->type instanceof \BackedEnum ? $acc->type->value : (string) $acc->type;
                        @endphp
                        <tr>
                            <td class="text-muted fw-semibold">{{ $acc->code }}</td>
                            <td>
                                {{-- QBO Drill-Down Link --}}
                                <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id, 'end_date' => $endDate]) }}"
                                   class="fc-drill-link">
                                    {{ $acc->name }}
                                </a>
                            </td>
                            <td><span class="fc-badge fc-badge-info">{{ strtoupper($typeVal) }}</span></td>
                            <td class="text-end fw-semibold">
                                @if($item['debit'] > 0)
                                    <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id, 'end_date' => $endDate]) }}" class="fc-drill-link text-dark">
                                        {{ number_format($item['debit'], 2) }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">
                                @if($item['credit'] > 0)
                                    <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id, 'end_date' => $endDate]) }}" class="fc-drill-link text-dark">
                                        {{ number_format($item['credit'], 2) }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No posted ledger transactions found as of this date.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background: #f8fafc; border-top: 2px solid var(--fc-border); border-bottom: 2px solid var(--fc-border);">
                        <td colspan="3" class="text-end">TOTALS</td>
                        <td class="text-end {{ $report['is_balanced'] ? 'text-success' : 'text-danger' }}">
                            {{ $currency }} {{ number_format($report['total_debits'], 2) }}
                        </td>
                        <td class="text-end {{ $report['is_balanced'] ? 'text-success' : 'text-danger' }}">
                            {{ $currency }} {{ number_format($report['total_credits'], 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 text-muted small">
            <div>
                Status:
                @if($report['is_balanced'])
                    <span class="text-success fw-bold">✓ General Ledger is perfectly balanced</span>
                @else
                    <span class="text-danger fw-bold">⚠ Out of balance</span>
                @endif
            </div>
            <div>Generated by NML FinCore Engine</div>
        </div>
    </div>
@endsection
