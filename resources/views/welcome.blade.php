<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Media — Job Management</title>
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .page {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .page::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
        }

        /* ── Navbar ── */
        nav {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            padding: 1.4rem 2.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }

        .brand-bar {
            width: 3px;
            height: 1.4rem;
            background: #e53e3e;
            border-radius: 2px;
        }

        .brand-name {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #ffffff;
        }

        /* ── Hero ── */
        .hero {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .divider {
            width: 2.5rem;
            height: 3px;
            background: #e53e3e;
            margin: 0 auto 1.75rem;
        }

        .title {
            font-size: clamp(2rem, 4.5vw, 3.5rem);
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.03em;
            line-height: 1.05;
            margin-bottom: 0.75rem;
        }

        .title span {
            color: #e53e3e;
        }

        .subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.04em;
            margin-bottom: 3rem;
        }

        /* ── Portal grid ── */
        .portals {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            width: 100%;
            max-width: 820px;
        }

        .portal-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.9rem;
            padding: 1.75rem 1.25rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
            cursor: pointer;
        }

        .portal-card:hover {
            background: rgba(229, 62, 62, 0.12);
            border-color: #e53e3e;
            transform: translateY(-3px);
        }

        .portal-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            background: rgba(229, 62, 62, 0.15);
            border-radius: 50%;
            color: #e53e3e;
        }

        .portal-icon svg {
            width: 1.2rem;
            height: 1.2rem;
        }

        .portal-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.85);
        }

        .portal-roles {
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.35);
            letter-spacing: 0.03em;
            line-height: 1.5;
            text-align: center;
        }

        .portal-arrow {
            font-size: 0.7rem;
            color: #e53e3e;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .portal-card:hover .portal-arrow {
            opacity: 1;
        }

        /* ── Footer ── */
        footer {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 1.25rem;
            font-size: 0.68rem;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="page">

        <nav>
            <a href="/" class="brand">
                <div class="brand-bar"></div>
                <span class="brand-name">Household Media</span>
            </a>
        </nav>

        <main class="hero">
            <div class="divider"></div>
            <h1 class="title">Job Management <span>System</span></h1>
            <p class="subtitle">Select your portal to continue</p>

            <div class="portals">

                {{-- Admin --}}
                <a href="/admin" class="portal-card">
                    <div class="portal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <span class="portal-label">Admin Portal</span>
                    <span class="portal-roles">Super Admin · Manager<br>Department Head</span>
                    <span class="portal-arrow">Login &rarr;</span>
                </a>

                {{-- Staff --}}
                <a href="/staff" class="portal-card">
                    <div class="portal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="portal-label">Staff Portal</span>
                    <span class="portal-roles">Field Staff<br>Technicians</span>
                    <span class="portal-arrow">Login &rarr;</span>
                </a>

                {{-- Accountant --}}
                <a href="/accountant" class="portal-card">
                    <div class="portal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <span class="portal-label">Finance Portal</span>
                    <span class="portal-roles">Accountants<br>Finance Team</span>
                    <span class="portal-arrow">Login &rarr;</span>
                </a>

                {{-- Client --}}
                <a href="/client" class="portal-card">
                    <div class="portal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <span class="portal-label">Client Portal</span>
                    <span class="portal-roles">Clients<br>External Partners</span>
                    <span class="portal-arrow">Login &rarr;</span>
                </a>

            </div>
        </main>

        <footer>
            &copy; {{ date('Y') }} Household Media. All rights reserved.
        </footer>

    </div>
</body>
</html>
