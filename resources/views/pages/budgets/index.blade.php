@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Budgets &amp; Variance Analysis</h4>
            <small class="text-muted">Set monthly financial targets by account and analyze Budget vs Actual variance from the general ledger.</small>
        </div>
        <div>
            <button type="button" class="btn fc-btn-primary btn-sm px-3" onclick="document.getElementById('setBudgetCard').classList.toggle('d-none')">
                + Set Monthly Budget
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="fc-card mb-3 p-3 bg-light">
        <form action="{{ route('fincore.budgets.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Fiscal Year</label>
                <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2020" max="2099">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Month</label>
                <select name="month" class="form-select form-select-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }} (Month {{ $m }})
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn fc-btn-primary btn-sm px-4">
                    View Variance Report
                </button>
            </div>
        </form>
    </div>

    {{-- Set Monthly Budget Card --}}
    <div id="setBudgetCard" class="fc-card mb-4 {{ $errors->any() ? '' : 'd-none' }}" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
        <h6 class="fw-bold mb-3">+ Set / Update Monthly Account Budget</h6>
        <form action="{{ route('fincore.budgets.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">General Ledger Account</label>
                    <select name="account_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Revenue / Expense Account --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type->value) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Fiscal Year</label>
                    <input type="number" name="fiscal_year" class="form-control form-control-sm" value="{{ $year }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Month</label>
                    <select name="month" class="form-select form-select-sm" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Budget Amount ({{ $currency }})</label>
                    <input type="number" step="0.01" name="amount" class="form-control form-control-sm" placeholder="0.00" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn fc-btn-primary btn-sm w-100">
                        + Save Budget
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Summary KPIs --}}
    <div class="row g-2 mb-4">
        <div class="col-md-4 col-12">
            <div class="fc-card text-center p-3">
                <div class="text-muted small" style="font-size:0.75rem;">Total Budgeted</div>
                <div class="fw-bold font-monospace text-primary mt-1" style="font-size:1.2rem;">{{ number_format($totalBudget, 2) }} {{ $currency }}</div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="fc-card text-center p-3">
                <div class="text-muted small" style="font-size:0.75rem;">Actual Ledger Total</div>
                <div class="fw-bold font-monospace mt-1" style="font-size:1.2rem;">{{ number_format($totalActual, 2) }} {{ $currency }}</div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="fc-card text-center p-3 {{ $totalVariance >= 0 ? 'bg-success-subtle border-success' : 'bg-danger-subtle border-danger' }}">
                <div class="small fw-semibold {{ $totalVariance >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.75rem;">NET VARIANCE</div>
                <div class="fw-bold font-monospace mt-1 {{ $totalVariance >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.2rem;">
                    {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance, 2) }} {{ $currency }}
                </div>
            </div>
        </div>
    </div>

    {{-- Variance Table --}}
    <div class="fc-card p-0">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th class="text-end">Budget Target</th>
                        <th class="text-end">Actual Balance</th>
                        <th class="text-end">Variance Amount</th>
                        <th class="text-end">Variance %</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($varianceReport as $row)
                        <tr>
                            <td class="fw-bold font-monospace">{{ $row['account_code'] }}</td>
                            <td class="fw-semibold">{{ $row['account_name'] }}</td>
                            <td><span class="badge bg-light text-dark">{{ strtoupper($row['account_type']) }}</span></td>
                            <td class="text-end font-monospace">{{ number_format($row['budget_amount'], 2) }}</td>
                            <td class="text-end font-monospace">{{ number_format($row['actual_amount'], 2) }}</td>
                            <td class="text-end font-monospace fw-bold {{ $row['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $row['variance'] >= 0 ? '+' : '' }}{{ number_format($row['variance'], 2) }}
                            </td>
                            <td class="text-end font-monospace {{ $row['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $row['variance_percentage'] }}%
                            </td>
                            <td class="text-center">
                                @if($row['status'] === 'Favorable')
                                    <span class="fc-badge fc-badge-success">FAVORABLE</span>
                                @else
                                    <span class="fc-badge fc-badge-danger">UNFAVORABLE</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size: 2rem;">📊</div>
                                <h6>No budgets set for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h6>
                                <p class="small text-muted mb-3">Click "+ Set Monthly Budget" to allocate targets for revenue or operational expense accounts.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
