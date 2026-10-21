
<nav class="flex h-screen w-64 shrink-0 flex-col border-r border-emerald-900 bg-[#0F5132] text-white shadow-lg">

    {{-- Brand Header --}}
    <div class="flex h-20 shrink-0 items-center border-b border-white/10 px-5">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-lg font-extrabold text-white">
                LE
            </div>

            <div>
                <h1 class="text-base font-extrabold tracking-tight leading-tight">
                    Livestock Express
                </h1>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-200">
                    City Vet Admin
                </p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="admin-scrollbar flex-1 space-y-6 overflow-y-auto px-3 py-5">

        {{-- Main Menu --}}
        <div>
            <p class="mb-3 px-4 text-[10px] font-extrabold uppercase tracking-[0.2em] text-emerald-300">
                Main Menu
            </p>

            <div class="space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.dashboard')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="1.8"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="1.8"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="1.8"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="1.8"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                {{-- Pending Approvals --}}
                <a href="{{ route('admin.pending.listings') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.pending.listings')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l2.5 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <span>Pending Approvals</span>
                </a>

                {{-- All Listings --}}
                <a href="{{ route('admin.listings') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.listings')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="16" rx="2" stroke-width="1.8"/>
                        <path stroke-width="1.8" stroke-linecap="round" d="M3 9h18M7 14h4"/>
                    </svg>
                    <span>All Listings</span>
                </a>

                {{-- Deliveries --}}
                <a href="{{ route('admin.deliveries') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.deliveries')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                              d="M3 7h11v11H3zM14 11h4l3 3v4h-7zM7 18a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0"/>
                    </svg>
                    <span>Deliveries</span>
                </a>

                {{-- Transactions --}}
                <a href="{{ route('admin.transactions') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.transactions')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h13m0 0-4-4m4 4-4 4M17 17H4m0 0 4 4m-4-4 4-4"/>
                    </svg>
                    <span>Transactions</span>
                </a>

                {{-- Payment Monitoring --}}
                <a href="{{ route('admin.payments') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.payments')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8"/>
                        <path stroke-width="1.8" stroke-linecap="round" d="M3 10h18M8 15h3"/>
                    </svg>
                    <span>Payment Monitoring</span>
                </a>

            </div>
        </div>

        {{-- Administration --}}
        <div>
            <p class="mb-3 px-4 text-[10px] font-extrabold uppercase tracking-[0.2em] text-emerald-300">
                Administration
            </p>

            <div class="space-y-1">

                {{-- Manage Users / Verification --}}
                <a href="{{ route('admin.verifications') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.verifications*', 'admin.update')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                              d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-3h6m-3-3v6"/>
                    </svg>
                    <span>User Verification</span>
                </a>

                {{-- Support Tickets --}}
                <a href="{{ route('admin.support') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.support*')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                              d="M8 10h8M8 14h5m-9 6 1.5-4A8 8 0 1 1 20 16.5L21 20l-4-1A8 8 0 0 1 4 20Z"/>
                    </svg>
                    <span>Support Tickets</span>
                </a>

                {{-- Activity Logs --}}
                <a href="{{ route('admin.logs') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.logs')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="5" y="3" width="14" height="18" rx="2" stroke-width="1.8"/>
                        <path stroke-width="1.8" stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/>
                    </svg>
                    <span>Activity Logs</span>
                </a>

                {{-- Settings --}}
                <a href="{{ route('admin.settings') }}"
                   class="flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm transition-all
                   {{ request()->routeIs('admin.settings')
                        ? 'border-emerald-300 bg-[#1B5E35] font-bold text-white shadow-sm'
                        : 'border-transparent text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm8 4a8 8 0 0 0-.1-1.3l1.4-1.1-1.5-2.6-1.7.7a8 8 0 0 0-2.2-1.3L15.6 4h-3.1l-.3 2.4A8 8 0 0 0 10 7.7l-1.8-.7-1.5 2.6 1.4 1.1A8 8 0 0 0 8 12c0 .4 0 .9.1 1.3l-1.4 1.1 1.5 2.6 1.8-.7a8 8 0 0 0 2.2 1.3l.3 2.4h3.1l.3-2.4a8 8 0 0 0 2.2-1.3l1.7.7 1.5-2.6-1.4-1.1A8 8 0 0 0 20 12Z"/>
                    </svg>
                    <span>Settings</span>
                </a>

            </div>
        </div>
    </div>

    {{-- Admin Profile and Logout --}}
    <div class="shrink-0 border-t border-white/10 p-3">
        <div class="mb-3 flex items-center gap-3 rounded-xl bg-white/5 p-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-400 font-extrabold text-emerald-950">
                {{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-white">
                    {{ auth()->user()->name ?? 'City Veterinary Office' }}
                </p>
                <p class="truncate text-xs text-emerald-300">
                    City Veterinary Office
                </p>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                    class="flex w-full items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-emerald-100 transition hover:border-red-400/30 hover:bg-red-500/20 hover:text-white">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                          d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
                </svg>
                <span>Sign Out</span>
            </button>
        </form>
    </div>

</nav>
