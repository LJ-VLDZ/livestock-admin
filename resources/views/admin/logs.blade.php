@extends('layouts.app')

@section('title', 'Activity Logs - Livestock Express')
@section('header_title', 'Activity Logs')
@section('header_subtitle', 'Review administrative actions and system activity records.')

@section('content')
<div class="space-y-6">

    {{-- Page Introduction --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Administration</span>
                <span>/</span>
                <span class="font-semibold text-[#0F5132]">Activity Logs</span>
            </div>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                System Activity
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Review recorded actions for accountability and monitoring.
            </p>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-[#0F5132] shadow-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 12h6m-6 4h6" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">
                    Total records
                </p>
                <p class="text-xl font-extrabold leading-tight text-[#0A3622]">
                    {{ number_format($activityLogs->total()) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Information Banner --}}
    <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a2 2 0 0 0-3.4 0Z" />
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800">Audit record overview</h3>
            <p class="mt-1 text-xs leading-5 text-slate-500">
                This page displays the activity records currently supplied by the system.
                The information shown depends on what your application saves in its activity log records.
            </p>
        </div>
    </div>

    {{-- Activity Table --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Recorded Activities</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Administrative and user actions, timestamps, and associated target IDs.
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Activity records
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-500">
                        <th class="px-6 py-4">Date and time</th>
                        <th class="px-6 py-4">Initiator</th>
                        <th class="px-6 py-4">Action performed</th>
                        <th class="px-6 py-4">Target ID</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($activityLogs as $log)
                        <tr class="transition-colors hover:bg-emerald-50/30">

                            {{-- Timestamp --}}
                            <td class="whitespace-nowrap px-6 py-4">
                                @if(property_exists($log, 'timestamp') && !empty($log->timestamp))
                                    @php
                                        try {
                                            $displayTimestamp = \Carbon\Carbon::parse($log->timestamp)->setTimezone('Asia/Manila');
                                        } catch (\Throwable $e) {
                                            $displayTimestamp = null;
                                        }
                                    @endphp
                                    
                                    @if($displayTimestamp)
                                        <p class="text-sm font-bold text-slate-800">
                                            {{ $displayTimestamp->format('M d, Y') }}
                                        </p>
                                        <p class="mt-1 text-xs font-medium text-slate-400">
                                            {{ $displayTimestamp->format('h:i:s A') }} PHT
                                        </p>
                                    @else
                                        <span class="text-sm text-slate-400">Invalid timestamp</span>
                                    @endif
                                @else
                                    <span class="text-sm text-slate-400">N/A</span>
                                @endif
                            </td>

                            {{-- Initiator --}}
                            <td class="px-6 py-4">
                                @php
                                    // Safely extract properties to prevent stdClass undefined errors
                                    $actorType = property_exists($log, 'actorType') ? $log->actorType : (property_exists($log, 'actor_type') ? $log->actor_type : '');
                                    $adminName = property_exists($log, 'admin_name') ? $log->admin_name : null;
                                    $adminEmail = property_exists($log, 'admin_email') ? $log->admin_email : null;
                                    $userName = property_exists($log, 'user_name') ? $log->user_name : null;
                                    $userEmail = property_exists($log, 'user_email') ? $log->user_email : null;

                                    $isAdminActivity = in_array(strtolower($actorType), ['admin', 'administrator'], true)
                                        || ($actorType === '' && (!empty($adminEmail) || !empty($adminName)));

                                    if ($isAdminActivity) {
                                        $initiator = !empty($adminName) ? $adminName : (!empty($adminEmail) ? $adminEmail : 'City Veterinary Office');
                                        $roleLabel = 'Administrator';
                                    } else {
                                        // Display the real user name resolved from the controller
                                        $initiator = !empty($userName) ? $userName : (!empty($userEmail) ? $userEmail : 'Registered User');
                                        $roleLabel = 'User';
                                    }

                                    $initial = strtoupper(substr(trim($initiator), 0, 1)) ?: '?';
                                @endphp

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-extrabold text-[#0F5132]">
                                        {{ $initial }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="max-w-[220px] truncate text-sm font-bold text-slate-800">
                                            {{ $initiator }}
                                        </p>
                                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                            {{ $roleLabel }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Action --}}
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2.5">
                                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                        </svg>
                                    </span>
                                    <span class="pt-1 text-sm font-semibold leading-5 text-slate-700">
                                        {{ property_exists($log, 'action') ? $log->action : 'Unknown Action' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Target --}}
                            <td class="px-6 py-4">
                                @php
                                    $targetId = null;
                                    if (is_object($log)) {
                                        $targetId = $log->target_display_id 
                                            ?? $log->target_user_id 
                                            ?? $log->user_id 
                                            ?? $log->buyerId 
                                            ?? $log->sellerId 
                                            ?? $log->causer_id 
                                            ?? null;
                                    } elseif (is_array($log)) {
                                        $targetId = $log['target_display_id'] 
                                            ?? $log['target_user_id'] 
                                            ?? $log['user_id'] 
                                            ?? $log['buyerId'] 
                                            ?? $log['sellerId'] 
                                            ?? $log['causer_id'] 
                                            ?? null;
                                    }
                                @endphp

                                @if(!empty($targetId) && $targetId !== 'N/A')
                                    <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 font-mono text-xs font-semibold text-slate-600" title="{{ $targetId }}">
                                        {{ Str::length($targetId) > 10 ? '#' . Str::substr($targetId, 0, 8) . '...' : $targetId }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">No target ID</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 12h6m-6 4h6" />
                                    </svg>
                                </div>
                                <h4 class="mt-4 text-sm font-extrabold text-slate-800">
                                    No activity records found
                                </h4>
                                <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-slate-500">
                                    Activity records will appear here when they are available in the database.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($activityLogs->hasPages())
            <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Showing
                    <span class="font-bold text-slate-700">{{ $activityLogs->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-bold text-slate-700">{{ $activityLogs->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-bold text-slate-700">{{ $activityLogs->total() }}</span>
                    records
                </p>

                <div class="text-sm">
                    {{ $activityLogs->links('pagination::tailwind') }}
                </div>
            </div>
        @endif

    </section>
</div>
@endsection