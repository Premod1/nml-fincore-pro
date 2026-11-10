@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">System Audit Trail</h4>
            <small class="text-muted">Immutable compliance audit log tracking ledger modifications, approvals, posts, and cancellations.</small>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="fc-card mb-3 p-3 bg-light">
        <form action="{{ route('fincore.audit-trail.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">-- All Actions --</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                            {{ strtoupper($act) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search JV / IP</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="JV number or IP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">
                    Filter Logs
                </button>
            </div>
        </form>
    </div>

    {{-- Audit Logs Table --}}
    <div class="fc-card p-0">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>Journal Voucher</th>
                        <th>User &amp; IP Address</th>
                        <th>Payload / Audit Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="font-monospace small text-nowrap">
                                {{ $log->created_at?->format('Y-m-d H:i:s') }}
                            </td>
                            <td>
                                @if(in_array(strtolower($log->action), ['posted', 'post']))
                                    <span class="fc-badge fc-badge-success">POSTED</span>
                                @elseif(in_array(strtolower($log->action), ['voided', 'void', 'deleted']))
                                    <span class="fc-badge fc-badge-danger">{{ strtoupper($log->action) }}</span>
                                @else
                                    <span class="fc-badge fc-badge-info">{{ strtoupper($log->action) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->journalEntry)
                                    <a href="{{ route('fincore.journals.show', $log->journal_entry_id) }}" class="font-monospace fw-semibold text-decoration-none">
                                        {{ $log->journalEntry->entry_number }}
                                    </a>
                                    <small class="text-muted d-block">{{ $log->journalEntry->reference }}</small>
                                @elseif($log->journal_entry_id)
                                    <span class="font-monospace small text-muted">JV #{{ $log->journal_entry_id }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($log->user_id)
                                    <div class="small fw-semibold">User #{{ $log->user_id }}</div>
                                @endif
                                <div class="font-monospace text-muted" style="font-size:0.75rem;">
                                    IP: {{ $log->ip_address ?: '127.0.0.1' }}
                                </div>
                            </td>
                            <td>
                                @if(!empty($log->new_values) || !empty($log->old_values))
                                    <details>
                                        <summary class="small text-primary cursor-pointer" style="cursor: pointer;">View Change Data</summary>
                                        <pre class="bg-light p-2 rounded mt-1 font-monospace mb-0" style="font-size:0.7rem; max-width: 450px; overflow-x: auto;">@json(['old' => $log->old_values, 'new' => $log->new_values], JSON_PRETTY_PRINT)</pre>
                                    </details>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size: 2rem;">🛡️</div>
                                <h6>No audit logs recorded</h6>
                                <p class="small text-muted mb-0">System audit records will appear here whenever journal vouchers are created, posted, or voided.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-3 border-top">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
