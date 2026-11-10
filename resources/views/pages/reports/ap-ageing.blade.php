@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Accounts Payable (A/P) Ageing Summary</h4>
            <small class="text-muted">Breakdown of outstanding supplier bills &amp; payables grouped by ageing buckets (Current, 1-30, 31-60, 61-90, 91+ days).</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                🖨️ Print Report
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="fc-card mb-3 p-3 bg-light">
        <form action="{{ route('fincore.reports.ap-ageing') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Ageing As Of Date</label>
                <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn fc-btn-primary btn-sm px-4">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    {{-- Ageing KPI Summary --}}
    <div class="row g-2 mb-4">
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Current / Not Due</div>
                <div class="fw-bold font-monospace text-success mt-1">{{ number_format($totals['current'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">1 - 30 Days</div>
                <div class="fw-bold font-monospace mt-1">{{ number_format($totals['1_30'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">31 - 60 Days</div>
                <div class="fw-bold font-monospace text-warning mt-1">{{ number_format($totals['31_60'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">61 - 90 Days</div>
                <div class="fw-bold font-monospace text-warning mt-1">{{ number_format($totals['61_90'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">91+ Days Overdue</div>
                <div class="fw-bold font-monospace text-danger mt-1">{{ number_format($totals['91_plus'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2 bg-primary-subtle border-primary">
                <div class="text-primary small fw-semibold" style="font-size:0.75rem;">TOTAL PAYABLES</div>
                <div class="fw-bold font-monospace text-primary mt-1" style="font-size:1.1rem;">{{ number_format($totals['total_outstanding'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="fc-card p-0">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Supplier / Creditor</th>
                        <th class="text-end">Current</th>
                        <th class="text-end">1 - 30 Days</th>
                        <th class="text-end">31 - 60 Days</th>
                        <th class="text-end">61 - 90 Days</th>
                        <th class="text-end">91+ Days Overdue</th>
                        <th class="text-end">Total Outstanding ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $row)
                        <tr>
                            <td class="fw-semibold">
                                {{ $row['partner_name'] }}
                                @if($row['partner_id'])
                                    <small class="text-muted d-block">ID: #{{ $row['partner_id'] }}</small>
                                @endif
                            </td>
                            <td class="text-end font-monospace">{{ $row['current'] > 0 ? number_format($row['current'], 2) : '-' }}</td>
                            <td class="text-end font-monospace">{{ $row['1_30'] > 0 ? number_format($row['1_30'], 2) : '-' }}</td>
                            <td class="text-end font-monospace text-warning">{{ $row['31_60'] > 0 ? number_format($row['31_60'], 2) : '-' }}</td>
                            <td class="text-end font-monospace text-warning">{{ $row['61_90'] > 0 ? number_format($row['61_90'], 2) : '-' }}</td>
                            <td class="text-end font-monospace text-danger fw-semibold">{{ $row['91_plus'] > 0 ? number_format($row['91_plus'], 2) : '-' }}</td>
                            <td class="text-end font-monospace fw-bold">{{ number_format($row['total_outstanding'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size: 2rem;">🎉</div>
                                <h6>No outstanding supplier bills</h6>
                                <p class="small text-muted mb-0">All supplier bills are fully settled as of {{ $asOfDate }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($report))
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td>Total ({{ count($report) }} Suppliers)</td>
                            <td class="text-end font-monospace">{{ number_format($totals['current'], 2) }}</td>
                            <td class="text-end font-monospace">{{ number_format($totals['1_30'], 2) }}</td>
                            <td class="text-end font-monospace text-warning">{{ number_format($totals['31_60'], 2) }}</td>
                            <td class="text-end font-monospace text-warning">{{ number_format($totals['61_90'], 2) }}</td>
                            <td class="text-end font-monospace text-danger">{{ number_format($totals['91_plus'], 2) }}</td>
                            <td class="text-end font-monospace text-primary" style="font-size:1.05rem;">{{ number_format($totals['total_outstanding'], 2) }} {{ $currency }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
