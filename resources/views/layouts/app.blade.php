
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Livestock Express | Admin Portal')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        html, body {
            min-height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #172b22;
            background: #f5f8f5;
        }

        .admin-sidebar {
            width: 272px;
            min-width: 272px;
            height: 100vh;
            height: 100dvh;
            position: sticky;
            top: 0;
            background: #0a3622;
            color: #e5f4e9;
            transition: transform .25s ease;
            z-index: 50;
        }

        .admin-main {
            flex: 1 1 0%;
            min-width: 0;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            color: #b8d2c2;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
            text-decoration: none;
            transition: background .2s ease, color .2s ease;
        }

        .admin-nav-link:hover {
            background: rgba(255,255,255,.08);
            color: white;
        }

        .admin-nav-link.active {
            background: #1b5e35;
            color: white;
            box-shadow: inset 3px 0 #86efac;
        }

        .admin-nav-link svg {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
        }

        .admin-card {
            border: 1px solid #e5ece6;
            border-radius: 18px;
            background: white;
            box-shadow: 0 4px 18px rgba(15,54,34,.035);
        }

        .admin-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #789581 transparent;
        }

        .admin-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .admin-scrollbar::-webkit-scrollbar-thumb {
            background: #789581;
            border-radius: 10px;
        }

        .sidebar-backdrop { display: none; }

        @media (max-width: 767px) {
            .admin-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                transform: translateX(-100%);
            }

            .admin-sidebar.sidebar-open {
                transform: translateX(0);
            }

            .sidebar-backdrop.visible {
                display: block;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen overflow-x-hidden">
<div class="flex min-h-screen w-full">

    <button
        id="sidebarBackdrop"
        class="sidebar-backdrop fixed inset-0 z-40 bg-slate-950/50 md:hidden"
        type="button"
        aria-label="Close navigation menu">
    </button>

    <aside id="adminSidebar" class="admin-sidebar flex shrink-0 flex-col">

        <div class="flex min-h-[88px] items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-300 to-emerald-600 text-lg font-extrabold text-[#0a3622] shadow-lg">
                LE
            </div>
            <div class="min-w-0">
                <h1 class="truncate text-base font-extrabold tracking-tight text-white">
                    Livestock Express
                </h1>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.15em] text-emerald-300">
                    City Vet Admin
                </p>
            </div>
        </div>

        <nav class="admin-scrollbar flex-1 space-y-1 overflow-y-auto px-4 py-6">

            <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-500">
                Main Menu
            </p>

            <a href="{{ route('admin.dashboard') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.dashboard')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="2"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="2"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.verifications') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.verifications', 'admin.update')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1v6m-3-3h6"/>
                </svg>
                <span>Manage Users</span>
            </a>

            <a href="{{ route('admin.pending.listings') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.pending.listings')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/>
                </svg>
                <span>Pending Approvals</span>
            </a>

            <a href="{{ route('admin.listings') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.listings', 'admin.deleteListing')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <span>All Listings</span>
            </a>

            <a href="{{ route('admin.transactions') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.transactions')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0-4-4m4 4-4 4M16 17H4m0 0 4 4m-4-4 4-4"/>
                </svg>
                <span>Transactions</span>
            </a>

            <a href="{{ route('admin.deliveries') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.deliveries')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v12H3zM14 10h4l3 3v5h-7zM7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm11 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                </svg>
                <span>Deliveries</span>
            </a>

            <a href="{{ route('admin.payments') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.payments')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 9.5c-.5-1-1.4-1.5-3-1.5-1.4 0-2.5.7-2.5 1.8 0 2.8 5.5 1 5.5 3.8 0 1.1-1.1 1.9-2.8 1.9-1.4 0-2.5-.5-3.2-1.5M12 6v12"/>
                </svg>
                <span>Payment Monitoring</span>
            </a>

            <p class="mb-3 mt-8 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-500">
                Administration
            </p>

            <a href="{{ route('admin.logs') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.logs')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="5" y="4" width="14" height="17" rx="2" stroke-width="2"/>
                    <path stroke-width="2" stroke-linecap="round" d="M9 4V2h6v2M9 9h6M9 13h6M9 17h3"/>
                </svg>
                <span>Activity Logs</span>
            </a>

            <a href="{{ route('admin.support') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.support', 'admin.support.resolve')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M18 10a6 6 0 0 0-12 0v3a3 3 0 0 0 3 3h1m6-6v3a3 3 0 0 1-3 3h-1m-3 0h4m-7-6H4a2 2 0 0 0-2 2v1a2 2 0 0 0 2 2h2m12-5h2a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-2"/>
                </svg>
                <span>Support Tickets</span>
            </a>

            <a href="{{ route('admin.settings') }}"
               @class(['admin-nav-link', 'active' => request()->routeIs('admin.settings', 'admin.settings.update')])>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m19.4 15 .1.1a1.8 1.8 0 0 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 0 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 0 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3H3.8a1.8 1.8 0 0 1 0-3.6H4a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 0 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 0 1 3.6 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 0 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 0 1 0 3.6h-.2a1.8 1.8 0 0 0-.9 3Z"/>
                </svg>
                <span>Settings</span>
            </a>
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-4 flex items-center gap-3 rounded-xl bg-white/5 p-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-300 font-bold text-[#0a3622]">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-bold text-white">
                        {{ auth()->user()->name ?? 'Administrator' }}
                    </p>
                    <p class="mt-1 text-[10px] text-emerald-300">City Veterinary Office</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-300/20 bg-red-400/10 px-4 py-3 text-sm font-bold text-red-200 transition hover:bg-red-400/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-5V5a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5a2 2 0 0 1-2-2v-2"/>
                    </svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-main flex min-h-screen flex-col">

        <header class="sticky top-0 z-30 flex min-h-[88px] items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button id="sidebarToggle" type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-100 md:hidden"
                        aria-label="Open navigation menu"
                        aria-controls="adminSidebar"
                        aria-expanded="false">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="2" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="min-w-0">
                    <h2 class="truncate text-lg font-extrabold tracking-tight text-[#0A3622] sm:text-xl">
                        @yield('header_title', 'Dashboard')
                    </h2>
                    <p class="mt-1 hidden text-xs text-slate-500 sm:block">
                        @yield('header_subtitle', 'Alaminos City Veterinary Office Portal')
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-2 sm:gap-3 sm:px-4">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                </span>
                <span class="text-xs font-bold text-emerald-900">Admin Portal</span>
            </div>
        </header>

        <main class="admin-scrollbar flex-1 overflow-x-hidden overflow-y-auto px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <div class="mx-auto w-full max-w-[1600px]">
                @yield('content')
            </div>

            <footer class="mx-auto mt-10 w-full max-w-[1600px] border-t border-slate-200 py-5 text-xs text-slate-500">
                Livestock Express &middot; Alaminos City Veterinary Office
            </footer>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('adminSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    function closeSidebar() {
        if (!sidebar || !toggle || !backdrop) return;

        sidebar.classList.remove('sidebar-open');
        backdrop.classList.remove('visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
    }

    function openSidebar() {
        if (!sidebar || !toggle || !backdrop) return;

        sidebar.classList.add('sidebar-open');
        backdrop.classList.add('visible');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
    }

    toggle?.addEventListener('click', function () {
        sidebar?.classList.contains('sidebar-open')
            ? closeSidebar()
            : openSidebar();
    });

    backdrop?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 768) closeSidebar();
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768) closeSidebar();
    });
});
</script>

@stack('scripts')
</body>
</html>
