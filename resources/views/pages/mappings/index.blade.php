@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">ERP Auto-Posting Rules &amp; Account Mapping</h4>
            <small class="text-muted">
                Configure which General Ledger accounts are debited and credited when business events occur in your ERP.
            </small>
        </div>
        <form action="{{ route('fincore.mappings.sync') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                ↻ Reset to Defaults
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
@endsection
