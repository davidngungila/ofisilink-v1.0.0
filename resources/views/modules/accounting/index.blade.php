@extends('layouts.app')

@section('title', 'Accounting Module')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounting Module</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
        height: 100%;
    }

    .summary-card[data-type="accounts"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="journals"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card[data-type="bills"] {
        border-left-color: #dc3545 !important;
    }

    .summary-card[data-type="receivables"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="budgets"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card[data-type="invoices"] {
        border-left-color: #6f42c1 !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .accounting-module-box {
        height: 180px;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 20px;
        color: white;
        text-decoration: none !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .accounting-module-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .accounting-module-box:hover::before {
        left: 100%;
    }

    .accounting-module-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        text-decoration: none !important;
        color: white;
    }

    .accounting-module-box .icon {
        font-size: 3rem;
        margin-bottom: 15px;
        transition: transform 0.3s;
    }

    .accounting-module-box:hover .icon {
        transform: scale(1.1);
    }

    .accounting-module-box .title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .accounting-module-box .description {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .module-section-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #007bff;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-calculator me-2"></i>Accounting Module Dashboard
                    </h2>
                    <p class="mb-0 opacity-90">Comprehensive financial management and reporting</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button class="btn btn-light btn-sm" id="btn-refresh" title="Refresh Dashboard">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4" id="summaryCards" style="display: none;">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="accounts">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Accounts</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="statAccounts">0</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-list-ul fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="journals">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Pending Journals</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="statPendingJournals">0</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-book fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="bills">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Outstanding Bills</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="statOutstandingBills">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-file-blank fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="receivables">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Receivables</h6>
                            <h3 class="mb-0 text-success fw-bold" id="statReceivables">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="invoices">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Invoices</h6>
                            <h3 class="mb-0 text-purple fw-bold" id="statInvoices">0</h3>
                        </div>
                        <div class="bg-purple bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-receipt fs-4 text-purple"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="bills">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Bills</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="statBills">0</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-file fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="journals">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Posted Journals</h6>
                            <h3 class="mb-0 text-info fw-bold" id="statPostedJournals">0</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="budgets">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Active Budgets</h6>
                            <h3 class="mb-0 text-info fw-bold" id="statBudgets">0</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-wallet fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="card border-0 shadow-sm mb-4" id="trendsCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-line-chart me-2"></i>Monthly Financial Trends (Last 6 Months)
            </h6>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Core Accounting Modules -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-cog"></i> Core Accounting
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.chart-of-accounts') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="icon"><i class="bx bx-list-ul"></i></div>
                <div class="title">Chart of Accounts</div>
                <div class="description">Full accounting structure with hierarchy, balances & relationships</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.journal-entries') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="icon"><i class="bx bx-book"></i></div>
                <div class="title">Journal Entries</div>
                <div class="description">Record all financial transactions</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.general-ledger') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="icon"><i class="bx bx-file-blank"></i></div>
                <div class="title">General Ledger</div>
                <div class="description">Central record of all transactions</div>
            </a>
        </div>
    </div>

    <!-- Accounts Payable -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-file-blank"></i> Accounts Payable (A/P)
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ap.vendors') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="icon"><i class="bx bx-truck"></i></div>
                <div class="title">Vendors</div>
                <div class="description">Manage supplier profiles and details</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ap.bills') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="icon"><i class="bx bx-receipt"></i></div>
                <div class="title">Bills</div>
                <div class="description">Record and track vendor invoices</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ap.payments') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="icon"><i class="bx bx-money"></i></div>
                <div class="title">Bill Payments</div>
                <div class="description">Process vendor payments</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ap.aging-report') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                <div class="icon"><i class="bx bx-time"></i></div>
                <div class="title">A/P Aging Report</div>
                <div class="description">Analyze outstanding payables</div>
            </a>
        </div>
    </div>

    <!-- Accounts Receivable -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-money"></i> Accounts Receivable (A/R)
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ar.customers') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                <div class="icon"><i class="bx bx-user"></i></div>
                <div class="title">Customers</div>
                <div class="description">Manage customer profiles</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ar.invoices') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);">
                <div class="icon"><i class="bx bx-receipt"></i></div>
                <div class="title">Invoices</div>
                <div class="description">Create and manage customer invoices</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ar.payments') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%);">
                <div class="icon"><i class="bx bx-money-withdraw"></i></div>
                <div class="title">Invoice Payments</div>
                <div class="description">Record customer payments</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ar.credit-memos') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                <div class="icon"><i class="bx bx-undo"></i></div>
                <div class="title">Credit Memos</div>
                <div class="description">Handle returns and adjustments</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.ar.aging-report') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);">
                <div class="icon"><i class="bx bx-hourglass"></i></div>
                <div class="title">A/R Aging Report</div>
                <div class="description">Track overdue receivables</div>
            </a>
        </div>
    </div>

    <!-- Financial Reports -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-bar-chart-alt-2"></i> Financial Reports
            </h3>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.trial-balance') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="icon"><i class="bx bx-balance"></i></div>
                <div class="title">Trial Balance</div>
                <div class="description">Debit/Credit balance summary</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.balance-sheet') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="icon"><i class="bx bx-file"></i></div>
                <div class="title">Balance Sheet</div>
                <div class="description">Assets, Liabilities & Equity</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.income-statement') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="icon"><i class="bx bx-line-chart"></i></div>
                <div class="title">Income Statement</div>
                <div class="description">Profit & Loss report</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.cash-bank.cash-flow') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="icon"><i class="bx bx-transfer"></i></div>
                <div class="title">Cash Flow Statement</div>
                <div class="description">Operating, investing, financing</div>
            </a>
        </div>
    </div>

    <!-- Budgeting & Forecasting -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-pie-chart"></i> Budgeting & Forecasting
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.budgeting.budgets') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="icon"><i class="bx bx-wallet"></i></div>
                <div class="title">Budgets</div>
                <div class="description">Create and manage budgets</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.budgeting.budget-reports') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="icon"><i class="bx bx-bar-chart"></i></div>
                <div class="title">Budget Reports</div>
                <div class="description">Actual vs Budgeted analysis</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.budgeting.forecasting') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                <div class="icon"><i class="bx bx-trending-up"></i></div>
                <div class="title">Forecasting</div>
                <div class="description">Financial projections</div>
            </a>
        </div>
    </div>

    <!-- Cash & Bank Management -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-building"></i> Cash & Bank Management
            </h3>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('petty-cash.accountant.index') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                <div class="icon"><i class="bx bx-money"></i></div>
                <div class="title">Petty Cash</div>
                <div class="description">Manage petty cash transactions</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('petty-cash.accountant.index') }}?view=reports" class="accounting-module-box" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);">
                <div class="icon"><i class="bx bx-bar-chart-alt-2"></i></div>
                <div class="title">Petty Cash Reports</div>
                <div class="description">View petty cash analytics & reports</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.cash-bank.accounts') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%);">
                <div class="icon"><i class="bx bx-building-house"></i></div>
                <div class="title">Bank Accounts</div>
                <div class="description">Manage bank accounts</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.cash-bank.reconciliation') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="icon"><i class="bx bx-check-double"></i></div>
                <div class="title">Bank Reconciliation</div>
                <div class="description">Reconcile bank statements</div>
            </a>
        </div>
    </div>

    <!-- Taxation -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-receipt"></i> Taxation
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.taxation.index') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="icon"><i class="bx bx-cog"></i></div>
                <div class="title">Tax Settings</div>
                <div class="description">Configure VAT, GST, WHT rates</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.taxation.reports') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="icon"><i class="bx bx-file-blank"></i></div>
                <div class="title">Tax Reports</div>
                <div class="description">Generate tax return reports</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.taxation.paye-management') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="icon"><i class="bx bx-user-check"></i></div>
                <div class="title">PAYE Management</div>
                <div class="description">Payroll tax calculations</div>
            </a>
        </div>
    </div>

    <!-- Fixed Assets -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-building"></i> Fixed Assets Management
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="icon"><i class="bx bx-box"></i></div>
                <div class="title">Asset Register</div>
                <div class="description">Track company assets</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.fixed-assets.depreciation') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="icon"><i class="bx bx-trending-down"></i></div>
                <div class="title">Depreciation</div>
                <div class="description">Calculate asset depreciation</div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('modules.accounting.fixed-assets.reports') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="icon"><i class="bx bx-bar-chart-alt"></i></div>
                <div class="title">Asset Reports</div>
                <div class="description">Asset valuation and reports</div>
            </a>
        </div>
    </div>

    <!-- Advanced -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="module-section-title">
                <i class="bx bx-cog"></i> Advanced
            </h3>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('finance.settings.index') }}" class="accounting-module-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="icon"><i class="bx bx-cog"></i></div>
                <div class="title">Finance Settings</div>
                <div class="description">Setup GL Accounts & Cash Boxes for petty cash & transactions</div>
            </a>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row" id="recentActivities" style="display: none;">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-book me-2"></i>Recent Journal Entries
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="journalsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Entry No</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                </tr>
                            </thead>
                            <tbody id="journalsTableBody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-receipt me-2"></i>Recent Bills & Invoices
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="billsInvoicesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="billsInvoicesTableBody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Advanced Accounting Dashboard Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.dashboard.data') }}';
    let trendsChart = null;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function escapeHtml(s){ 
        return (s||'').replace(/[&<>"']/g, m => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            '\'':'&#39;'
        }[m])); 
    }
    
    function animateValue(id, start, end, duration, isCurrency = false){
        const element = document.getElementById(id);
        if(!element) return;
        const startVal = parseFloat(element.textContent.replace(/[^0-9.-]/g, '')) || start;
        let current = startVal;
        const range = end - startVal;
        const increment = range / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                if(isCurrency) {
                    element.textContent = formatCurrency(end);
                } else if(id.includes('Accounts') || id.includes('Journals') || id.includes('Bills') || id.includes('Invoices') || id.includes('Budgets')) {
                    element.textContent = Math.round(end).toLocaleString();
                } else {
                    element.textContent = formatCurrency(end);
                }
                clearInterval(timer);
            } else {
                if(isCurrency) {
                    element.textContent = formatCurrency(current);
                } else if(id.includes('Accounts') || id.includes('Journals') || id.includes('Bills') || id.includes('Invoices') || id.includes('Budgets')) {
                    element.textContent = Math.round(current).toLocaleString();
                } else {
                    element.textContent = formatCurrency(current);
                }
            }
        }, 16);
    }
    
    function updateStats(stats){
        animateValue('statAccounts', 0, stats.total_accounts || 0, 800);
        animateValue('statPendingJournals', 0, stats.pending_journals || 0, 800);
        animateValue('statOutstandingBills', 0, stats.total_outstanding_bills || 0, 800, true);
        animateValue('statReceivables', 0, stats.total_receivables || 0, 800, true);
        animateValue('statInvoices', 0, stats.total_invoices || 0, 800);
        animateValue('statBills', 0, stats.total_bills || 0, 800);
        animateValue('statPostedJournals', 0, stats.posted_journals || 0, 800);
        animateValue('statBudgets', 0, stats.active_budgets || 0, 800);
    }
    
    function renderTrendsChart(trends){
        const ctx = document.getElementById('trendsChart');
        if(!ctx || !trends || !trends.length) return;
        
        if(trendsChart) trendsChart.destroy();
        
        trendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: trends.map(t => t.month),
                datasets: [
                    {
                        label: 'Bills',
                        data: trends.map(t => t.bills),
                        borderColor: 'rgb(220, 53, 69)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Invoices',
                        data: trends.map(t => t.invoices),
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Payments Received',
                        data: trends.map(t => t.payments_received),
                        borderColor: 'rgb(0, 123, 255)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Payments Made',
                        data: trends.map(t => t.payments_made),
                        borderColor: 'rgb(255, 193, 7)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }
    
    function renderRecentJournals(journals){
        const tbody = document.getElementById('journalsTableBody');
        if(!tbody) return;
        
        if(!journals || !journals.length){
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">No journal entries yet</td></tr>';
            return;
        }
        
        tbody.innerHTML = journals.map((j, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.05}s">
                <td><code class="text-primary">${escapeHtml(j.entry_no || '')}</code></td>
                <td>${escapeHtml(j.entry_date || '')}</td>
                <td>
                    <span class="badge bg-${j.status === 'Posted' ? 'success' : 'warning'}">
                        ${escapeHtml(j.status || '')}
                    </span>
                </td>
                <td>${escapeHtml(j.created_by || 'N/A')}</td>
            </tr>
        `).join('');
    }
    
    function renderRecentBillsInvoices(bills, invoices){
        const tbody = document.getElementById('billsInvoicesTableBody');
        if(!tbody) return;
        
        let html = '';
        
        if(bills && bills.length){
            bills.forEach((b, idx) => {
                html += `
                    <tr class="fade-in" style="animation-delay: ${idx * 0.05}s">
                        <td><code class="text-danger">${escapeHtml(b.bill_no || '')}</code></td>
                        <td>${escapeHtml(b.vendor_name || 'N/A')}</td>
                        <td>${formatCurrency(b.total_amount || 0)}</td>
                        <td>
                            <span class="badge bg-${b.status === 'Paid' ? 'success' : 'warning'}">
                                ${escapeHtml(b.status || '')}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }
        
        if(invoices && invoices.length){
            invoices.forEach((inv, idx) => {
                html += `
                    <tr class="fade-in" style="animation-delay: ${(bills?.length || 0) + idx * 0.05}s">
                        <td><code class="text-success">${escapeHtml(inv.invoice_no || '')}</code></td>
                        <td>${escapeHtml(inv.customer_name || 'N/A')}</td>
                        <td>${formatCurrency(inv.total_amount || 0)}</td>
                        <td>
                            <span class="badge bg-${inv.status === 'Paid' ? 'success' : 'info'}">
                                ${escapeHtml(inv.status || '')}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }
        
        if(!html){
            html = '<tr><td colspan="4" class="text-center py-3 text-muted">No bills or invoices yet</td></tr>';
        }
        
        tbody.innerHTML = html;
    }
    
    function load(){
        showLoading(true);
        document.getElementById('summaryCards').style.display = 'none';
        document.getElementById('trendsCard').style.display = 'none';
        document.getElementById('recentActivities').style.display = 'none';
        
        fetch(endpoint, {
            method:'POST', 
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Accept':'application/json'
            }
        })
        .then(async r=>{
            const text = await r.text();
            let res;
            try { 
                res = JSON.parse(text); 
            } catch(e) { 
                console.error('JSON parse error:', text); 
                throw new Error('Invalid response from server'); 
            }
            
            if(!r.ok || !res.success){ 
                const msg = res.message || 'Failed to load dashboard data';
                console.error('Error:', msg);
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', msg, { duration: 5000, sound: true });
                }
                return; 
            }
            
            document.getElementById('summaryCards').style.display = 'flex';
            if(res.monthly_trends && res.monthly_trends.length){
                document.getElementById('trendsCard').style.display = 'block';
                renderTrendsChart(res.monthly_trends);
            }
            document.getElementById('recentActivities').style.display = 'flex';
            
            updateStats(res.stats || {});
            renderRecentJournals(res.recent_journals || []);
            renderRecentBillsInvoices(res.recent_bills || [], res.recent_invoices || []);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', 'Failed to load dashboard data: ' + err.message, { duration: 5000, sound: true });
            }
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // Event Listeners
    if(document.getElementById('btn-refresh')) {
        document.getElementById('btn-refresh').addEventListener('click', () => { 
            load(); 
        });
    }
    
    // Make load function globally accessible
    window.loadAccountingDashboard = function() {
        load();
    };
    
    // Auto-load on page load
    load();
})();
</script>
@endpush
