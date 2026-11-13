@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">AI Receipt &amp; Invoice OCR Scanner</h4>
            <small class="text-muted">
                Powered by Gemini Vision (<code>{{ $geminiModel }}</code>) &rarr; Auto-generates Draft <code>credit_purchase</code> Vouchers
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('fincore.journals.index', ['status' => 'draft']) }}" class="btn btn-outline-secondary btn-sm">
                View All Draft Vouchers
            </a>
            <a href="{{ route('fincore.mappings.index') }}" class="btn btn-outline-primary btn-sm">
                ⚡ ERP Purchase Rule
            </a>
        </div>
    </div>

    @if(!$geminiConfigured)
        <div class="fc-alert fc-alert-danger mb-3">
            <span>
                <strong>Gemini API Key Missing:</strong> Set <code>GEMINI_API_KEY</code> in your <code>.env</code> file to enable live AI OCR scanning. You can still enter or review draft purchase vouchers manually below.
            </span>
        </div>
    @endif

    <div class="row g-3 mb-4">
        {{-- Left Column: Drag-and-Drop Uploader & Extracted Line Items --}}
        <div class="col-lg-6">
            <div class="fc-card h-100">
                <h6 class="fw-bold mb-1">1. Upload Receipt Photo or PDF Supplier Invoice</h6>
                <p class="text-muted small mb-3">
                    Drag &amp; drop a PDF, JPG, PNG, or WEBP file (max {{ number_format($maxFileKb / 1024, 0) }} MB) for instant Gemini Vision OCR extraction.
                </p>

                <div id="fc-ocr-dropzone"
                     class="border border-2 border-dashed rounded-3 p-4 text-center bg-light"
                     style="cursor: pointer; border-color: #cbd5e1; transition: all 0.15s ease;">
                    <input type="file" id="fc-ocr-file-input" accept=".pdf,image/jpeg,image/png,image/webp,image/gif" class="d-none">
                    <div style="font-size: 2rem; line-height: 1.2;">📄</div>
                    <div class="fw-semibold mt-2">Drop receipt or invoice here, or <span class="text-primary text-decoration-underline">browse</span></div>
                    <div class="text-muted small mt-1">Supports PDF supplier invoices &amp; receipt photos</div>
                </div>

                {{-- Scan Status Banner --}}
                <div id="fc-ocr-status" class="mt-3 d-none"></div>

                {{-- File Preview --}}
                <div id="fc-ocr-preview" class="mt-3 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-muted" id="fc-ocr-filename"></span>
                        <button type="button" id="fc-ocr-rescan-btn" class="btn btn-sm btn-outline-primary py-0 px-2">
                            ↻ Re-scan
                        </button>
                    </div>
                    <div class="border rounded bg-white p-2 text-center" style="max-height: 280px; overflow: auto;">
                        <img id="fc-ocr-preview-img" src="" alt="Receipt preview" class="img-fluid rounded d-none" style="max-height: 250px;">
                        <div id="fc-ocr-preview-pdf" class="py-4 text-muted d-none">
                            <div style="font-size: 2.2rem;">📑</div>
                            <div class="small fw-semibold mt-1">PDF Invoice Loaded</div>
                        </div>
                    </div>
                </div>

                {{-- Extracted Line Items --}}
                <div id="fc-ocr-items-wrap" class="mt-3 d-none">
                    <h6 class="fw-bold small text-uppercase text-muted mb-2">Extracted Line Items</h6>
                    <div class="table-responsive">
                        <table class="fc-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="fc-ocr-items-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Auto-Populated Draft Credit Purchase Form --}}
        <div class="col-lg-6">
            <div class="fc-card h-100">
                <h6 class="fw-bold mb-1">2. Review Extracted Metadata &amp; Generate Draft Voucher</h6>
                <p class="text-muted small mb-3">
                    Populates <code>ErpPostingEngine::recordErpEvent('credit_purchase')</code> as a Draft Voucher (Dr Inventory &amp; Input Tax / Cr Accounts Payable).
                </p>

                <form method="POST" action="{{ route('fincore.receipts.draft') }}" id="fc-ocr-draft-form">
                    @csrf

                    <div class="row g-2">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Supplier Name *</label>
                            <input type="text" name="supplier_name" id="ocr_supplier_name"
                                   class="form-control form-control-sm"
                                   value="{{ old('supplier_name') }}"
                                   placeholder="e.g. Acme Supplies Ltd" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Invoice / Receipt #</label>
                            <input type="text" name="invoice_number" id="ocr_invoice_number"
                                   class="form-control form-control-sm"
                                   value="{{ old('invoice_number') }}"
                                   placeholder="e.g. INV-2026-0042">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Invoice Date *</label>
                            <input type="date" name="invoice_date" id="ocr_invoice_date"
                                   class="form-control form-control-sm"
                                   value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Currency</label>
                            <input type="text" name="currency" id="ocr_currency" maxlength="3"
                                   class="form-control form-control-sm text-uppercase"
                                   value="{{ old('currency', $currency) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Net Amount *</label>
                            <input type="number" step="0.01" min="0.01" name="net_amount" id="ocr_net_amount"
                                   class="form-control form-control-sm text-end"
                                   value="{{ old('net_amount') }}" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tax / VAT Amount</label>
                            <input type="number" step="0.01" min="0" name="tax_amount" id="ocr_tax_amount"
                                   class="form-control form-control-sm text-end"
                                   value="{{ old('tax_amount', '0.00') }}" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Total Amount *</label>
                            <input type="number" step="0.01" min="0.01" name="total_amount" id="ocr_total_amount"
                                   class="form-control form-control-sm text-end fw-bold"
                                   value="{{ old('total_amount') }}" placeholder="0.00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Debit Account Override <span class="text-muted fw-normal">(Optional — defaults to Credit Purchase mapping)</span></label>
                            <select name="inventory_account_id" class="form-select form-select-sm">
                                <option value="">Use Default ERP Mapping (1300 - Merchandise Inventory)</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ (string) old('inventory_account_id') === (string) $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code }} - {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Narration / Notes</label>
                            <input type="text" name="notes" id="ocr_notes"
                                   class="form-control form-control-sm"
                                   value="{{ old('notes') }}"
                                   placeholder="Summary of purchased items or services">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4 pt-2 border-top">
                        <button type="submit" name="action" value="draft" class="btn fc-btn-primary btn-sm flex-grow-1">
                            💾 Save as Draft Voucher
                        </button>
                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm flex-grow-1">
                            ✓ Approve &amp; Post Immediately
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bottom Section: One-Click Review & Approval Queue --}}
    <div class="fc-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-0">3. Pending Draft Purchase Vouchers (One-Click Review &amp; Approval)</h6>
                <small class="text-muted">Review OCR-generated or draft supplier bills and post them to the General Ledger in one click.</small>
            </div>
            <span class="badge bg-warning text-dark">{{ $draftVouchers->count() }} Pending Draft(s)</span>
        </div>

        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th style="width: 13%">Voucher #</th>
                        <th style="width: 10%">Date</th>
                        <th style="width: 13%">Invoice Ref</th>
                        <th style="width: 30%">Supplier &amp; Narration</th>
                        <th style="width: 12%" class="text-end">Total ({{ $currency }})</th>
                        <th style="width: 7%">Status</th>
                        <th style="width: 15%" class="text-end">Accountant Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($draftVouchers as $voucher)
                        <tr>
                            <td>
                                <a href="{{ route('fincore.journals.show', $voucher->id) }}" class="fw-bold text-decoration-none">
                                    {{ $voucher->entry_number }}
                                </a>
                            </td>
                            <td>{{ $voucher->date?->format('Y-m-d') }}</td>
                            <td><span class="text-muted">{{ $voucher->reference ?? '—' }}</span></td>
                            <td>
                                <div>{{ $voucher->description }}</div>
                                <div class="text-muted" style="font-size: 0.74rem;">
                                    {{ $voucher->lines->map(fn($l) => ($l->account?->code ?? '') . ' ' . strtoupper($l->type) . ' ' . number_format($l->amount, 2))->implode(' · ') }}
                                </div>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($voucher->getDebitSum(), 2) }}</td>
                            <td><span class="fc-badge fc-badge-draft">DRAFT</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form method="POST" action="{{ route('fincore.receipts.approve', $voucher->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm py-1 px-2">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <a href="{{ route('fincore.journals.show', $voucher->id) }}" class="btn btn-outline-secondary btn-sm py-1 px-2">
                                        Review
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No pending draft purchase vouchers. Upload a receipt or invoice above to generate one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            const dropzone = document.getElementById('fc-ocr-dropzone');
            const fileInput = document.getElementById('fc-ocr-file-input');
            const statusBox = document.getElementById('fc-ocr-status');
            const previewWrap = document.getElementById('fc-ocr-preview');
            const previewImg = document.getElementById('fc-ocr-preview-img');
            const previewPdf = document.getElementById('fc-ocr-preview-pdf');
            const filenameLabel = document.getElementById('fc-ocr-filename');
            const rescanBtn = document.getElementById('fc-ocr-rescan-btn');
            const itemsWrap = document.getElementById('fc-ocr-items-wrap');
            const itemsBody = document.getElementById('fc-ocr-items-body');

            const netInput = document.getElementById('ocr_net_amount');
            const taxInput = document.getElementById('ocr_tax_amount');
            const totalInput = document.getElementById('ocr_total_amount');

            let lastSelectedFile = null;

            function syncTotalFromNetAndTax() {
                const net = parseFloat(netInput.value) || 0;
                const tax = parseFloat(taxInput.value) || 0;
                if (net > 0 || tax > 0) {
                    totalInput.value = (net + tax).toFixed(2);
                }
            }

            netInput.addEventListener('input', syncTotalFromNetAndTax);
            taxInput.addEventListener('input', syncTotalFromNetAndTax);

            function showStatus(type, html) {
                statusBox.className = 'mt-3 fc-alert fc-alert-' + type;
                statusBox.innerHTML = '<span>' + html + '</span>';
                statusBox.classList.remove('d-none');
            }

            function renderPreview(file) {
                previewWrap.classList.remove('d-none');
                filenameLabel.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';

                if (file.type === 'application/pdf') {
                    previewImg.classList.add('d-none');
                    previewPdf.classList.remove('d-none');
                } else {
                    previewPdf.classList.add('d-none');
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        previewImg.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str ?? '';
                return div.innerHTML;
            }

            function populateForm(data) {
                document.getElementById('ocr_supplier_name').value = data.supplier_name || '';
                document.getElementById('ocr_invoice_number').value = data.invoice_number || '';
                document.getElementById('ocr_invoice_date').value = data.invoice_date || '';
                if (data.currency) {
                    document.getElementById('ocr_currency').value = data.currency;
                }
                netInput.value = Number(data.net_amount || 0).toFixed(2);
                taxInput.value = Number(data.tax_amount || 0).toFixed(2);
                totalInput.value = Number(data.total_amount || 0).toFixed(2);
                document.getElementById('ocr_notes').value = data.notes || '';

                itemsBody.innerHTML = '';
                if (Array.isArray(data.line_items) && data.line_items.length > 0) {
                    data.line_items.forEach(function (item) {
                        const tr = document.createElement('tr');
                        tr.innerHTML =
                            '<td>' + escapeHtml(item.description) + '</td>' +
                            '<td class="text-end">' + Number(item.quantity || 1).toFixed(2) + '</td>' +
                            '<td class="text-end">' + Number(item.unit_price || 0).toFixed(2) + '</td>' +
                            '<td class="text-end fw-semibold">' + Number(item.line_total || 0).toFixed(2) + '</td>';
                        itemsBody.appendChild(tr);
                    });
                    itemsWrap.classList.remove('d-none');
                } else {
                    itemsWrap.classList.add('d-none');
                }
            }

            async function scanFile(file) {
                lastSelectedFile = file;
                renderPreview(file);
                showStatus('success', '<strong>Scanning with Gemini Vision...</strong> Extracting supplier metadata and line items.');

                const csrfToken = document.querySelector('#fc-ocr-draft-form input[name="_token"]')?.value || '{{ csrf_token() }}';
                const formData = new FormData();
                formData.append('receipt', file);
                formData.append('_token', csrfToken);

                try {
                    const response = await fetch('{{ route("fincore.receipts.scan") }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.ok) {
                        const errMsg = payload.message || (payload.errors && payload.errors.receipt && payload.errors.receipt[0]) || 'OCR extraction failed.';
                        showStatus('danger', '<strong>OCR Error:</strong> ' + escapeHtml(errMsg));
                        return;
                    }

                    populateForm(payload.data);
                    showStatus('success', '<strong>Extraction Complete:</strong> Review the auto-populated draft voucher fields on the right and click Save or Approve.');
                } catch (err) {
                    showStatus('danger', '<strong>Network Error:</strong> ' + escapeHtml(err.message));
                }
            }

            dropzone.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    scanFile(fileInput.files[0]);
                }
            });

            dropzone.addEventListener('dragover', function (e) {
                e.preventDefault();
                dropzone.style.borderColor = '#2563eb';
                dropzone.classList.add('bg-white');
            });

            dropzone.addEventListener('dragleave', function () {
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.classList.remove('bg-white');
            });

            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.classList.remove('bg-white');
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    scanFile(e.dataTransfer.files[0]);
                }
            });

            rescanBtn.addEventListener('click', function () {
                if (lastSelectedFile) {
                    scanFile(lastSelectedFile);
                }
            });
        })();
    </script>
@endsection
