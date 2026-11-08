@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Profit &amp; Loss (Income Statement)</h4>
            <small class="text-muted">Period: {{ $startDate }} to {{ $endDate }} ({{ $currency }})</small>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fc-no-print">🖨️ Print / Save PDF</button>
    </div>

    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.income-statement') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Update Statement</button>
            </div>
        </form>
    </div>

    <div class="fc-card">
        <h6 class="fw-bold text-uppercase text-success mb-2">Operating Revenue</h6>
        <table class="fc-table mb-4">
            <tbody>
                @forelse($report['revenue']['accounts'] as $item)
                    <tr>
                        <td style="width: 15%" class="fw-bold">{{ $item['account']->code }}</td>
                        <td>{{ $item['account']->name }}</td>
                        <td class="text-end fw-semibold">{{ number_format($item['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No revenue recorded for period.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold bg-light">
                    <td colspan="2">Total Revenue</td>
                    <td class="text-end text-success">{{ $currency }} {{ number_format($report['revenue']['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <h6 class="fw-bold text-uppercase text-danger mb-2">Operating &amp; Cost Expenses</h6>
        <table class="fc-table mb-4">
            <tbody>
                @forelse($report['expenses']['accounts'] as $item)
                    <tr>
                        <td style="width: 15%" class="fw-bold">{{ $item['account']->code }}</td>
                        <td>{{ $item['account']->name }}</td>
                        <td class="text-end fw-semibold">{{ number_format($item['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No expenses recorded for period.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold bg-light">
                    <td colspan="2">Total Expenses</td>
                    <td class="text-end text-danger">{{ $currency }} {{ number_format($report['expenses']['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-between align-items-center p-3 rounded border bg-light">
            <h5 class="fw-bold mb-0">Net Profit / (Loss)</h5>
            <h4 class="fw-bold mb-0 {{ $report['net_income'] >= 0 ? 'text-primary' : 'text-danger' }}">
                {{ $currency }} {{ number_format($report['net_income'], 2) }}
            </h4>
        </div>
    </div>
@endsection
