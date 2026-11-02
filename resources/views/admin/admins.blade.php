@extends('layouts.app')

@section('title', 'Manage Administrators - Livestock Express')
@section('header_title', 'Administrator Management')
@section('header_subtitle', 'Manage system administrators and super administrator access levels.')

@section('content')
<div class="max-w-7xl mx-auto space-y-7 pb-12">

    {{-- Success message --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Errors --}}
    @if($errors->any())
        <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm font-semibold">{{ $errors->first() }}</p>
        </div>
    @endif

    {{-- Page introduction & metrics header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Super Admin Portal Controls
                </div>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                    System Administrators
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-50/85">
                    Provision new administrative credentials, supervise active privileges, and manage role security levels across the platform.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm sm:min-w-[160px]">
                    <p class="text-xs font-semibold text-emerald-100">Total Admins</p>
                    <p class="mt-2 text-3xl font-extrabold">{{ isset($admins) ? $admins->count() : 0 }}</p>
                </div>
                <button
                    type="button"
                    onclick="openCreateAdminModal()"
                    class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-4 text-xs font-extrabold text-[#0A3622] shadow-sm transition hover:bg-emerald-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Admin
                </button>
            </div>
        </div>

        <div class="pointer-events-none absolute -right-12 -top-20 h-64 w-64 rounded-full border-[35px] border-white/5"></div>
        <div class="pointer-events-none absolute -bottom-28 right-40 h-56 w-56 rounded-full bg-white/5"></div>
    </div>

    {{-- Admins Directory Table Section --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-[#1B5E20]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Authorized Personnel Directory</h2>
                    <p class="mt-1 text-xs text-slate-500">List of accounts with backend panel access</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Active Access Control
            </span>
        </div>

        @if(!isset($admins) || $admins->isEmpty())
            <div class="px-6 py-14 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">No administrator accounts found</h3>
                <p class="mt-1 text-sm text-slate-500">Create a new administrator account using the button above.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left">
                    <thead class="bg-slate-50">
                        <tr class="border-y border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Administrator Name</th>
                            <th class="px-6 py-4">Email Address</th>
                            <th class="px-6 py-4">Role Level</th>
                            <th class="px-6 py-4">Date Added (PHT)</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($admins as $adminAccount)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-extrabold uppercase text-[#1B5E20]">
                                            {{ strtoupper(substr($adminAccount->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="break-words text-sm font-bold text-slate-900">
                                                {{ $adminAccount->name }}
                                                @if($adminAccount->id === auth()->id())
                                                    <span class="ml-2 rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold text-emerald-800 font-monospace">You</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-600">
                                    {{ $adminAccount->email }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($adminAccount->role === 'super_admin')
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Super Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Standard Admin
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-500">
                                    {{ $adminAccount->created_at ? $adminAccount->created_at->setTimezone('Asia/Manila')->format('M d, Y h:i A') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($adminAccount->id !== auth()->id())
                                        <form action="{{ route('admin.admins.delete', $adminAccount->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to revoke access for this admin?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                Revoke Access
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold text-slate-400 italic">Protected Account</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

{{-- Create Admin Modal --}}
<div id="createAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-white px-5 py-4 sm:px-7">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#1B5E20]">Access Provisioning</p>
                <h3 id="modalTitle" class="mt-1 text-lg font-extrabold text-slate-900 sm:text-xl">Create Administrator Account</h3>
            </div>
            <button type="button" onclick="closeCreateAdminModal()" aria-label="Close modal"
                    class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.admins.store') }}" method="POST">
            @csrf
            <div class="overflow-y-auto p-5 sm:p-7 space-y-4">
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 mb-1.5">Full Name</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1B5E20]" required placeholder="e.g. Officer Juan">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 mb-1.5">Email Address</label>
                    <input type="email" name="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1B5E20]" required placeholder="admin@livestock.com">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1B5E20]" required placeholder="Minimum 6 characters">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 mb-1.5">Access Role Level</label>
                    <select name="role" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1B5E20]" required>
                        <option value="admin">Standard Admin (Regular panel tasks)</option>
                        <option value="super_admin">Super Admin (Full system control + Admin management)</option>
                    </select>
                </div>
            </div>

            <div class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-7">
                <button type="button" onclick="closeCreateAdminModal()"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-[#1B5E20] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#144c19]">
                    Save Administrator
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateAdminModal() {
        const modal = document.getElementById('createAdminModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateAdminModal() {
        const modal = document.getElementById('createAdminModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateAdminModal();
        }
    });

    document.getElementById('createAdminModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeCreateAdminModal();
        }
    });
</script>
@endpush