@extends('layouts.app')

@section('title', 'Manage Users - Livestock Express')
@section('header_title', 'User Verification & Directory')
@section('header_subtitle', 'Verify user identities and manage trusted marketplace accounts.')

@section('content')
@php
    $pendingCollection = collect($pendingUsers ?? [])->filter(function($user) {
        // Only include users who have actually submitted ID images or a valid ID type
        return !empty($user->idFrontUrl) || !empty($user->idUrl) || (!empty($user->idType) && $user->idType !== 'N/A' && $user->idType !== '');
    });
    $verifiedCollection = collect($verifiedUsers ?? []);
    $unverifiedCollection = collect($unverifiedUsers ?? []);
    $rejectedCollection = collect($rejectedUsers ?? []);
@endphp

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

    {{-- Page introduction --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.5-1a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.4-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                    </svg>
                    Account Verification Center
                </div>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                    Manage Marketplace Users
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-50/85">
                    Review submitted identification documents and facial verification assets, approve eligible accounts, and manage existing status.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:min-w-[270px]">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold text-emerald-100">Awaiting Review</p>
                    <p class="mt-2 text-3xl font-extrabold">{{ $pendingCollection->count() }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold text-emerald-100">Verified Users</p>
                    <p class="mt-2 text-3xl font-extrabold">{{ $verifiedCollection->count() }}</p>
                </div>
            </div>
        </div>

        <div class="pointer-events-none absolute -right-12 -top-20 h-64 w-64 rounded-full border-[35px] border-white/5"></div>
        <div class="pointer-events-none absolute -bottom-28 right-40 h-56 w-56 rounded-full bg-white/5"></div>
    </div>

    {{-- Verification queue --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Verification Queue</h2>
                    <p class="mt-1 text-xs text-slate-500">Seller accounts waiting for identity & facial clearance</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                {{ $pendingCollection->count() }} Pending Review
            </span>
        </div>

        @if($pendingCollection->isEmpty())
            <div class="px-6 py-14 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">All caught up!</h3>
                <p class="mt-1 text-sm text-slate-500">There are no pending user verifications at the moment.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($pendingCollection as $user)
                    <div class="flex flex-col gap-5 p-5 transition hover:bg-slate-50/70 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex min-w-0 items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-extrabold uppercase text-[#1B5E20]">
                                {{ strtoupper(substr($user->name ?? $user->email ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="break-words text-sm font-extrabold text-slate-900">
                                        {{ $user->name ?? $user->email ?? 'Seller Applicant' }}
                                    </h3>
                                    <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                                        Pending
                                    </span>
                                </div>
                                <p class="mt-1 break-all text-xs text-slate-500">{{ $user->email ?? 'No email provided' }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $user->idType ?? 'ID type not specified' }}
                                    </span>
                                    @if(!empty($user->barangay) || !empty($user->city))
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ collect([$user->barangay ?? null, $user->city ?? null])->filter()->implode(', ') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 lg:shrink-0">
                            <button
                                type="button"
                                data-user="{{ json_encode([
                                    'id' => $user->id,
                                    'name' => $user->name ?? 'N/A',
                                    'email' => $user->email ?? 'N/A',
                                    'phone' => $user->phone ?? 'N/A',
                                    'barangay' => $user->barangay ?? '',
                                    'city' => $user->city ?? '',
                                    'front' => $user->idFrontUrl ?? '',
                                    'back' => $user->idBackUrl ?? '',
                                    'face' => $user->faceUrl ?? '',
                                    'idType' => $user->idType ?? 'N/A',
                                    'status' => 'pending'
                                ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
                                onclick="openUserModal(this)"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-[#1B5E20]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"/>
                                </svg>
                                Inspect Documents
                            </button>

                            <form action="{{ route('admin.update', $user->id) }}" method="POST" onsubmit="return confirm('Approve this user verification?');">
                                @csrf
                                <input type="hidden" name="status" value="verified">
                                <button type="submit" class="rounded-xl bg-[#1B5E20] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#144c19]">
                                    Approve
                                </button>
                            </form>

                            <form action="{{ route('admin.update', $user->id) }}" method="POST" onsubmit="return confirm('Reject this verification application?');">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Rejected Applications Section --}}
    @if($rejectedCollection->isNotEmpty())
    <section class="overflow-hidden rounded-3xl border border-rose-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-rose-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 bg-rose-50/30">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Rejected Applications</h2>
                    <p class="mt-1 text-xs text-slate-500">Accounts whose ID verifications were denied</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-800">
                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                {{ $rejectedCollection->count() }} Rejected
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($rejectedCollection as $rUser)
                <div class="flex flex-col gap-5 p-5 transition hover:bg-slate-50/70 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-sm font-extrabold uppercase text-rose-700">
                            {{ strtoupper(substr($rUser->name ?? $rUser->email ?? 'U', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="break-words text-sm font-extrabold text-slate-900">
                                    {{ $rUser->name ?? $rUser->email ?? 'User' }}
                                </h3>
                                <span class="rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-rose-700">
                                    Rejected
                                </span>
                            </div>
                            <p class="mt-1 break-all text-xs text-slate-500">{{ $rUser->email ?? 'No email provided' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 lg:shrink-0">
                        <form action="{{ route('admin.update', $rUser->id) }}" method="POST" onsubmit="return confirm('Move this user back to pending review?');">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                Re-Review Application
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Verified users directory --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-[#1B5E20]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Verified Users Directory</h2>
                    <p class="mt-1 text-xs text-slate-500">Manage accounts with approved identity verification</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                {{ $verifiedCollection->count() }} Verified Accounts
            </span>
        </div>

        @if($verifiedCollection->isEmpty())
            <div class="px-6 py-14 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">No verified users yet</h3>
                <p class="mt-1 text-sm text-slate-500">Approved accounts will appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left">
                    <thead class="bg-slate-50">
                        <tr class="border-y border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">User Details</th>
                            <th class="px-6 py-4">Identification</th>
                            <th class="px-6 py-4">Verification Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($verifiedCollection as $vUser)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-extrabold uppercase text-[#1B5E20]">
                                            {{ strtoupper(substr($vUser->name ?? $vUser->email ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="break-words text-sm font-bold text-slate-900">{{ $vUser->name ?? 'N/A' }}</p>
                                            <p class="mt-1 break-all text-xs text-slate-500">{{ $vUser->email ?? 'No email provided' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600">
                                        {{ $vUser->idType ?? 'Standard ID' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Verified
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            data-user="{{ json_encode([
                                                'id' => $vUser->id,
                                                'name' => $vUser->name ?? 'N/A',
                                                'email' => $vUser->email ?? 'N/A',
                                                'phone' => $vUser->phone ?? 'N/A',
                                                'barangay' => $vUser->barangay ?? '',
                                                'city' => $vUser->city ?? '',
                                                'front' => $vUser->idFrontUrl ?? '',
                                                'back' => $vUser->idBackUrl ?? '',
                                                'face' => $vUser->faceUrl ?? '',
                                                'idType' => $vUser->idType ?? 'N/A',
                                                'status' => 'verified'
                                            ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
                                            onclick="openUserModal(this)"
                                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-[#1B5E20]">
                                            Manage
                                        </button>

                                        <form action="{{ route('admin.update', $vUser->id) }}" method="POST" onsubmit="return confirm('Revoke verification status for this user?');">
                                            @csrf
                                            <input type="hidden" name="status" value="unverified">
                                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                Revoke Access
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Unverified Users Section --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Unverified Accounts</h2>
                    <p class="mt-1 text-xs text-slate-500">Users who have registered but have not submitted ID documents</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">
                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                {{ $unverifiedCollection->count() }} Unverified Accounts
            </span>
        </div>

        @if($unverifiedCollection->isEmpty())
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-slate-500">No unverified accounts found.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($unverifiedCollection as $uUser)
                    <div class="flex flex-col gap-5 p-5 transition hover:bg-slate-50/70 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex min-w-0 items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-sm font-extrabold uppercase text-slate-600">
                                {{ strtoupper(substr($uUser->name ?? $uUser->email ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="break-words text-sm font-extrabold text-slate-900">
                                        {{ $uUser->name ?? $uUser->email ?? 'Standard User' }}
                                    </h3>
                                    <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-600">
                                        Unverified
                                    </span>
                                </div>
                                <p class="mt-1 break-all text-xs text-slate-500">{{ $uUser->email ?? 'No email provided' }}</p>
                            </div>
                        </div>

                        <div class="text-xs font-semibold text-slate-400">
                            Awaiting ID Submission
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

{{-- User details modal --}}
<div id="detailsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-white px-5 py-4 sm:px-7">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#1B5E20]">Account Review</p>
                <h3 id="modalTitle" class="mt-1 text-lg font-extrabold text-slate-900 sm:text-xl">User Verification Details</h3>
            </div>
            <button type="button" onclick="closeUserModal()" aria-label="Close modal"
                    class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5 sm:p-7">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#0A3622] p-4 text-white">
                <div>
                    <p class="text-xs font-medium text-emerald-100">Account holder</p>
                    <p id="modalName" class="mt-1 break-words text-lg font-extrabold">-</p>
                    <p id="modalEmail" class="mt-1 break-all text-xs text-emerald-100">-</p>
                </div>
                <span id="modalStatus" class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold">
                    Account Status
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">ID Type</p>
                    <p id="modalIdType" class="mt-2 break-words text-sm font-bold text-[#1B5E20]">-</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Phone Number</p>
                    <p id="modalPhone" class="mt-2 break-words text-sm font-bold text-slate-800">-</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 sm:col-span-2">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Barangay / City</p>
                    <p id="modalLocation" class="mt-2 break-words text-sm font-bold text-slate-800">-</p>
                </div>
            </div>

            <div class="mt-7">
                <div class="mb-4">
                    <h4 class="text-sm font-extrabold text-slate-900">Submitted Identification & Facial Assets</h4>
                    <p class="mt-1 text-xs text-slate-500">Select an image to inspect details in a separate tab.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 p-3">
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-wider text-slate-500">Front ID</p>
                        <a id="modalFrontLink" href="#" target="_blank" rel="noopener noreferrer" class="hidden">
                            <img id="modalFrontImg" src="" alt="Front side of submitted ID" class="h-44 w-full rounded-xl border border-slate-200 object-cover">
                        </a>
                        <p id="modalNoFrontText" class="flex h-44 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 text-center text-xs font-semibold text-slate-400">
                            No front ID available
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-3">
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-wider text-slate-500">Back ID</p>
                        <a id="modalBackLink" href="#" target="_blank" rel="noopener noreferrer" class="hidden">
                            <img id="modalBackImg" src="" alt="Back side of submitted ID" class="h-44 w-full rounded-xl border border-slate-200 object-cover">
                        </a>
                        <p id="modalNoBackText" class="flex h-44 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 text-center text-xs font-semibold text-slate-400">
                            No back ID available
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-3">
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-wider text-slate-500">Facial Selfie</p>
                        <a id="modalFaceLink" href="#" target="_blank" rel="noopener noreferrer" class="hidden">
                            <img id="modalFaceImg" src="" alt="Facial verification selfie" class="h-44 w-full rounded-xl border border-slate-200 object-cover">
                        </a>
                        <p id="modalNoFaceText" class="flex h-44 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 text-center text-xs font-semibold text-slate-400">
                            No selfie available
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <button type="button" onclick="closeUserModal()"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                Close
            </button>

            <div id="modalActions" class="flex flex-col gap-2 sm:flex-row">
                <form id="modalRejectForm" action="" method="POST" onsubmit="return confirm('Revoke verification status for this user?');">
                    @csrf
                    <input type="hidden" name="status" value="unverified">
                    <button type="submit" class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                        Revoke Verification
                    </button>
                </form>
                <form id="modalApproveForm" action="" method="POST" onsubmit="return confirm('Approve this user verification?');">
                    @csrf
                    <input type="hidden" name="status" value="verified">
                    <button type="submit" class="w-full rounded-xl bg-[#1B5E20] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#144c19]">
                        Approve User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openUserModal(button) {
        let user;

        try {
            user = JSON.parse(button.dataset.user);
        } catch (error) {
            console.error('Unable to read user details.', error);
            alert('Unable to open user details. Please refresh the page and try again.');
            return;
        }

        const setText = (id, value) => {
            document.getElementById(id).textContent = value || 'N/A';
        };

        setText('modalName', user.name);
        setText('modalEmail', user.email);
        setText('modalPhone', user.phone);
        setText('modalIdType', user.idType);
        setText('modalLocation', [user.barangay, user.city].filter(Boolean).join(', '));

        const statusElement = document.getElementById('modalStatus');
        const isVerified = user.status === 'verified';

        statusElement.textContent = isVerified ? 'Verified Account' : 'Pending Verification';
        statusElement.className = isVerified
            ? 'rounded-full border border-emerald-200 bg-emerald-400/20 px-3 py-1.5 text-xs font-bold text-emerald-50'
            : 'rounded-full border border-amber-200 bg-amber-400/20 px-3 py-1.5 text-xs font-bold text-amber-50';

        const setImage = (prefix, url) => {
            const image = document.getElementById('modal' + prefix + 'Img');
            const link = document.getElementById('modal' + prefix + 'Link');
            const emptyText = document.getElementById('modalNo' + prefix + 'Text');

            image.onerror = () => {
                image.removeAttribute('src');
                link.classList.add('hidden');
                emptyText.textContent = 'Unable to load image.';
                emptyText.classList.remove('hidden');
            };

            if (url && url.trim() !== '') {
                emptyText.textContent = 'No ' + prefix.toLowerCase() + ' available';
                image.src = url;
                link.href = url;
                link.classList.remove('hidden');
                emptyText.classList.add('hidden');
            } else {
                image.removeAttribute('src');
                link.removeAttribute('href');
                link.classList.add('hidden');
                emptyText.textContent = 'No ' + prefix.toLowerCase() + ' available';
                emptyText.classList.remove('hidden');
            }
        };

        setImage('Front', user.front);
        setImage('Back', user.back);
        setImage('Face', user.face); // Handles facial verification selfie preview

        const updateRouteBase = @json(url('/admin/verifications'));
        const actionUrl = updateRouteBase + '/' + encodeURIComponent(user.id);

        document.getElementById('modalApproveForm').action = actionUrl;
        document.getElementById('modalRejectForm').action = actionUrl;

        document.getElementById('modalApproveForm').classList.toggle('hidden', isVerified);
        document.getElementById('modalRejectForm').classList.toggle('hidden', !isVerified);

        const modal = document.getElementById('detailsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeUserModal() {
        const modal = document.getElementById('detailsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeUserModal();
        }
    });

    document.getElementById('detailsModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeUserModal();
        }
    });
</script>
@endpush