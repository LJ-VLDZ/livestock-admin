
@extends('layouts.app')

@section('title', 'Support Tickets - Livestock Express')
@section('header_title', 'User Support Center')
@section('header_subtitle', 'Manage user reports, verification concerns, and inquiries from the Livestock Express mobile app.')

@section('content')
<div class="space-y-6">

    {{-- Page Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    User assistance
                </span>
                <h2 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl">Support center</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/90">
                    Review user inquiries, investigate reports, and keep track of unresolved concerns.
                </p>
            </div>

            <div class="flex h-16 w-16 shrink-0 items-center justify-center self-start rounded-2xl border border-white/15 bg-white/10 sm:self-center">
                <svg class="h-9 w-9 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 10h8M8 14h5m-1 7l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </div>
        </div>
        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/10"></div>
        <div class="absolute -right-2 -top-8 h-40 w-40 rounded-full border border-white/10"></div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <p class="font-bold">Request completed</p>
                <p class="mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Ticket Summary --}}
    @php
        $ticketCollection = collect($tickets ?? []);
        $openTicketsCount = $ticketCollection->filter(function ($ticket) {
            return strtolower(trim((string) ($ticket->status ?? 'Open'))) === 'open';
        })->count();
        $resolvedTicketsCount = $ticketCollection->count() - $openTicketsCount;
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Total tickets</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h8M8 14h5m-1 7l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $ticketCollection->count() }}</p>
            <p class="mt-1 text-xs text-slate-400">Tickets currently displayed</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Open tickets</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $openTicketsCount }}</p>
            <p class="mt-1 text-xs text-slate-400">Waiting for resolution</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Resolved tickets</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $resolvedTicketsCount }}</p>
            <p class="mt-1 text-xs text-slate-400">No longer marked open</p>
        </div>
    </div>

    {{-- Tickets Table --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Incoming reports & inquiries</h3>
                <p class="mt-1 text-sm text-slate-500">Review submitted concerns and update their resolution status.</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ $ticketCollection->count() }} records
            </span>
        </div>

        @if($ticketCollection->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h8M8 14h5m-1 7l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <h4 class="mt-4 text-base font-extrabold text-slate-900">No support tickets yet</h4>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                    User reports and inquiries will appear here when they are submitted through the mobile app.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Category</th>
                            <th class="px-5 py-4">User</th>
                            <th class="px-5 py-4">Subject & message</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Date submitted</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($ticketCollection as $ticket)
                            @php
                                $ticketStatus = strtolower(trim((string) ($ticket->status ?? 'Open')));
                                $isOpen = $ticketStatus === 'open';
                            @endphp
                            <tr class="transition hover:bg-emerald-50/30">
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800">
                                        {{ $ticket->category ?? 'General' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-extrabold text-slate-600">
                                            {{ strtoupper(substr((string) ($ticket->userEmail ?? 'U'), 0, 1)) }}
                                        </div>
                                        <span class="max-w-[190px] break-words text-sm font-semibold text-slate-700">
                                            {{ $ticket->userEmail ?? 'No email provided' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="max-w-sm px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $ticket->subject ?? 'No subject' }}</p>
                                    <p class="mt-1 whitespace-pre-line break-words text-xs leading-5 text-slate-500">
                                        {{ $ticket->message ?? 'No message provided.' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    @if($isOpen)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Open
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ \Illuminate\Support\Str::headline($ticketStatus) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs font-medium text-slate-500">
                                    @if(isset($ticket->createdAt) && $ticket->createdAt)
                                        @php
                                            try {
                                                $submittedAt = $ticket->createdAt instanceof \DateTimeInterface
                                                    ? $ticket->createdAt
                                                    : \Illuminate\Support\Carbon::parse($ticket->createdAt);
                                                $submittedAtText = $submittedAt->format('M d, Y h:i A');
                                            } catch (\Throwable $e) {
                                                $submittedAtText = (string) $ticket->createdAt;
                                            }
                                        @endphp
                                        {{ $submittedAtText }}
                                    @else
                                        Not available
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @if($isOpen)
                                        <form action="{{ route('admin.support.resolve', $ticket->id) }}" method="POST" onsubmit="return confirm('Mark this support ticket as resolved?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0F5132] px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0A3622] focus:outline-none focus:ring-4 focus:ring-emerald-700/20">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Resolve
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Completed
                                        </span>
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
        Support ticket totals reflect the records provided to this page. If your controller uses pagination, these summary counts may represent only the current page.
    </p>
</div>
@endsection
