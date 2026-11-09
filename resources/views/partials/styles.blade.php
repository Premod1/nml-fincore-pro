<style>
    :root {
        --fc-primary: {{ config('fincore-pro.theme.primary_color', '#0077c5') }}; /* QBO signature blue */
        --fc-primary-hover: #005f9e;
        --fc-surface: #ffffff;
        --fc-bg-subtle: #f4f5f8; /* QBO cool slate canvas */
        --fc-border: #d4d7dc; /* QBO crisp border */
        --fc-border-light: #eceef1;
        --fc-text: #393a3d; /* QBO typography dark gray */
        --fc-text-muted: #6b6c72;
        --fc-success: #108000; /* QBO accounting green */
        --fc-success-bg: #eaf6ea;
        --fc-danger: #d52b1e;
        --fc-danger-bg: #fdf2f1;
        --fc-warning: #ea6c00;
        --fc-warning-bg: #fef7ed;
        --fc-info-bg: #e8f3fa;
    }

    .fincore-scope {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: var(--fc-text);
        background: transparent;
    }

    /* QBO High-Performance Top Header & Subnav */
    .fc-subnav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        background: var(--fc-surface);
        border: 1px solid var(--fc-border);
        border-radius: 6px;
        padding: 0.4rem 0.65rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        align-items: center;
        position: relative;
    }

    .fc-subnav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.75rem;
        border-radius: 4px;
        font-size: 0.84rem;
        font-weight: 500;
        color: var(--fc-text);
        text-decoration: none !important;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
    }

    .fc-subnav-link:hover {
        background: var(--fc-bg-subtle);
        color: var(--fc-primary);
    }

    .fc-subnav-link.active {
        background: #eef5fb;
        color: var(--fc-primary) !important;
        font-weight: 600;
    }

    /* QBO Signature '+ New' Global Action Button */
    .fc-btn-new {
        background-color: var(--fc-success) !important;
        border-color: var(--fc-success) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        border-radius: 20px !important;
        padding: 0.38rem 1rem !important;
        font-size: 0.84rem !important;
        box-shadow: 0 1px 2px rgba(16,128,0,0.2);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .fc-btn-new:hover {
        background-color: #0d6b00 !important;
    }

    /* QBO Dropdown styling */
    .fc-dropdown {
        position: relative;
        display: inline-block;
    }

    .fc-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1060;
        min-width: 220px;
        padding: 0.35rem 0;
        margin-top: 0.25rem;
        background: #ffffff;
        border: 1px solid var(--fc-border);
        border-radius: 6px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .fc-dropdown:hover .fc-dropdown-menu,
    .fc-dropdown:focus-within .fc-dropdown-menu {
        display: block;
    }

    .fc-dropdown-item {
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        font-size: 0.84rem;
        color: var(--fc-text);
        text-decoration: none !important;
        transition: background 0.1s;
    }

    .fc-dropdown-item:hover {
        background: #f1f7fc;
        color: var(--fc-primary);
    }

    .fc-dropdown-divider {
        height: 1px;
        margin: 0.35rem 0;
        background: var(--fc-border-light);
    }

    /* QBO Cards & Canvas */
    .fc-card {
        background: var(--fc-surface);
        border: 1px solid var(--fc-border);
        border-radius: 6px;
        padding: 1.25rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        margin-bottom: 1.25rem;
    }

    .fc-kpi-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fc-text-muted);
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .fc-kpi-value {
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--fc-text);
        font-feature-settings: "tnum";
    }

    /* QBO Accounting Spreadsheet Table */
    .fc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.865rem;
    }

    .fc-table th {
        background: #fafafb;
        color: var(--fc-text-muted);
        font-size: 0.74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.65rem 0.85rem;
        border-top: 1px solid var(--fc-border);
        border-bottom: 1px solid var(--fc-border);
    }

    .fc-table td {
        padding: 0.65rem 0.85rem;
        border-bottom: 1px solid var(--fc-border-light);
        vertical-align: middle;
        font-feature-settings: "tnum";
    }

    .fc-table tr:hover td {
        background-color: #f7fafc;
    }

    /* Drill-down financial links */
    .fc-drill-link {
        color: var(--fc-primary);
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .fc-drill-link:hover {
        text-decoration: underline !important;
        color: var(--fc-primary-hover);
    }

    /* Status Badges */
    .fc-badge {
        display: inline-block;
        padding: 0.18rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .fc-badge-posted, .fc-badge-success { background: var(--fc-success-bg); color: var(--fc-success); }
    .fc-badge-draft, .fc-badge-warning { background: var(--fc-warning-bg); color: var(--fc-warning); }
    .fc-badge-void, .fc-badge-danger { background: var(--fc-danger-bg); color: var(--fc-danger); }
    .fc-badge-info { background: var(--fc-info-bg); color: var(--fc-primary); }

    .fc-alert {
        padding: 0.75rem 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        font-size: 0.86rem;
    }
    .fc-alert-success { background: var(--fc-success-bg); border: 1px solid #b7e3b5; color: #0d5f00; }
    .fc-alert-danger { background: var(--fc-danger-bg); border: 1px solid #f9c0bb; color: var(--fc-danger); }

    .fc-btn-primary {
        background-color: var(--fc-primary) !important;
        border-color: var(--fc-primary) !important;
        color: #ffffff !important;
        font-weight: 500;
    }

    .fc-btn-primary:hover {
        background-color: var(--fc-primary-hover) !important;
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
