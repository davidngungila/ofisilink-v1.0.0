<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome | OfisiLink</title>

    <!-- Internal Assets Only -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <style>
        :root {
            --primary: #940000;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c3e50;
        }
        .landing-card {
            width: 100%;
            max-width: 980px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(148,0,0,0.15);
            overflow: hidden;
        }
        .landing-header {
            background: linear-gradient(135deg, var(--primary) 0%, #b30000 100%);
            padding: 48px 32px;
            color: #fff;
            position: relative;
        }
        .landing-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(600px 200px at 10% 10%, rgba(255,255,255,0.15), transparent 60%),
                        radial-gradient(600px 200px at 90% 20%, rgba(255,255,255,0.12), transparent 60%);
            pointer-events: none;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }
        .headline {
            margin: 16px 0 8px 0;
            font-size: clamp(1.8rem, 3.2vw, 2.6rem);
            font-weight: 900;
        }
        .subtitle { opacity: .95; max-width: 760px; }
        .landing-body { padding: 32px; }

        .cta-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #b30000);
            border-color: transparent;
        }
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-outline-primary:hover { background: rgba(148,0,0,0.1); }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        .feature {
            border: 1px solid rgba(148,0,0,0.12);
            border-radius: 12px;
            padding: 18px;
            background: linear-gradient(180deg, #fff, #fafbfc);
        }
        .feature i { color: var(--primary); font-size: 24px; }

        .footer {
            padding: 18px 32px;
            border-top: 1px solid #eef0f4;
            font-size: .9rem;
            color: #6c757d;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;
        }
    </style>
</head>
<body>
    <main class="landing-card">
        <header class="landing-header">
            <div class="brand">
                <span class="brand-logo"><i class="bx bxs-building"></i></span>
                <span>OfisiLink</span>
            </div>
            <h1 class="headline">Work Smarter. Manage Better.</h1>
            <p class="subtitle mb-0">A modern platform for HR, Accounting, Files and more—designed for teams that value speed, clarity, and control.</p>
            <div class="cta-row">
                <a href="{{ route('careers') }}" class="btn btn-primary">
                    <i class="bx bx-briefcase-alt-2 me-1"></i> Explore Careers
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light">
                    <i class="bx bx-log-in me-1"></i> Sign In
                </a>
            </div>
        </header>

        <section class="landing-body">
            <div class="features">
                <div class="feature">
                    <div class="d-flex align-items-center mb-2"><i class="bx bx-user-check me-2"></i><strong>Recruitment</strong></div>
                    <div class="text-muted small">Create vacancies, review candidates, schedule interviews, and hire—end to end.</div>
                </div>
                <div class="feature">
                    <div class="d-flex align-items-center mb-2"><i class="bx bx-spreadsheet me-2"></i><strong>Payroll</strong></div>
                    <div class="text-muted small">Structure pay cycles with validations and approvals, then generate payslips instantly.</div>
                </div>
                <div class="feature">
                    <div class="d-flex align-items-center mb-2"><i class="bx bx-file-find me-2"></i><strong>Files</strong></div>
                    <div class="text-muted small">Organize and track physical or digital files with audit trails and access controls.</div>
                </div>
                <div class="feature">
                    <div class="d-flex align-items-center mb-2"><i class="bx bx-bar-chart-alt-2 me-2"></i><strong>Analytics</strong></div>
                    <div class="text-muted small">See trends at a glance—recruiting funnels, processing times, and staffing KPIs.</div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <span>© {{ date('Y') }} OfisiLink. All rights reserved.</span>
            <span>Powered by EmCa Technologies</span>
        </footer>
    </main>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>


