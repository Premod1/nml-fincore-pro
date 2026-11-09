@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Profit and Loss</h4>
            <small class="text-muted">Reporting Period: {{ $startDate }} to {{ $endDate }} ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2 fc-no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨️ Print / Export PDF</button>
        </div>
    </div>

    {{-- Date Filter Bar --}}
    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.income-statement') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Report Period From</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Report Period To</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Run Report</button>
            </div>
        </form>
    </div>

    {{-- Statement Sheet --}}
    <div class="fc-card">
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1">{{ config('fincore-pro.theme.brand_name', 'FinCore Accounting') }}</h5>
            <h6 class="text-muted mb-0">PROFIT AND LOSS STATEMENT</h6>
            <small class="text-muted">{{ date('F d, Y', strtotime($startDate)) }} — {{ date('F d, Y', strtotime($endDate)) }}</small>
        </div>

        {{-- Revenue Section --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                <span class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--fc-success);">
                    Operating Income
                </span>
                <span class="small text-muted fw-semibold">TOTAL ({{ $currency }})</span>
            </div>
            <table class="fc-table">
                <tbody>
                    @forelse($report['revenue']['accounts'] as $item)
                        <tr>
                            <td style="width: 15%;" class="text-muted">{{ $item['account']->code }}</td>
                            <td>
                                {{-- QBO Drill-Down Link to General Ledger --}}
                                <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                   class="fc-drill-link">
                                    {{ $item['account']->name }}
                                </a>
                            </td>
                            <td class="text-end fw-semibold" style="width: 25%;">
                                <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                   class="fc-drill-link text-dark">
                                    {{ number_format($item['balance'], 2) }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted py-2">No operating income recorded during this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background: #f8fafc; border-top: 1px solid var(--fc-border); border-bottom: 2px solid var(--fc-border);">
                        <td colspan="2">Total Operating Income</td>
                        <td class="text-end text-success">{{ $currency }} {{ number_format($report['revenue']['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Expenses Section --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                <span class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--fc-danger);">
                    Operating &amp; Overhead Expenses
                </span>
                <span class="small text-muted fw-semibold">TOTAL ({{ $currency }})</span>
            </div>
            <table class="fc-table">
                <tbody>
                    @forelse($report['expenses']['accounts'] as $item)
                        <tr>
                            <td style="width: 15%;" class="text-muted">{{ $item['account']->code }}</td>
                            <td>
                                {{-- QBO Drill-Down Link to General Ledger --}}
                                <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                   class="fc-drill-link">
                                    {{ $item['account']->name }}
                                </a>
                            </td>
                            <td class="text-end fw-semibold" style="width: 25%;">
                                <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                   class="fc-drill-link text-dark">
                                    {{ number_format($item['balance'], 2) }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted py-2">No operating expenses recorded during this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background: #f8fafc; border-top: 1px solid var(--fc-border); border-bottom: 2px solid var(--fc-border);">
                        <td colspan="2">Total Operating Expenses</td>
                        <td class="text-end text-danger">{{ $currency }} {{ number_format($report['expenses']['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Net Income Result --}}
        <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: #f4f7fa; border: 1px solid var(--fc-border);">
            <div>
                <h5 class="fw-bold mb-0">NET PROFIT / (LOSS)</h5>
                <small class="text-muted">Total Income minus Total Expenses</small>
            </div>
            <h4 class="fw-bold mb-0 {{ $report['net_income'] >= 0 ? 'text-primary' : 'text-danger' }}">
                {{ $currency }} {{ number_format($report['net_income'], 2) }}
            </h4>
        </div>
    </div>
@endsection
