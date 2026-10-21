
@extends('layouts.app')

@section('title', 'Payment Monitoring - Livestock Express')
@section('header_title', 'Payment Monitoring')
@section('header_subtitle', 'Review payment records and monitor marketplace settlements.')

@section('content')
<div class="space-y-6">

    {{-- Page Heading --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Marketplace</span>
                <span>/</span>
                <span class="font-semibold text-[#0F5132]">Payment Monitoring</span>
            </div>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                Payment Records
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Monitor recorded payment methods, amounts, and settlement statuses.
            </p>
        </div>

        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-[#0F5132]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm0 9h5" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Total records
                </p>
                <p class="text-xl font-extrabold leading-tight text-slate-900">
                    {{ is_countable($payments) ? number_format(count($payments)) : '0' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Payment Information --}}
    <div class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#0F5132] shadow-sm">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a2 2 0 0 0-3.4 0Z" />
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-bold text-[#0A3622]">Payment verification</h3>
            <p class="mt-1 text-xs leading-5 text-emerald-900/75">
                These records reflect the data provided by the application. Confirm payment status against your actual transaction records before treating a payment as settled.
            </p>
        </div>
    </div>

    {{-- Payment Table --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">
                    All Payments
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    Payment references and recorded settlement information.
                </p>
            </div>

            <span class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Payment register
            </span>
        </div>

        @if(empty($payments) || (is_countable($payments) && count($payments) === 0))
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-slate-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                            d="M2 8h20M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm2 10h5" />
                    </svg>
                </div>
                <h4 class="mt-4 text-sm font-extrabold text-slate-800">
                    No payment records yet
                </h4>
                <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-slate-500">
                    Payment records will appear here when payment data is available in the system.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-500">
                            <th class="px-6 py-4">Reference ID</th>
                            <th class="px-6 py-4">Payer</th>
                            <th class="px-6 py-4">Payment method</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4 text-right">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($payments as $pay)
                            @php
                                $paymentStatus = strtolower(trim((string) ($pay->status ?? 'unknown')));

                                $statusClasses = match($paymentStatus) {
                                    'paid', 'completed', 'verified' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'pending', 'processing' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'failed', 'rejected', 'cancelled', 'canceled' => 'border-red-200 bg-red-50 text-red-700',
                                    default => 'border-slate-200 bg-slate-50 text-slate-600',
                                };

                                $statusLabel = $paymentStatus === 'unknown'
                                    ? 'Unspecified'
                                    : \Illuminate\Support\Str::headline($paymentStatus);
                            @endphp

                            <tr class="transition-colors hover:bg-emerald-50/30">

                                {{-- Reference --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 font-mono text-xs font-bold text-slate-700">
                                        #{{ $pay->id ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- Payer --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-extrabold text-[#0F5132]">
                                            {{ strtoupper(substr($pay->buyerName ?? 'B', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="max-w-[200px] truncate text-sm font-bold text-slate-800">
                                                {{ $pay->buyerName ?? 'Unknown Buyer' }}
                                            </p>
                                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                                Buyer
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Method --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm0 9h5" />
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-slate-700">
                                            {{ $pay->paymentMethod ?? 'Not specified' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Amount --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="text-sm font-extrabold text-[#0F5132]">
                                        ₱{{ number_format((float) ($pay->amount ?? 0), 2) }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-bold {{ $statusClasses }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-5 py-3">
            <p class="text-xs text-slate-500">
                Displaying available payment records
            </p>
            <p class="text-xs font-semibold text-slate-600">
                Livestock Express
            </p>
        </div>
    </section>
</div>
@endsection
