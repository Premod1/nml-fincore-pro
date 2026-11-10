@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Fixed Assets &amp; Depreciation</h4>
            <small class="text-muted">Manage capital fixed assets, automated straight-line / reducing-balance depreciation, and net book values.</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="document.getElementById('batchDepreciationCard').classList.toggle('d-none')">
                ⚡ Run Monthly Depreciation
            </button>
            <button type="button" class="btn fc-btn-primary btn-sm px-3" onclick="document.getElementById('newAssetCard').classList.toggle('d-none')">
                + Register Fixed Asset
            </button>
        </div>
    </div>

    {{-- Run Batch Depreciation Card --}}
    <div id="batchDepreciationCard" class="fc-card mb-3 d-none" style="background: #eff6ff; border: 1px dashed #93c5fd;">
        <h6 class="fw-bold mb-2 text-primary">⚡ Post Monthly Depreciation to General Ledger</h6>
        <p class="small text-muted mb-3">Calculates monthly depreciation for all active fixed assets and automatically creates double-entry journal vouchers (Dr Depreciation Expense / Cr Accumulated Depreciation).</p>
        <form action="{{ route('fincore.assets.depreciate') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Depreciation Posting Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn fc-btn-primary btn-sm px-4">
                    Run Batch Depreciation
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="document.getElementById('batchDepreciationCard').classList.add('d-none')">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- Register New Fixed Asset Card --}}
    <div id="newAssetCard" class="fc-card mb-4 {{ $errors->any() ? '' : 'd-none' }}" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
        <h6 class="fw-bold mb-3">+ Register New Capital Fixed Asset</h6>
        <form action="{{ route('fincore.assets.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Asset Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. MacBook Pro M3" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Asset Tag / Code</label>
                    <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. FA-IT-001" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Acquisition / Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Purchase Cost ({{ $currency }})</label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control form-control-sm" placeholder="0.00" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Salvage / Scrap Value</label>
                    <input type="number" step="0.01" name="salvage_value" class="form-control form-control-sm" value="0.00" placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Useful Life (Years)</label>
                    <input type="number" name="useful_life_years" class="form-control form-control-sm" value="5" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Depreciation Method</label>
                    <select name="depreciation_method" class="form-select form-select-sm" required>
                        <option value="straight_line">Straight Line (SLM)</option>
                        <option value="reducing_balance">Reducing Balance (WDV)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Annual Rate (% optional)</label>
                    <input type="number" step="0.01" name="depreciation_rate" class="form-control form-control-sm" placeholder="Auto-calculated from life">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Fixed Asset Ledger Account</label>
                    <select name="asset_account_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Asset Account (1500) --</option>
                        @foreach($assetAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->code === '1500' ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Accumulated Depreciation Account</label>
                    <select name="accumulated_depreciation_account_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Acc. Dep. Account (1600) --</option>
                        @foreach($assetAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->code === '1600' ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Depreciation Expense Account</label>
                    <select name="depreciation_expense_account_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Expense Account (6500) --</option>
                        @foreach($expenseAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->code === '6500' ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="document.getElementById('newAssetCard').classList.add('d-none')">
                        Cancel
                    </button>
                    <button type="submit" class="btn fc-btn-primary btn-sm px-4">
                        + Save Fixed Asset
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Asset Summary KPIs --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="fc-card p-2 text-center">
                <div class="text-muted small" style="font-size:0.75rem;">Registered Assets</div>
                <div class="fw-bold font-monospace mt-1">{{ $assets->total() }} Assets</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="fc-card p-2 text-center">
                <div class="text-muted small" style="font-size:0.75rem;">Total Original Cost</div>
                <div class="fw-bold font-monospace text-primary mt-1">{{ number_format($totalCost, 2) }} {{ $currency }}</div>
            </div>
        </div>
    </div>

    {{-- Fixed Assets Table --}}
    <div class="fc-card p-0">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Name</th>
                        <th>Purchase Date</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Acc. Depreciation</th>
                        <th class="text-end">Book Value (NBV)</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        @php
                            $accumulated = (float) $asset->depreciationLogs->sum('amount');
                            $bookValue = max(0, $asset->purchase_cost - $accumulated);
                        @endphp
                        <tr>
                            <td class="fw-bold font-monospace">
                                <a href="{{ route('fincore.assets.show', $asset->id) }}" class="text-decoration-none">
                                    {{ $asset->code }}
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $asset->name }}</span>
                                <small class="text-muted d-block">{{ $asset->assetAccount?->name }}</small>
                            </td>
                            <td>{{ $asset->purchase_date?->format('Y-m-d') }}</td>
                            <td class="text-end font-monospace">{{ number_format($asset->purchase_cost, 2) }}</td>
                            <td class="text-end font-monospace text-danger">-{{ number_format($accumulated, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-success">{{ number_format($bookValue, 2) }}</td>
                            <td>
                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                    {{ $asset->depreciation_method === 'straight_line' ? 'SLM' : 'WDV' }} ({{ $asset->depreciation_rate }}%)
                                </span>
                            </td>
                            <td>
                                <span class="fc-badge fc-badge-success">{{ strtoupper($asset->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('fincore.assets.show', $asset->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">
                                    Schedule &amp; Logs →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size: 2rem;">🏢</div>
                                <h6>No fixed assets registered</h6>
                                <p class="small text-muted mb-3">Click "+ Register Fixed Asset" to add property, vehicles, IT hardware or machinery.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
            <div class="p-3 border-top">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
@endsection
