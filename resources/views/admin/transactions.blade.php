@extends('layouts.app')

@section('title', 'Transactions - Livestock Express')
@section('header_title', 'Transactions Management')
@section('header_subtitle', 'Monitor marketplace sales, transaction statuses, and buyer-seller activity.')

@section('content')
<div class="space-y-6">

    {{-- Page Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Marketplace operations
                </span>
                <h2 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl">Transaction overview</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/90">
                    Track recorded livestock sales and review the current status of each buyer-seller transaction.
                </p>
            </div>
            <div class="flex h-16 w-16 shrink-0 items-center justify-center self-start rounded-2xl border border-white/15 bg-white/10 sm:self-center">
                <svg class="h-9 w-9 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 7h18M5 7l1 13h12l1-13M9 7V4h6v3m-5 5v4m4-4v4"/>
                </svg>
            </div>
        </div>
        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/10"></div>
        <div class="absolute -right-2 -top-8 h-40 w-40 rounded-full border border-white/10"></div>
    </div>

    {{-- Summary Cards --}}
    @php
        $transactionCollection = collect($transactions ?? []);
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Total transactions</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $transactionCollection->count() }}</p>
            <p class="mt-1 text-xs text-slate-400">Records provided to this page</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Completed</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $completedCount ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-400">Completed or delivered sales</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Ongoing</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $pendingCount ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-400">Pending or in progress</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Recorded revenue</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-[#0F5132]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-2 0-3 .9-3 2s1 2 3 2 3 1 3 2-1 2-3 2m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 break-words text-2xl font-extrabold text-slate-900">₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">Total completed sales revenue</p>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">All transactions</h3>
                <p class="mt-1 text-sm text-slate-500">Transaction references, participants, amounts, and statuses.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    {{ $transactionCollection->count() }} records
                </span>

                {{-- Export PDF Report Button --}}
                <a href="{{ route('admin.transactions.export') }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-[#0F5132] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#0A3622]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Report (PDF)
                </a>
            </div>
        </div>

        @if($transactionCollection->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M5 7l1 13h12l1-13M9 7V4h6v3"/>
                    </svg>
                </div>
                <h4 class="mt-4 text-base font-extrabold text-slate-900">No transactions recorded</h4>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                    Transactions will appear here when marketplace sales are recorded.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Transaction</th>
                            <th class="px-5 py-4">Buyer / seller</th>
                            <th class="px-5 py-4">Amount</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transactionCollection as $txn)
                            @php
                                $transactionStatus = trim((string) ($txn->orderStatus ?? $txn->status ?? 'Unknown'));
                                $normalizedStatus = strtolower($transactionStatus);

                                $statusClasses = match (true) {
                                    in_array($normalizedStatus, ['completed', 'delivered', 'paid']) => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                    in_array($normalizedStatus, ['pending verification & dispatch', 'city veterinary inspection approved', 'dispatched', 'pending', 'pending online inspection review']) => 'border-amber-200 bg-amber-50 text-amber-800',
                                    in_array($normalizedStatus, ['cancelled by buyer', 'cancelled', 'canceled', 'rejected', 'failed']) => 'border-rose-200 bg-rose-50 text-rose-800',
                                    default => 'border-slate-200 bg-slate-50 text-slate-700',
                                };
                            @endphp

                            <tr class="transition hover:bg-emerald-50/30">
                                <td class="px-6 py-4">
                                    <p class="font-mono text-sm font-bold text-slate-800">#{{ substr($txn->id ?? ($txn->orderId ?? 'UNKNOWN'), 0, 8) }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">{{ $txn->livestockName ?? 'Livestock Item' }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $txn->buyerName ?? 'Buyer not specified' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Seller: <span class="font-semibold">{{ $txn->sellerName ?? 'Not specified' }}</span>
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="font-extrabold text-[#0F5132]">
                                        ₱{{ number_format((float) ($txn->totalAmount ?? $txn->amount ?? 0), 2) }}
                                    </p>
                                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        {{ $txn->paymentMethod ?? 'COD' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $statusClasses }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $transactionStatus }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-xs font-medium text-slate-500">
                                    @if(!empty($txn->createdAt))
                                        @php
                                            try {
                                                $transactionDate = $txn->createdAt instanceof \DateTimeInterface
                                                    ? \Illuminate\Support\Carbon::instance($txn->createdAt)->setTimezone('Asia/Manila')
                                                    : \Illuminate\Support\Carbon::parse($txn->createdAt)->setTimezone('Asia/Manila');
                                                $transactionDateText = $transactionDate->format('M d, Y h:i A');
                                            } catch (\Throwable $e) {
                                                $transactionDateText = 'Date unavailable';
                                            }
                                        @endphp
                                        {{ $transactionDateText }}
                                    @else
                                        Date unavailable
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <p class="px-1 text-xs leading-5 text-slate-400">
        Summary totals are based on marketplace transactions and fulfilled order records.
    </p>
</div>
@endsection