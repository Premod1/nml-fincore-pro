@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Accounting Settings</h4>
            <small class="text-muted">Configure ERP business posting rules, tax rates, fiscal years, and currency parameters.</small>
        </div>
    </div>

    {{-- QBO Settings Tabs --}}
    <div class="fc-card p-0 mb-3" style="overflow: hidden;">
        <div class="d-flex border-bottom bg-light px-3 pt-2" style="gap: 0.5rem;">
            <a href="{{ route('fincore.settings.index', ['tab' => 'rules']) }}"
               class="fc-subnav-link py-2 px-3 {{ $tab === 'rules' ? 'active bg-white border-bottom-0 rounded-top' : '' }}"
               style="border: 1px solid transparent; border-bottom: none;">
                ⚡ ERP Posting Rules (14)
            </a>
            <a href="{{ route('fincore.settings.index', ['tab' => 'taxes']) }}"
               class="fc-subnav-link py-2 px-3 {{ $tab === 'taxes' ? 'active bg-white border-bottom-0 rounded-top' : '' }}"
               style="border: 1px solid transparent; border-bottom: none;">
                🏛️ Sales Tax / VAT Rates
            </a>
            <a href="{{ route('fincore.settings.index', ['tab' => 'fiscal']) }}"
               class="fc-subnav-link py-2 px-3 {{ $tab === 'fiscal' ? 'active bg-white border-bottom-0 rounded-top' : '' }}"
               style="border: 1px solid transparent; border-bottom: none;">
                📅 Fiscal Years &amp; Periods
            </a>
            <a href="{{ route('fincore.settings.index', ['tab' => 'general']) }}"
               class="fc-subnav-link py-2 px-3 {{ $tab === 'general' ? 'active bg-white border-bottom-0 rounded-top' : '' }}"
               style="border: 1px solid transparent; border-bottom: none;">
                ⚙️ General &amp; Currency
            </a>
        </div>
    </div>

    {{-- TAB 1: ERP POSTING RULES --}}
    @if($tab === 'rules')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">ERP Business Event Mappings</h6>
            <form action="{{ route('fincore.mappings.sync') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    ↻ Reset / Sync Default Rules
                </button>
            </form>
        </div>

        <div class="row g-3">
            <div class="col-12">
                @foreach($rules as $rule)
                    <div class="fc-card">
                        <form action="{{ route('fincore.mappings.update', $rule->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold d-inline mb-0">{{ $rule->name }}</h6>
                                    <p class="text-muted small mb-2 mt-1">{{ $rule->description }}</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_post" value="1"
                                           id="autopost_{{ $rule->id }}" {{ $rule->auto_post ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold" for="autopost_{{ $rule->id }}">
                                        Auto-Post to Ledger
                                    </label>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="fc-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%">Entry Side</th>
                                            <th style="width: 40%">Transaction Line Description</th>
                                            <th style="width: 45%">Mapped General Ledger Account</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rule->lines as $line)
                                            <tr>
                                                <td>
                                                    @if($line->type === 'debit')
                                                        <span class="fc-badge fc-badge-info">DEBIT (Dr)</span>
                                                    @else
                                                        <span class="fc-badge fc-badge-warning">CREDIT (Cr)</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $line->label }}</span>
                                                    @if($line->is_optional)
                                                        <span class="text-muted small ms-1">(Optional)</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <select name="lines[{{ $line->id }}]" class="form-select form-select-sm">
                                                        <option value="">-- Select Account --</option>
                                                        @foreach($accounts as $acc)
                                                            <option value="{{ $acc->id }}"
                                                                {{ ($line->account_id == $acc->id || (!$line->account_id && $line->default_code === $acc->code)) ? 'selected' : '' }}>
                                                                {{ $acc->code }} - {{ $acc->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end align-items-center mt-3">
                                <button type="submit" class="btn fc-btn-primary btn-sm px-3">
                                    Save Mapping
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

    {{-- TAB 2: TAXES & VAT --}}
    @elseif($tab === 'taxes')
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="fc-card">
                    <h6 class="fw-bold mb-3">Statutory Tax &amp; VAT Rates</h6>
                    <div class="table-responsive">
                        <table class="fc-table">
                            <thead>
                                <tr>
                                    <th>Tax Code</th>
                                    <th>Tax Name</th>
                                    <th>Rate (%)</th>
                                    <th>Linked Ledger Account</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxes as $tax)
                                    <tr>
                                        <td class="fw-bold">{{ $tax->code }}</td>
                                        <td class="fw-semibold">{{ $tax->name }}</td>
                                        <td><span class="fc-badge fc-badge-info">{{ number_format($tax->rate, 2) }}%</span></td>
                                        <td>{{ $tax->account?->code }} - {{ $tax->account?->name }}</td>
                                        <td>
                                            @if($tax->is_active)
                                                <span class="fc-badge fc-badge-success">ACTIVE</span>
                                            @else
                                                <span class="fc-badge fc-badge-danger">INACTIVE</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('fincore.settings.taxes.toggle', $tax->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.75rem;">
                                                    {{ $tax->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No tax rates defined yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="fc-card">
                    <h6 class="fw-bold mb-3">+ Create New Tax Rate</h6>
                    <form action="{{ route('fincore.settings.taxes.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tax Code</label>
                            <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. VAT-18" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tax Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Value Added Tax (18%)" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Percentage Rate (%)</label>
                            <input type="number" step="0.01" name="rate" class="form-control form-control-sm" placeholder="18.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Liability Account</label>
                            <select name="account_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Liability Account --</option>
                                @foreach($accounts->where('type', \Nml\FinCore\Enums\AccountType::LIABILITY) as $acc)
                                    <option value="{{ $acc->id }}" {{ $acc->code === '2300' ? 'selected' : '' }}>
                                        {{ $acc->code }} - {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn fc-btn-primary btn-sm w-100">
                            + Save Tax Rate
                        </button>
                    </form>
                </div>
            </div>
        </div>

    {{-- TAB 3: FISCAL YEARS & PERIOD LOCKING --}}
    @elseif($tab === 'fiscal')
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="fc-card">
                    <h6 class="fw-bold mb-3">Accounting Fiscal Years</h6>
                    <div class="table-responsive">
                        <table class="fc-table">
                            <thead>
                                <tr>
                                    <th>Fiscal Year</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Periods</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fiscalYears as $fy)
                                    <tr>
                                        <td class="fw-bold">{{ $fy->name }}</td>
                                        <td>{{ $fy->start_date?->format('Y-m-d') }}</td>
                                        <td>{{ $fy->end_date?->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-light text-dark">{{ $fy->periods->count() }} monthly periods</span></td>
                                        <td>
                                            @if($fy->is_closed)
                                                <span class="fc-badge fc-badge-danger">CLOSED</span>
                                            @else
                                                <span class="fc-badge fc-badge-success">OPEN</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No fiscal years defined. Add your current financial year.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="fc-card">
                    <h6 class="fw-bold mb-3">+ Create New Fiscal Year</h6>
                    <form action="{{ route('fincore.settings.fiscal.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Fiscal Year Label</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. FY 2026/2027" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ now()->startOfYear()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ now()->endOfYear()->format('Y-m-d') }}" required>
                        </div>
                        <button type="submit" class="btn fc-btn-primary btn-sm w-100">
                            + Create Fiscal Year &amp; 12 Periods
                        </button>
                    </form>
                </div>
            </div>
        </div>

    {{-- TAB 4: GENERAL & CURRENCY --}}
    @elseif($tab === 'general')
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="fc-card">
                    <h6 class="fw-bold mb-3">General Ledger Parameters</h6>
                    <table class="fc-table">
                        <tbody>
                            <tr>
                                <td class="fw-semibold">Functional Operating Currency</td>
                                <td class="text-end fw-bold text-primary">{{ $currency }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Rounding Tolerance (Dr vs Cr)</td>
                                <td class="text-end font-monospace">{{ config('accounting.rounding_tolerance', '0.005') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Period Locking Enforcement</td>
                                <td class="text-end">
                                    @if(config('accounting.enforce_period_lock', true))
                                        <span class="fc-badge fc-badge-success">ENFORCED</span>
                                    @else
                                        <span class="fc-badge fc-badge-warning">DISABLED</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Database Table Prefix</td>
                                <td class="text-end font-monospace">{{ config('accounting.table_prefix', 'fincore_') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Default Cash Account Codes</td>
                                <td class="text-end font-monospace">1000, 1100</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="fc-card">
                    <h6 class="fw-bold mb-3">Host ERP Theme &amp; Layout Integration</h6>
                    <table class="fc-table">
                        <tbody>
                            <tr>
                                <td class="fw-semibold">Integration Mode</td>
                                <td class="text-end"><span class="fc-badge fc-badge-info">{{ strtoupper(config('fincore-pro.mode', 'embedded')) }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">ERP Blade Layout</td>
                                <td class="text-end font-monospace">{{ config('fincore-pro.erp_layout', 'layouts.app') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">ERP Content Section</td>
                                <td class="text-end font-monospace">Section: {{ config('fincore-pro.erp_section', 'content') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Brand Primary Color</td>
                                <td class="text-end">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 12px; height: 12px; background: {{ config('fincore-pro.theme.primary_color', '#0077c5') }}; vertical-align: middle;"></span>
                                    {{ config('fincore-pro.theme.primary_color', '#0077c5') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
