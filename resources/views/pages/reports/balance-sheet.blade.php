@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Balance Sheet (Statement of Financial Position)</h4>
            <small class="text-muted">As of {{ $asOfDate }} ({{ $currency }})</small>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fc-no-print">🖨️ Print / Save PDF</button>
    </div>

    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.balance-sheet') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">As of Date</label>
                <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">SBU / Branch Code</label>
                <input type="text" name="sbu_code" class="form-control form-control-sm" value="{{ $sbuCode }}" placeholder="All Branches">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Update Balance Sheet</button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="fc-card h-100">
                <h6 class="fw-bold text-uppercase text-primary mb-3">Assets</h6>
                <table class="fc-table">
                    <tbody>
                        @forelse($report['assets']['accounts'] as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['account']->code }}</td>
                                <td>{{ $item['account']->name }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">No asset balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="2">Total Assets</td>
                            <td class="text-end">{{ $currency }} {{ number_format($report['assets']['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="fc-card h-100">
                <h6 class="fw-bold text-uppercase text-danger mb-3">Liabilities</h6>
                <table class="fc-table mb-4">
                    <tbody>
                        @forelse($report['liabilities']['accounts'] as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['account']->code }}</td>
                                <td>{{ $item['account']->name }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">No liability balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="2">Total Liabilities</td>
                            <td class="text-end">{{ $currency }} {{ number_format($report['liabilities']['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <h6 class="fw-bold text-uppercase text-success mb-3">Equity</h6>
                <table class="fc-table">
                    <tbody>
                        @forelse($report['equity']['accounts'] as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['account']->code }}</td>
                                <td>{{ $item['account']->name }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">No equity balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="2">Total Liabilities &amp; Equity</td>
                            <td class="text-end">
                                {{ $currency }} {{ number_format($report['liabilities']['total'] + $report['equity']['total'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
