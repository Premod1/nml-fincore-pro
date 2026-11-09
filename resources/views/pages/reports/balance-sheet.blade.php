@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Balance Sheet</h4>
            <small class="text-muted">Statement of Financial Position as of {{ $asOfDate }} ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2 fc-no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨️ Print / Export PDF</button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.balance-sheet') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">As of Date</label>
                <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">SBU / Branch Code (Optional)</label>
                <input type="text" name="sbu_code" class="form-control form-control-sm" value="{{ $sbuCode }}" placeholder="Consolidated (All Branches)">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Run Report</button>
            </div>
        </form>
    </div>

    <div class="fc-card">
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1">{{ config('fincore-pro.theme.brand_name', 'FinCore Accounting') }}</h5>
            <h6 class="text-muted mb-0">BALANCE SHEET (STATEMENT OF FINANCIAL POSITION)</h6>
            <small class="text-muted">As of {{ date('F d, Y', strtotime($asOfDate)) }}</small>
        </div>

        <div class="row g-4">
            {{-- Assets Side --}}
            <div class="col-lg-6">
                <div class="border-bottom pb-1 mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--fc-primary);">
                        Assets
                    </span>
                </div>
                <table class="fc-table mb-3">
                    <tbody>
                        @forelse($report['assets']['accounts'] as $item)
                            <tr>
                                <td style="width: 20%;" class="text-muted">{{ $item['account']->code }}</td>
                                <td>
                                    <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'end_date' => $asOfDate]) }}"
                                       class="fc-drill-link">
                                        {{ $item['account']->name }}
                                    </a>
                                </td>
                                <td class="text-end fw-semibold" style="width: 30%;">
                                    <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'end_date' => $asOfDate]) }}"
                                       class="fc-drill-link text-dark">
                                        {{ number_format($item['balance'], 2) }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted py-2">No asset balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold" style="background: #f8fafc; border-top: 1px solid var(--fc-border); border-bottom: 2px solid var(--fc-border);">
                            <td colspan="2">TOTAL ASSETS</td>
                            <td class="text-end text-primary">{{ $currency }} {{ number_format($report['assets']['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Liabilities & Equity Side --}}
            <div class="col-lg-6">
                {{-- Liabilities --}}
                <div class="border-bottom pb-1 mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--fc-danger);">
                        Liabilities
                    </span>
                </div>
                <table class="fc-table mb-4">
                    <tbody>
                        @forelse($report['liabilities']['accounts'] as $item)
                            <tr>
                                <td style="width: 20%;" class="text-muted">{{ $item['account']->code }}</td>
                                <td>
                                    <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'end_date' => $asOfDate]) }}"
                                       class="fc-drill-link">
                                        {{ $item['account']->name }}
                                    </a>
                                </td>
                                <td class="text-end fw-semibold" style="width: 30%;">
                                    <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'end_date' => $asOfDate]) }}"
                                       class="fc-drill-link text-dark">
                                        {{ number_format($item['balance'], 2) }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted py-2">No liability balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold" style="background: #f8fafc; border-top: 1px solid var(--fc-border);">
                            <td colspan="2">Total Liabilities</td>
                            <td class="text-end text-danger">{{ $currency }} {{ number_format($report['liabilities']['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Equity --}}
                <div class="border-bottom pb-1 mb-2">
                    <span class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--fc-success);">
                        Equity
                    </span>
                </div>
                <table class="fc-table mb-3">
                    <tbody>
                        @forelse($report['equity']['accounts'] as $item)
                            <tr>
                                <td style="width: 20%;" class="text-muted">{{ $item['account']->code ?? 'P&L' }}</td>
                                <td>
                                    @if(!empty($item['account']->id))
                                        <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'end_date' => $asOfDate]) }}"
                                           class="fc-drill-link">
                                            {{ $item['account']->name }}
                                        </a>
                                    @else
                                        {{-- Current Year Net Earnings rollup --}}
                                        <a href="{{ route('fincore.reports.income-statement', ['end_date' => $asOfDate]) }}"
                                           class="fc-drill-link">
                                            {{ $item['account']->name }} <small class="text-muted">(P&amp;L Rollup)</small>
                                        </a>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold" style="width: 30%;">
                                    {{ number_format($item['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted py-2">No equity balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold" style="background: #f8fafc; border-top: 1px solid var(--fc-border); border-bottom: 2px solid var(--fc-border);">
                            <td colspan="2">TOTAL LIABILITIES &amp; EQUITY</td>
                            <td class="text-end text-dark">
                                {{ $currency }} {{ number_format($report['liabilities']['total'] + $report['equity']['total'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
