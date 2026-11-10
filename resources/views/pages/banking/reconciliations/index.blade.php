@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Bank Reconciliations</h4>
            <small class="text-muted">Reconcile bank and cash accounts against bank statements to ensure zero ledger variance.</small>
        </div>
        <div>
            <button type="button" class="btn fc-btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#newReconciliationModal" onclick="document.getElementById('newReconcileCard').classList.toggle('d-none')">
                + New Reconciliation Statement
            </button>
        </div>
    </div>

    {{-- Create New Reconciliation Card --}}
    <div id="newReconcileCard" class="fc-card mb-4 {{ $errors->any() ? '' : 'd-none' }}" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
        <h6 class="fw-bold mb-3">Start New Bank Reconciliation Statement</h6>
        <form action="{{ route('fincore.banking.reconciliations.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Bank / Cash Account</label>
                    <select name="account_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Bank Account --</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statement Ending Date</label>
                    <input type="date" name="statement_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Beginning / Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control form-control-sm" placeholder="0.00" value="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Ending / Closing Balance</label>
                    <input type="number" step="0.01" name="closing_balance" class="form-control form-control-sm" placeholder="0.00" required>
                </div>
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="document.getElementById('newReconcileCard').classList.add('d-none')">
                        Cancel
                    </button>
                    <button type="submit" class="btn fc-btn-primary btn-sm px-4">
                        Continue to Reconcile →
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Reconciliations List --}}
    <div class="fc-card p-0">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Statement Date</th>
                        <th>Bank / Cash Account</th>
                        <th class="text-end">Opening Balance</th>
                        <th class="text-end">Closing Balance</th>
                        <th class="text-center">Status</th>
                        <th>Reconciled At</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliations as $rec)
                        <tr>
                            <td class="fw-bold">{{ $rec->statement_date?->format('Y-m-d') }}</td>
                            <td>
                                <span class="fw-semibold">{{ $rec->account?->code }} - {{ $rec->account?->name }}</span>
                            </td>
                            <td class="text-end font-monospace">{{ number_format($rec->opening_balance, 2) }}</td>
                            <td class="text-end font-monospace fw-bold">{{ number_format($rec->closing_balance, 2) }}</td>
                            <td class="text-center">
                                @if($rec->is_finalized)
                                    <span class="fc-badge fc-badge-success">FINALIZED</span>
                                @else
                                    <span class="fc-badge fc-badge-warning">IN PROGRESS</span>
                                @endif
                            </td>
                            <td>
                                {{ $rec->reconciled_at ? $rec->reconciled_at->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('fincore.banking.reconciliations.show', $rec->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.78rem;">
                                    {{ $rec->is_finalized ? 'View Summary' : 'Reconcile →' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size: 2rem;">🏦</div>
                                <h6>No bank reconciliations found</h6>
                                <p class="small text-muted mb-3">Click "+ New Reconciliation Statement" to match your physical bank statement with general ledger entries.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reconciliations->hasPages())
            <div class="p-3 border-top">
                {{ $reconciliations->links() }}
            </div>
        @endif
    </div>
@endsection
