<style>
    :root {
        --fc-primary: {{ config('fincore-pro.theme.primary_color', '#2563eb') }};
        --fc-surface: #ffffff;
        --fc-border: #e2e8f0;
        --fc-text: #0f172a;
        --fc-muted: #64748b;
        --fc-success: #059669;
        --fc-danger: #dc2626;
        --fc-warning: #d97706;
    }

    .fincore-scope {
        font-family: inherit;
        color: var(--fc-text);
    }

    .fc-subnav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        background: var(--fc-surface);
        border: 1px solid var(--fc-border);
        border-radius: 0.65rem;
        padding: 0.5rem 0.75rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        align-items: center;
    }

    .fc-subnav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.85rem;
        border-radius: 0.45rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #334155;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }

    .fc-subnav-link:hover {
        background: #f1f5f9;
        color: var(--fc-primary);
    }

    .fc-subnav-link.active {
        background: var(--fc-primary);
        color: #ffffff !important;
        font-weight: 600;
    }

    .fc-card {
        background: var(--fc-surface);
        border: 1px solid var(--fc-border);
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.25rem;
    }

    .fc-kpi-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--fc-muted);
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .fc-kpi-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--fc-text);
    }

    .fc-table {
        width: 100%;
        border-collapse: collapse;
    }

    .fc-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.775rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--fc-border);
    }

    .fc-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .fc-table tr:hover td {
        background-color: #f8fafc;
    }

    .fc-badge {
        display: inline-block;
        padding: 0.22rem 0.6rem;
        font-size: 0.74rem;
        font-weight: 600;
        border-radius: 999px;
    }

    .fc-badge-posted, .fc-badge-success { background: #dcfce7; color: #166534; }
    .fc-badge-draft, .fc-badge-warning { background: #fef3c7; color: #92400e; }
    .fc-badge-void, .fc-badge-danger { background: #fee2e2; color: #991b1b; }
    .fc-badge-info { background: #dbeafe; color: #1e40af; }

    .fc-alert {
        padding: 0.85rem 1.1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    .fc-alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
    .fc-alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

    .fc-btn-primary {
        background-color: var(--fc-primary) !important;
        border-color: var(--fc-primary) !important;
        color: #fff !important;
    }

    @media print {
        .fc-subnav, .fc-no-print {
            display: none !important;
        }
        .fc-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>
