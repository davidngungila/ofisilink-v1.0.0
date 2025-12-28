<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Career Opportunities | OfisiLink</title>
    
    <!-- Internal Assets -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    
    <style>
        :root {
            --primary-color: #940000;
            --primary-dark: #7a0000;
            --primary-light: #b30000;
            --secondary-color: #f8f9fa;
            --accent-color: #ff6b6b;
            --text-color: #2c3e50;
            --text-muted: #6c757d;
            --heading-color: #1a1a1a;
            --border-radius: 16px;
            --border-radius-sm: 12px;
            --box-shadow: 0 12px 40px rgba(148, 0, 0, 0.1);
            --box-shadow-hover: 0 20px 60px rgba(148, 0, 0, 0.2);
            --transition-speed: 0.4s;
            --transition-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
            --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            --gradient-secondary: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            --gradient-success: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(148, 0, 0, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(148, 0, 0, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(148, 0, 0, 0.05) 0%, transparent 50%);
            animation: floatingParticles 25s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: -1;
        }

        @keyframes floatingParticles {
            0% { transform: translateY(0px) translateX(0px) scale(1); }
            100% { transform: translateY(-30px) translateX(15px) scale(1.08); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }

        .header {
            background: var(--gradient-primary);
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            margin-bottom: 50px;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            animation: shimmer 4s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .header-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header .brand {
            color: #fff;
            font-size: clamp(1.4rem, 3vw, 2.2rem);
            font-weight: 300;
            margin-bottom: 20px;
            opacity: 0.95;
            animation: slideInFromTop 1s var(--transition-bounce) 0.2s both;
            letter-spacing: 2px;
        }

        .header h1 {
            color: #fff;
            margin: 0 0 25px 0;
            font-size: clamp(2.8rem, 6vw, 4.5rem);
            font-weight: 900;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
            animation: slideInFromTop 1s var(--transition-bounce);
            letter-spacing: -1px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.98);
            font-size: clamp(1.1rem, 2.2vw, 1.5rem);
            font-weight: 300;
            animation: slideInFromTop 1s var(--transition-bounce) 0.4s both;
            line-height: 1.9;
        }

        @keyframes slideInFromTop {
            from { opacity: 0; transform: translateY(-60px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Search and Filter Section */
        .search-section {
            background: #fff;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .search-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 16px 20px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: all var(--transition-speed);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.1);
        }

        /* Job Listings */
        .job-listings {
            margin-top: 30px;
        }

        #job-listings-container {
            min-height: 400px;
            position: relative;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 35px;
            padding: 20px 0;
        }

        .job-card {
            background: #fff;
            border: 2px solid rgba(148, 0, 0, 0.08);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--box-shadow);
            transition: all var(--transition-speed) var(--transition-bounce);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
            display: flex;
            flex-direction: column;
        }

        .job-card:nth-child(1) { animation-delay: 0.1s; }
        .job-card:nth-child(2) { animation-delay: 0.2s; }
        .job-card:nth-child(3) { animation-delay: 0.3s; }
        .job-card:nth-child(4) { animation-delay: 0.4s; }
        .job-card:nth-child(5) { animation-delay: 0.5s; }
        .job-card:nth-child(6) { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 5px;
            background: var(--gradient-primary);
            transition: left 0.7s ease;
        }

        .job-card:hover::before {
            left: 0;
        }

        .job-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: var(--box-shadow-hover);
            border-color: var(--primary-color);
        }

        .job-card.closed {
            opacity: 0.65;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #dee2e6;
            cursor: not-allowed;
        }

        .job-card.closed:hover {
            transform: translateY(-5px);
        }

        .job-title {
            color: var(--primary-color);
            font-size: 1.9rem;
            font-weight: 800;
            margin: 0 0 25px 0;
            position: relative;
            transition: color var(--transition-speed);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .job-card:hover .job-title {
            color: var(--primary-dark);
        }

        .job-meta {
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 15px;
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .job-meta span {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: rgba(148, 0, 0, 0.06);
            border-radius: var(--border-radius-sm);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .job-meta span:hover {
            background: rgba(148, 0, 0, 0.12);
            transform: translateX(8px);
        }

        .job-meta i {
            margin-right: 14px;
            color: var(--primary-color);
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .job-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(148, 0, 0, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
            background: var(--gradient-primary);
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all var(--transition-speed) var(--transition-bounce);
            position: relative;
            overflow: hidden;
            min-width: 140px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            transition: all 0.7s ease;
            transform: translate(-50%, -50%);
        }

        .btn:hover::before {
            width: 400px;
            height: 400px;
        }

        .btn:disabled {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(148, 0, 0, 0.35);
        }

        .btn:active {
            transform: translateY(-2px);
        }

        .btn.secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }

        .btn.secondary:hover {
            box-shadow: 0 12px 28px rgba(108, 117, 125, 0.35);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: #fff;
            margin: 2% auto;
            padding: 0;
            border: none;
            width: 96%;
            max-width: 900px;
            border-radius: var(--border-radius);
            box-shadow: 0 25px 80px rgba(148, 0, 0, 0.3);
            animation: modalSlideIn 0.6s var(--transition-bounce);
            overflow: hidden;
            max-height: 95vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-60px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            background: var(--gradient-primary);
            color: #fff;
            padding: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .modal-header::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 100%;
            height: 24px;
            background: var(--gradient-primary);
            clip-path: polygon(0 0, 100% 0, 96% 100%, 4% 100%);
        }

        .modal-title {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .modal-body {
            padding: 45px;
            max-height: calc(95vh - 200px);
            overflow-y: auto;
            flex: 1;
        }

        .modal-footer {
            padding: 30px 45px;
            border-top: 2px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 18px;
            background: var(--gradient-secondary);
        }

        .close-btn {
            color: rgba(255, 255, 255, 0.9);
            font-size: 36px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(90deg) scale(1.1);
        }

        .modal-body h4 {
            color: var(--primary-color);
            margin: 30px 0 18px 0;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 4px solid var(--primary-color);
            position: relative;
        }

        .modal-body h4::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-color);
        }

        .modal-body .content-block {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            white-space: pre-wrap;
            font-size: 1.05rem;
            border: 2px solid rgba(148, 0, 0, 0.1);
            position: relative;
            line-height: 1.8;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .modal-body .content-block::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        .modal-body ul {
            padding-left: 30px;
            margin: 18px 0;
        }

        .modal-body li {
            margin-bottom: 15px;
            position: relative;
            padding-left: 12px;
            font-size: 1.05rem;
        }

        .modal-body li::before {
            content: '✓';
            position: absolute;
            left: -24px;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Form Styles */
        #applicationForm .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 28px;
            margin-top: 25px;
        }

        @media (min-width: 768px) {
            #applicationForm .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-group label {
            margin-bottom: 12px;
            font-weight: 700;
            color: var(--heading-color);
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group input,
        .form-group textarea {
            padding: 18px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1.05rem;
            transition: all var(--transition-speed);
            font-family: inherit;
        }

        .form-group input[type="file"] {
            padding: 14px;
            border-style: dashed;
            cursor: pointer;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 5px rgba(148, 0, 0, 0.12);
            transform: translateY(-3px);
        }

        .form-group input:valid,
        .form-group textarea:valid {
            border-color: #28a745;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .file-upload-item {
            margin-bottom: 25px;
            padding: 25px;
            background: rgba(148, 0, 0, 0.03);
            border-radius: var(--border-radius);
            border: 2px dashed rgba(148, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .file-upload-item:hover {
            border-color: var(--primary-color);
            background: rgba(148, 0, 0, 0.06);
        }

        .file-upload-item input[type="file"].invalid {
            border-color: #dc3545;
            animation: shake 0.6s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .file-size-warning {
            font-size: 0.9rem;
            color: #dc3545;
            display: none;
            margin-top: 10px;
            padding: 10px 14px;
            background: rgba(220, 53, 69, 0.12);
            border-radius: var(--border-radius-sm);
            border: 2px solid rgba(220, 53, 69, 0.25);
        }

        .loader {
            width: 70px;
            height: 70px;
            border: 8px solid rgba(148, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 8px solid var(--primary-color);
            animation: spin 1.2s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .loader::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 35px;
            height: 35px;
            border: 4px solid rgba(148, 0, 0, 0.2);
            border-radius: 50%;
            border-top: 4px solid var(--accent-color);
            animation: spin 1.8s linear infinite reverse;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Status Indicators */
        .status-indicator {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin-right: 10px;
            animation: pulse 2.5s infinite;
        }

        .status-open {
            background: #28a745;
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }

        .status-closed {
            background: #dc3545;
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 currentColor; }
            70% { box-shadow: 0 0 0 12px transparent; }
            100% { box-shadow: 0 0 0 0 transparent; }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 25px;
            opacity: 0.8;
        }

        .empty-state h3 {
            color: var(--text-color);
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* Loading State */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid transparent;
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Custom Scrollbar */
        .modal-body::-webkit-scrollbar {
            width: 10px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 5px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header {
                padding: 50px 0;
            }

            #job-listings-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .job-card {
                padding: 30px;
            }

            .modal-content {
                width: 98%;
                margin: 3% auto;
            }

            .modal-body {
                padding: 30px;
            }

            .modal-footer {
                padding: 25px 30px;
                flex-direction: column;
            }

            .search-container {
                flex-direction: column;
            }
        }

        /* Success Animation */
        .success-pulse {
            animation: successPulse 0.7s ease-out;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="brand">OfisiLink</div>
            <h1>Career Opportunities</h1>
            <p>Join our dynamic team and help us revolutionize the future. Discover exciting positions that match your passion and expertise.</p>
        </div>
    </header>

    <main class="container">
        <section class="search-section">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search for positions, keywords..." autocomplete="off" aria-label="Search jobs">
                <select id="sortSelect" class="search-input" style="max-width:220px" aria-label="Sort jobs">
                    <option value="newest">Newest</option>
                    <option value="deadline">Deadline soon</option>
                    <option value="title_asc">Title A-Z</option>
                    <option value="title_desc">Title Z-A</option>
                </select>
                <button id="filterBtn" class="btn" type="button" aria-expanded="false"><i class="bx bx-filter"></i> Filters</button>
            </div>
            <div id="filtersPanel" class="card" style="display:none;margin-top:15px;">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <input id="filterDepartment" type="text" class="form-control" placeholder="e.g. HR, IT">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input id="filterLocation" type="text" class="form-control" placeholder="e.g. Dar es Salaam">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Type</label>
                            <input id="filterEmployment" type="text" class="form-control" placeholder="e.g. Full-time">
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button id="applyFilters" class="btn" type="button"><i class="bx bx-search"></i> Apply</button>
                        <button id="clearFilters" class="btn secondary" type="button"><i class="bx bx-reset"></i> Clear</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="job-listings">
            <div id="job-listings-container"></div>
            <div id="pagination" class="d-flex justify-content-center align-items-center mt-3" style="gap:10px;"></div>
        </section>
    </main>

    <!-- Job Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle" class="modal-title">Job Details</h2>
                <span class="close-btn" id="closeDetailsBtn">&times;</span>
            </div>
            <div id="modalBody" class="modal-body"></div>
            <div class="modal-footer">
                <button class="btn secondary" id="cancelDetailsBtn">
                    <i class="bx bx-x"></i> Close
                </button>
                <button class="btn" id="applyFromDetailsBtn">
                    <i class="bx bx-paper-plane"></i> Apply Now
                </button>
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <form id="applicationForm" novalidate>
                <div class="modal-header">
                    <h2 id="applicationModalTitle" class="modal-title">Apply for Position</h2>
                    <span class="close-btn" id="closeApplicationBtn">&times;</span>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="job_id" id="formJobId">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <ul class="nav nav-tabs" id="applyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-personal" data-bs-toggle="tab" data-bs-target="#pane-personal" type="button" role="tab">
                                <i class="bx bx-user"></i> Personal Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-docs" data-bs-toggle="tab" data-bs-target="#pane-docs" type="button" role="tab">
                                <i class="bx bx-paperclip"></i> Documents
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-review" data-bs-toggle="tab" data-bs-target="#pane-review" type="button" role="tab">
                                <i class="bx bx-check-circle"></i> Review & Submit
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="pane-personal" role="tabpanel">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="firstName"><i class="bx bx-user"></i> First Name *</label>
                                    <input type="text" id="firstName" name="first_name" required autocomplete="given-name">
                                </div>
                                <div class="form-group">
                                    <label for="lastName"><i class="bx bx-user"></i> Last Name *</label>
                                    <input type="text" id="lastName" name="last_name" required autocomplete="family-name">
                                </div>
                                <div class="form-group">
                                    <label for="email"><i class="bx bx-envelope"></i> Email Address *</label>
                                    <input type="email" id="email" name="email" required autocomplete="email">
                                </div>
                                <div class="form-group">
                                    <label for="phone"><i class="bx bx-phone"></i> Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" required autocomplete="tel" placeholder="e.g. 2557XXXXXXXX">
                                    <small class="text-muted">Format: 255XXXXXXXXX to receive SMS updates</small>
                                </div>
                                <div class="form-group full-width">
                                    <label for="address"><i class="bx bx-map"></i> Current Address</label>
                                    <textarea id="address" name="current_address" rows="3" autocomplete="street-address"></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label for="coverLetter"><i class="bx bx-file"></i> Cover Letter</label>
                                    <textarea id="coverLetter" name="cover_letter" rows="6" placeholder="Optional: Tell us why you're interested in this position and what makes you a great fit for our team..."></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-check">
                                        <input type="checkbox" id="smsOptIn" name="sms_opt_in" value="1"> I agree to receive SMS updates about my application
                                    </label>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="button" class="btn" id="toDocsBtn"><i class="bx bx-right-arrow-alt"></i> Next</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pane-docs" role="tabpanel">
                            <div id="required-attachments-container" class="full-width" style="margin-top: 10px;"></div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn secondary" id="backToPersonal"><i class="bx bx-left-arrow-alt"></i> Back</button>
                                <button type="button" class="btn" id="toReviewBtn"><i class="bx bx-right-arrow-alt"></i> Next</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pane-review" role="tabpanel">
                            <div id="reviewContent" class="content-block"></div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn secondary" id="backToDocs"><i class="bx bx-left-arrow-alt"></i> Back</button>
                                <button type="submit" class="btn" id="submitApplicationBtn"><i class="bx bx-paper-plane"></i> Submit Application</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-none"></div>
            </form>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const API_URL = {
                fetchJobs: '{{ route("api.jobs.public") }}',
                submitApplication: '{{ route("api.jobs.apply") }}'
            };

            const MAX_FILE_SIZE_MB = 10;
            const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

            const jobContainer = document.getElementById('job-listings-container');
            const detailsModal = document.getElementById('detailsModal');
            const applicationModal = document.getElementById('applicationModal');
            const applicationForm = document.getElementById('applicationForm');
            const searchInput = document.getElementById('searchInput');
            const sortSelect = document.getElementById('sortSelect');
            const filterBtn = document.getElementById('filterBtn');
            const filtersPanel = document.getElementById('filtersPanel');
            const applyFiltersBtn = document.getElementById('applyFilters');
            const clearFiltersBtn = document.getElementById('clearFilters');
            const filterDepartment = document.getElementById('filterDepartment');
            const filterLocation = document.getElementById('filterLocation');
            const filterEmployment = document.getElementById('filterEmployment');
            const paginationEl = document.getElementById('pagination');

            let allJobsData = [];
            let filteredJobs = [];
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 12;

            function openModal(modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                const content = modal.querySelector('.modal-content');
                content.style.animation = 'none';
                content.offsetHeight;
                content.style.animation = 'modalSlideIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            }

            function closeModal(modal) {
                const content = modal.querySelector('.modal-content');
                content.style.animation = 'modalSlideOut 0.4s ease-in forwards';
                setTimeout(() => {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }, 400);
            }

            // Add modal slide out animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes modalSlideOut {
                    from { opacity: 1; transform: translateY(0) scale(1); }
                    to { opacity: 0; transform: translateY(-40px) scale(0.95); }
                }
            `;
            document.head.appendChild(style);

            document.getElementById('closeDetailsBtn').onclick = () => closeModal(detailsModal);
            document.getElementById('cancelDetailsBtn').onclick = () => closeModal(detailsModal);
            document.getElementById('closeApplicationBtn').onclick = () => closeModal(applicationModal);
            const cancelApplicationBtn = document.getElementById('cancelApplicationBtn');
            if (cancelApplicationBtn) cancelApplicationBtn.onclick = () => closeModal(applicationModal);

            window.onclick = function (event) {
                if (event.target == detailsModal) closeModal(detailsModal);
                if (event.target == applicationModal) closeModal(applicationModal);
            }

            function showLoader() {
                jobContainer.innerHTML = '<div class="loader"></div>';
            }

            function renderJobs(jobs) {
                jobContainer.innerHTML = '';

                if (!jobs || jobs.length === 0) {
                    jobContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="bx bx-briefcase"></i>
                            <h3>No Open Positions</h3>
                            <p>There are currently no open positions. Please check back later for new opportunities.</p>
                        </div>
                    `;
                    return;
                }

                jobs.forEach((job, index) => {
                    const deadline = new Date(job.application_deadline);
                    const isExpired = deadline < new Date(new Date().toDateString());

                    let modes = [];
                    try {
                        modes = Array.isArray(job.interview_mode) ? job.interview_mode : JSON.parse(job.interview_mode || '[]');
                    } catch (e) {
                        modes = job.interview_mode ? [job.interview_mode] : ['Not specified'];
                    }
                    const modesText = modes.length > 0 ? modes.join(' / ') : 'Not specified';

                    const card = document.createElement('div');
                    card.className = `job-card ${isExpired ? 'closed' : ''}`;
                    card.style.animationDelay = `${index * 0.1}s`;

                    card.innerHTML = `
                        <div class="job-header">
                            <h3 class="job-title">
                                <span class="status-indicator ${isExpired ? 'status-closed' : 'status-open'}"></span>
                                ${job.job_title}
                            </h3>
                            <div class="job-meta">
                                <span><i class="bx bx-video"></i>Interview Mode: ${modesText}</span>
                                <span><i class="bx bx-calendar-x"></i> Deadline: ${deadline.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })}</span>
                            </div>
                        </div>
                        <div class="job-actions">
                            <button class="btn secondary view-details-btn" data-job-id="${job.id}">
                                <i class="bx bx-info-circle"></i> View Details
                            </button>
                            ${!isExpired ?
                                `<button class="btn apply-now-btn" data-job-id="${job.id}">
                                    <i class="bx bx-paper-plane"></i> Apply Now
                                </button>` :
                                `<button class="btn" disabled>
                                    <i class="bx bx-lock"></i> Closed
                                </button>`
                            }
                        </div>
                    `;

                    jobContainer.appendChild(card);
                });
            }

            function renderPagination(meta) {
                paginationEl.innerHTML = '';
                if (!meta || meta.last_page <= 1) return;

                currentPage = meta.page;
                lastPage = meta.last_page;

                const createBtn = (label, page, disabled=false, active=false) => {
                    const btn = document.createElement('button');
                    btn.className = `btn ${active ? 'secondary' : ''}`;
                    btn.textContent = label;
                    btn.disabled = disabled;
                    btn.addEventListener('click', () => {
                        if (!disabled) {
                            fetchAndDisplayJobs(page);
                        }
                    });
                    return btn;
                };

                paginationEl.appendChild(createBtn('Prev', currentPage - 1, currentPage === 1));
                // Pages window
                const start = Math.max(1, currentPage - 2);
                const end = Math.min(lastPage, currentPage + 2);
                for (let p = start; p <= end; p++) {
                    paginationEl.appendChild(createBtn(String(p), p, false, p === currentPage));
                }
                paginationEl.appendChild(createBtn('Next', currentPage + 1, currentPage === lastPage));
            }

            async function fetchAndDisplayJobs(page = 1) {
                showLoader();
                try {
                    const params = new URLSearchParams();
                    if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
                    if (sortSelect.value) params.set('sort', sortSelect.value);
                    if (filterDepartment.value.trim()) params.set('department', filterDepartment.value.trim());
                    if (filterLocation.value.trim()) params.set('location', filterLocation.value.trim());
                    if (filterEmployment.value.trim()) params.set('employment_type', filterEmployment.value.trim());
                    params.set('page', page);
                    params.set('per_page', perPage);

                    const response = await fetch(`${API_URL.fetchJobs}?${params.toString()}`);
                    if (!response.ok) throw new Error(`Network response was not ok (${response.statusText}).`);
                    const data = await response.json();
                    const jobs = Array.isArray(data) ? data : data.jobs;
                    allJobsData = jobs || [];
                    filteredJobs = allJobsData;
                    renderJobs(filteredJobs);
                    renderPagination(data.meta);
                } catch (error) {
                    console.error('Error fetching jobs:', error);
                    jobContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="bx bx-error" style="color: #dc3545;"></i>
                            <h3>Unable to Load Jobs</h3>
                            <p>Failed to load job listings. Please check your connection and try again.</p>
                            <button class="btn" onclick="location.reload()" style="margin-top: 20px;">
                                <i class="bx bx-refresh"></i> Retry
                            </button>
                        </div>
                    `;
                }
            }

            // Search functionality
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                if (searchTerm === '') {
                    filteredJobs = allJobsData;
                } else {
                    filteredJobs = allJobsData.filter(job => {
                        return job.job_title.toLowerCase().includes(searchTerm) ||
                               job.job_description.toLowerCase().includes(searchTerm) ||
                               job.qualifications.toLowerCase().includes(searchTerm);
                    });
                }
                renderJobs(filteredJobs);
            });

            // Event delegation for job card buttons
            jobContainer.addEventListener('click', function (e) {
                const jobId = e.target.closest('button')?.dataset.jobId;
                if (!jobId) return;

                e.target.closest('button').classList.add('success-pulse');
                setTimeout(() => {
                    e.target.closest('button').classList.remove('success-pulse');
                }, 700);

                if (e.target.closest('.view-details-btn')) {
                    showJobDetails(jobId);
                }
                if (e.target.closest('.apply-now-btn')) {
                    openApplicationForm(jobId);
                }
            });

            document.getElementById('applyFromDetailsBtn').addEventListener('click', function () {
                const jobId = this.dataset.jobId;
                closeModal(detailsModal);
                setTimeout(() => openApplicationForm(jobId), 400);
            });

            function showJobDetails(jobId) {
                const job = allJobsData.find(j => j.id == jobId);
                if (!job) return;

                const deadline = new Date(job.application_deadline);
                const isExpired = deadline < new Date(new Date().toDateString());

                document.getElementById('modalTitle').innerHTML = `
                    <i class="bx bx-briefcase"></i> ${job.job_title}
                `;

                let attachments = [];
                try {
                    attachments = Array.isArray(job.required_attachments) ? job.required_attachments : JSON.parse(job.required_attachments || '[]');
                } catch (e) {
                    attachments = ['Resume/CV'];
                }

                let attachmentsHtml = attachments.length > 0 ?
                    attachments.map(item => `<li><i class=\"bx bx-file\"></i> ${item}</li>`).join('') :
                    '<li><i class=\"bx bx-file\"></i> Resume/CV</li>';

                let modes = [];
                try {
                    modes = Array.isArray(job.interview_mode) ? job.interview_mode : JSON.parse(job.interview_mode || '[]');
                } catch (e) {
                    modes = job.interview_mode ? [job.interview_mode] : ['Not specified'];
                }
                const modesText = modes.length > 0 ? modes.join(' / ') : 'Not specified';

                document.getElementById('modalBody').innerHTML = `
                    <div class=\"job-meta\" style=\"margin-bottom: 35px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;\">
                        <span><i class=\"bx bx-video\"></i> <strong>Interview Mode:</strong> ${modesText}</span>
                        <span><i class=\"bx bx-calendar-x\"></i> <strong>Deadline:</strong> ${deadline.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })}</span>
                    </div>
                    
                    <h4><i class=\"bx bx-file\"></i> Job Description</h4>
                    <div class="content-block">${job.job_description || 'No description provided.'}</div>
                    
                    <h4><i class=\"bx bx-graduation\"></i> Qualifications & Requirements</h4>
                    <div class="content-block">${job.qualifications || 'No qualifications specified.'}</div>
                    
                    <h4><i class=\"bx bx-paperclip\"></i> Required Documents</h4>
                    <ul style="background: rgba(148, 0, 0, 0.06); padding: 25px; border-radius: 12px; border-left: 5px solid var(--primary-color);">${attachmentsHtml}</ul>
                    
                    ${isExpired ? `
                        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #fff; padding: 25px; border-radius: 12px; text-align: center; margin-top: 25px;">
                            <i class=\"bx bx-error\" style=\"font-size: 2rem; margin-bottom: 12px;\"></i>
                            <p style="margin: 0; font-weight: 700; font-size: 1.1rem;">This position is no longer accepting applications.</p>
                        </div>
                    ` : `
                        <div style="background: var(--gradient-success); color: #fff; padding: 25px; border-radius: 12px; text-align: center; margin-top: 25px;">
                            <i class=\"bx bx-check-circle\" style=\"font-size: 2rem; margin-bottom: 12px;\"></i>
                            <p style="margin: 0; font-weight: 700; font-size: 1.1rem;">Applications are currently being accepted for this position.</p>
                        </div>
                    `}
                `;

                document.getElementById('applyFromDetailsBtn').dataset.jobId = jobId;
                document.getElementById('applyFromDetailsBtn').disabled = isExpired;
                if (isExpired) {
                    document.getElementById('applyFromDetailsBtn').innerHTML = '<i class=\"bx bx-lock\"></i> Position Closed';
                } else {
                    document.getElementById('applyFromDetailsBtn').innerHTML = '<i class=\"bx bx-paper-plane\"></i> Apply Now';
                }

                openModal(detailsModal);
            }

            function openApplicationForm(jobId) {
                const job = allJobsData.find(j => j.id == jobId);
                if (!job) return;

                applicationForm.reset();
                document.getElementById('applicationModalTitle').innerHTML = `
                    <i class=\"bx bx-paper-plane\"></i> Apply for: ${job.job_title}
                `;
                document.getElementById('formJobId').value = jobId;

                const attachmentsContainer = document.getElementById('required-attachments-container');
                let attachments = [];
                try {
                    attachments = Array.isArray(job.required_attachments) ? job.required_attachments : JSON.parse(job.required_attachments || '[]');
                } catch (e) {
                    attachments = ['Resume/CV'];
                }

                attachmentsContainer.innerHTML = '';

                if (attachments.length > 0) {
                    let attachmentsHtml = `<h4><i class=\"bx bx-paperclip\"></i> Required Documents</h4>`;
                    attachments.forEach((docType, index) => {
                        const docId = `doc_${index}`;
                        attachmentsHtml += `
                            <div class="form-group file-upload-item">
                                <label for=\"${docId}\"><i class=\"bx bx-upload\"></i> ${docType} *</label>
                                <input type="file" id="${docId}" name="attachments[]" 
                                       data-doc-type="${docType}" required 
                                       accept=".pdf,.doc,.docx,.txt,image/*">
                                <small style=\"color: var(--text-muted); font-size: 0.95rem; margin-top: 8px;\">
                                    <i class=\"bx bx-info-circle\"></i> Accepted: PDF, DOC, DOCX, TXT, Images (Max ${MAX_FILE_SIZE_MB}MB)
                                </small>
                                <small class="file-size-warning">
                                    <i class=\"bx bx-error\"></i> File size must be less than ${MAX_FILE_SIZE_MB}MB
                                </small>
                                <input type="hidden" name="doc_types[]" value="${docType}">
                            </div>
                        `;
                    });
                    attachmentsContainer.innerHTML = attachmentsHtml;

                    document.querySelectorAll('input[type="file"]').forEach(input => {
                        input.addEventListener('change', function () {
                            validateFileSize(this);
                            if (this.files.length > 0) {
                                this.parentNode.style.background = 'rgba(40, 167, 69, 0.12)';
                                this.parentNode.style.borderColor = '#28a745';
                            }
                        });
                    });
                }

                // Ensure first tab is active when opening form
                const personalTabBtn = document.getElementById('tab-personal');
                if (personalTabBtn) personalTabBtn.click();
                openModal(applicationModal);
            }

            function validateFileSize(fileInput) {
                const file = fileInput.files[0];
                const warning = fileInput.parentNode.querySelector('.file-size-warning');
                if (file && file.size > MAX_FILE_SIZE_BYTES) {
                    fileInput.classList.add('invalid');
                    warning.style.display = 'block';
                    fileInput.parentNode.style.background = 'rgba(220, 53, 69, 0.12)';
                    fileInput.parentNode.style.borderColor = '#dc3545';
                    return false;
                } else {
                    fileInput.classList.remove('invalid');
                    warning.style.display = 'none';
                    if (file) {
                        fileInput.parentNode.style.background = 'rgba(40, 167, 69, 0.12)';
                        fileInput.parentNode.style.borderColor = '#28a745';
                    }
                    return true;
                }
            }

            // Tab navigation helpers
            function validatePersonalTab() {
                const req = ['firstName','lastName','email','phone'];
                let ok = true;
                req.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el.value.trim()) { ok = false; el.style.borderColor = '#dc3545'; }
                });
                return ok;
            }

            function buildReviewSummary() {
                const jobTitle = document.getElementById('applicationModalTitle').innerText.replace('Apply for: ','');
                const name = `${document.getElementById('firstName').value} ${document.getElementById('lastName').value}`;
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value || '—';
                const cover = document.getElementById('coverLetter').value || '—';
                const sms = document.getElementById('smsOptIn').checked ? 'Yes' : 'No';
                const docs = Array.from(document.querySelectorAll('#required-attachments-container input[type="file"]'))
                  .map(i => `${i.dataset.docType}: ${i.files && i.files[0] ? i.files[0].name : 'Missing'}`)
                  .join('<br>');
                document.getElementById('reviewContent').innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Position</strong><br>${jobTitle}</div>
                        <div class="col-md-6"><strong>Name</strong><br>${name}</div>
                        <div class="col-md-6"><strong>Email</strong><br>${email}</div>
                        <div class="col-md-6"><strong>Phone</strong><br>${phone}</div>
                        <div class="col-md-6"><strong>Address</strong><br>${address}</div>
                        <div class="col-md-6"><strong>SMS Updates</strong><br>${sms}</div>
                        <div class="col-12"><strong>Cover Letter</strong><br>${cover}</div>
                        <div class="col-12"><strong>Documents</strong><br>${docs}</div>
                    </div>`;
            }

            document.getElementById('toDocsBtn').addEventListener('click', function(){
                if (!validatePersonalTab()) {
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '320px';
                    toast.innerHTML = `<strong>Missing information</strong><br>Please fill all required personal details.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(()=>{ $(toast).fadeOut(()=> toast.remove()); }, 4000);
                    return;
                }
                document.querySelector('#tab-docs').click();
            });
            document.getElementById('backToPersonal').addEventListener('click', function(){
                document.querySelector('#tab-personal').click();
            });
            document.getElementById('toReviewBtn').addEventListener('click', function(){
                // validate docs
                let allFilesValid = true;
                document.querySelectorAll('#required-attachments-container input[type="file"]').forEach(input => {
                    if (!validateFileSize(input) || input.files.length === 0) allFilesValid = false;
                });
                if (!allFilesValid) {
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '320px';
                    toast.innerHTML = `<strong>File issues</strong><br>Check required documents and sizes.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(()=>{ $(toast).fadeOut(()=> toast.remove()); }, 4000);
                    return;
                }
                buildReviewSummary();
                document.querySelector('#tab-review').click();
            });
            document.getElementById('backToDocs').addEventListener('click', function(){
                document.querySelector('#tab-docs').click();
            });

            applicationForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submitApplicationBtn');
                let allFilesValid = true;

                document.querySelectorAll('input[type="file"]').forEach(input => {
                    if (!validateFileSize(input)) {
                        allFilesValid = false;
                    }
                });

                if (!allFilesValid) {
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '320px';
                    toast.innerHTML = `<strong>File Size Error</strong><br>Please ensure all files are under ${MAX_FILE_SIZE_MB}MB.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(()=>{ $(toast).fadeOut(()=> toast.remove()); }, 5000);
                    return;
                }

                let isValid = true;
                const requiredFields = this.querySelectorAll('[required]');
                requiredFields.forEach(input => {
                    if ((input.type !== 'file' && !input.value.trim()) ||
                        (input.type === 'file' && input.files.length === 0)) {
                        isValid = false;
                        input.style.borderColor = '#dc3545';
                        input.style.animation = 'shake 0.6s ease-in-out';
                        setTimeout(() => { input.style.animation = ''; }, 600);
                    } else {
                        input.style.borderColor = '#28a745';
                    }
                });

                if (!isValid) {
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '320px';
                    toast.innerHTML = `<strong>Incomplete Form</strong><br>Please fill out all required fields.
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(()=>{ $(toast).fadeOut(()=> toast.remove()); }, 4000);
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.textContent = 'Submitting...';

                const formData = new FormData(this);
                formData.append('_token', CSRF_TOKEN);

                try {
                    const response = await fetch(API_URL.submitApplication, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || `Server responded with status ${response.status}`);
                    }

                    closeModal(applicationModal);

                    // Bootstrap alert toast
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '320px';
                    toast.innerHTML = `<strong>Application Submitted!</strong><br>Thank you for your application.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(()=>{ $(toast).fadeOut(()=> toast.remove()); }, 4000);

                    applicationForm.reset();
                } catch (error) {
                    console.error('Submission error:', error);
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '320px';
                    toast.innerHTML = `<strong>Submission Failed</strong><br>${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    document.body.appendChild(toast);
                    setTimeout(()=>{ $(toast).fadeOut(()=> toast.remove()); }, 6000);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    submitBtn.innerHTML = '<i class="bx bx-paper-plane"></i> Submit Application';
                }
            });

            document.addEventListener('input', function (e) {
                if (e.target.matches('input[required], textarea[required]')) {
                    if (e.target.value.trim()) {
                        e.target.style.borderColor = '#28a745';
                    } else {
                        e.target.style.borderColor = '#ced4da';
                    }
                }
            });

            // Toggle filters panel
            filterBtn.addEventListener('click', function(){
                const visible = filtersPanel.style.display === 'block';
                filtersPanel.style.display = visible ? 'none' : 'block';
                this.setAttribute('aria-expanded', String(!visible));
            });

            applyFiltersBtn.addEventListener('click', function(){
                fetchAndDisplayJobs(1);
            });
            clearFiltersBtn.addEventListener('click', function(){
                filterDepartment.value = '';
                filterLocation.value = '';
                filterEmployment.value = '';
                fetchAndDisplayJobs(1);
            });

            // Search & Sort
            let searchDebounce;
            searchInput.addEventListener('input', function(){
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(()=> fetchAndDisplayJobs(1), 400);
            });
            sortSelect.addEventListener('change', function(){
                fetchAndDisplayJobs(1);
            });

            // Deep link to job
            const urlParams = new URLSearchParams(window.location.search);
            const deepJobId = urlParams.get('job');

            setTimeout(async () => {
                await fetchAndDisplayJobs();
                if (deepJobId) {
                    const check = setInterval(()=>{
                        if (allJobsData && allJobsData.length) {
                            clearInterval(check);
                            const exists = allJobsData.find(j => String(j.id) === String(deepJobId));
                            if (exists) showJobDetails(deepJobId);
                        }
                    }, 200);
                }
            }, 400);
        });
    </script>
</body>
</html>

