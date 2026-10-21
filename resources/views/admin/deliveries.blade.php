
@extends('layouts.app')

@section('title', 'Deliveries | Livestock Express')
@section('header_title', 'Delivery Management')
@section('header_subtitle', 'Monitor livestock shipments and delivery progress across Alaminos City.')

@section('content')
@php
    // Convert the controller's array into a Laravel Collection.
    $deliveryList = collect($deliveries ?? []);

    // Support both the current status field and Firebase orderStatus.
    $getStatus = function ($delivery) {
        $status = $delivery->orderStatus
            ?? $delivery->status
            ?? 'Pending Verification & Dispatch';

        return strtolower(trim((string) $status));
    };

    $pendingCount = $deliveryList->filter(function ($delivery) use ($getStatus) {
        $status = $getStatus($delivery);

        return in_array($status, [
            'pending',
            'pending verification & dispatch',
            'pending verification',
            'pending dispatch',
        ], true);
    })->count();

    $dispatchedCount = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), [
            'dispatched',
            'in transit',
            'in_transit',
        ], true);
    })->count();

    $deliveredCount = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return $getStatus($delivery) === 'delivered';
    })->count();
@endphp

<div class="space-y-6">

    {{-- Page Introduction --}}
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#0A3622] to-[#1B5E35] p-6 text-white shadow-sm sm:p-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">
                    Livestock Express Operations
                </p>
                <h3 class="mt-2 text-2xl font-extrabold">Delivery Tracking</h3>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100">
                    Review delivery requests, monitor shipment progress, and keep livestock movement records organized.
                </p>
            </div>

            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v12H3zM14 10h4l3 3v5h-7zM7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm11 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                </svg>
            </div>
        </div>
    </section>

    {{-- Delivery Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Total Deliveries</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <h3 class="text-3xl font-extrabold text-slate-900">{{ $deliveryList->count() }}</h3>
                <span class="rounded-xl bg-emerald-50 p-3 text-emerald-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v12H3zM14 10h4l3 3v5h-7z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-500">All order records returned</p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Pending</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <h3 class="text-3xl font-extrabold text-amber-600">{{ $pendingCount }}</h3>
                <span class="rounded-xl bg-amber-50 p-3 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        <path stroke-width="2" stroke-linecap="round" d="M12 7v5l3 2"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-500">Awaiting dispatch or verification</p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Dispatched</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <h3 class="text-3xl font-extrabold text-blue-600">{{ $dispatchedCount }}</h3>
                <span class="rounded-xl bg-blue-50 p-3 text-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-500">Shipment in progress</p>
        </div>

        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Delivered</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <h3 class="text-3xl font-extrabold text-emerald-700">{{ $deliveredCount }}</h3>
                <span class="rounded-xl bg-emerald-50 p-3 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-500">Marked as completed</p>
        </div>

    </div>

    {{-- Delivery Records --}}
    <section class="admin-card overflow-hidden">

        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Shipment Records</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Review the status and destination of each delivery.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                <span class="text-xs font-bold text-slate-600">
                    {{ $deliveryList->count() }} records
                </span>
            </div>
        </div>

        {{-- Search and Status Filter --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke-width="2"/>
                    <path stroke-width="2" stroke-linecap="round" d="m16 16 4 4"/>
                </svg>
                <input
                    id="deliverySearch"
                    type="search"
                    placeholder="Search delivery ID, livestock, or destination..."
                    class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    aria-label="Search delivery records">
            </div>

            <select
                id="deliveryStatusFilter"
                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                aria-label="Filter deliveries by status">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="dispatched">Dispatched</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled / Rejected</option>
            </select>
        </div>

        @if($deliveryList->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-800">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v12H3zM14 10h4l3 3v5h-7z"/>
                    </svg>
                </div>
                <h4 class="mt-4 text-base font-bold text-slate-900">No delivery records yet</h4>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                    Delivery requests will appear here when corresponding records are created in the system.
                </p>
            </div>
        @else
            <div class="admin-scrollbar overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Delivery ID</th>
                            <th class="px-5 py-4">Livestock / Order</th>
                            <th class="px-5 py-4">Destination</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>

                    <tbody id="deliveryTableBody" class="divide-y divide-slate-100 text-sm">
                        @foreach($deliveryList as $deliv)
                            @php
                                $status = $getStatus($deliv);

                                $isPending = in_array($status, [
                                    'pending',
                                    'pending verification & dispatch',
                                    'pending verification',
                                    'pending dispatch',
                                ], true);

                                $isDispatched = in_array($status, [
                                    'dispatched',
                                    'in transit',
                                    'in_transit',
                                ], true);

                                $isCancelled = in_array($status, [
                                    'cancelled',
                                    'canceled',
                                    'rejected',
                                ], true);

                                $filterStatus = $isPending
                                    ? 'pending'
                                    : ($isDispatched
                                        ? 'dispatched'
                                        : ($status === 'delivered'
                                            ? 'delivered'
                                            : ($isCancelled ? 'cancelled' : $status)));

                                $statusLabel = match ($filterStatus) {
                                    'pending' => 'Pending',
                                    'dispatched' => 'Dispatched',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled / Rejected',
                                    default => ucwords(str_replace(['_', '-'], ' ', $status)),
                                };

                                $statusClasses = match ($filterStatus) {
                                    'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                    'dispatched' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
                                    default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                };

                                $deliveryId = $deliv->id ?? 'N/A';
                                $livestockName = $deliv->livestock_name
                                    ?? $deliv->livestockName
                                    ?? $deliv->animalName
                                    ?? 'Livestock Order';

                                $destination = $deliv->destination
                                    ?? $deliv->deliveryAddress
                                    ?? $deliv->dropOffAddress
                                    ?? 'Not specified';
                            @endphp

                            <tr
                                class="delivery-row transition-colors hover:bg-emerald-50/30"
                                data-search="{{ strtolower($deliveryId . ' ' . $livestockName . ' ' . $destination) }}"
                                data-status="{{ $filterStatus }}">

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="font-mono text-xs font-bold text-slate-600">
                                        #{{ $deliveryId }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $livestockName }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Livestock delivery</p>
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{ $destination }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold ring-1 ring-inset {{ $statusClasses }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach

                        <tr id="deliveryNoResults" class="hidden">
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">
                                No deliveries match your search or selected status.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <p class="text-xs leading-5 text-slate-500">
        Delivery statuses shown here reflect the order records supplied by Firebase. This page currently displays status information; changing a shipment's status requires a connected update action.
    </p>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('deliverySearch');
    const statusFilter = document.getElementById('deliveryStatusFilter');
    const rows = Array.from(document.querySelectorAll('.delivery-row'));
    const noResults = document.getElementById('deliveryNoResults');

    function filterDeliveries() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        const selectedStatus = (statusFilter?.value || '').toLowerCase();
        let visibleCount = 0;

        rows.forEach(function (row) {
            const searchableText = row.dataset.search || '';
            const rowStatus = row.dataset.status || '';

            const matchesSearch = searchableText.includes(query);
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;
            const visible = matchesSearch && matchesStatus;

            row.classList.toggle('hidden', !visible);

            if (visible) {
                visibleCount++;
            }
        });

        if (noResults) {
            noResults.classList.toggle(
                'hidden',
                visibleCount > 0 || rows.length === 0
            );
        }
    }

    searchInput?.addEventListener('input', filterDeliveries);
    statusFilter?.addEventListener('change', filterDeliveries);
});
</script>
@endpush
