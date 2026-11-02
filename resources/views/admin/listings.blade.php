@extends('layouts.app')

@section('title', 'Manage Listings | Livestock Express')
@section('header_title', 'Livestock Listing Management')
@section('header_subtitle', 'Review and manage livestock posted by farmers and sellers across Alaminos City.')

@section('content')
@php
    $listingCollection = collect($listings ?? []);
    $totalListings = $listingCollection->count();
    $categoryCount = $listingCollection->pluck('category')->filter()->unique()->count();
    $totalValue = $listingCollection->sum(fn($item) => (float) ($item->price ?? 0));
@endphp

<div class="space-y-6">

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <p class="font-bold">Action completed</p>
                <p class="mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
            <p class="font-bold">Something went wrong</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <span class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Marketplace management
                </span>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Livestock Listings</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/90">
                    Monitor published livestock, review listing information, and remove listings when necessary.
                </p>
            </div>
            <div class="flex h-20 w-20 shrink-0 items-center justify-center self-start rounded-2xl border border-white/15 bg-white/10 sm:self-center">
                <svg class="h-10 w-10 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7v10a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V10z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Total listings</p>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $totalListings }}</p>
            <p class="mt-1 text-xs text-slate-400">Livestock records retrieved</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Livestock categories</p>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $categoryCount }}</p>
            <p class="mt-1 text-xs text-slate-400">Unique categories in listings</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 xl:col-span-1">
            <p class="text-sm font-semibold text-slate-500">Combined asking value</p>
            <p class="mt-3 break-words text-2xl font-extrabold text-slate-900">₱{{ number_format($totalValue, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">Sum of listing prices</p>
        </div>
    </div>

    {{-- Listings Table --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:p-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">All livestock listings</h3>
                <p class="mt-1 text-sm text-slate-500">Search listings and inspect their details.</p>
            </div>

            {{-- Status Filter Tabs --}}
            <div class="flex flex-wrap gap-2 pt-1">
                <button type="button" data-status="all" class="status-tab rounded-xl border border-emerald-600 bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition">All Listings</button>
                <button type="button" data-status="available" class="status-tab rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">Available</button>
                <button type="button" data-status="pending" class="status-tab rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">Pending</button>
                <button type="button" data-status="ordered" class="status-tab rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">Ordered / Sold</button>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 pt-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>
                    <input id="listingSearch" type="search" placeholder="Search livestock or seller..." class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                </div>

                <select id="categoryFilter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                    <option value="">All categories</option>
                    @foreach($listingCollection->pluck('category')->filter()->unique()->sort() as $category)
                        <option value="{{ strtolower($category) }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <p id="listingResultCount" class="text-xs font-medium text-slate-500">Showing {{ $totalListings }} listings</p>
        </div>

        @if($listingCollection->isEmpty())
            <div class="px-6 py-16 text-center">
                <h4 class="mt-4 text-base font-extrabold text-slate-900">No listings found</h4>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Livestock</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Seller</th>
                            <th class="px-5 py-4">Asking price</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="listingTableBody" class="divide-y divide-slate-100">
                        @foreach($listingCollection as $item)
                            @php
                                $listingData = [
                                    'id' => $item->id ?? '',
                                    'name' => $item->livestock_name ?? 'Unnamed livestock',
                                    'category' => $item->category ?? 'Not specified',
                                    'price' => number_format((float) ($item->price ?? 0), 2),
                                    'ownerName' => $item->ownerName ?? 'Unknown seller',
                                    'ownerEmail' => $item->ownerEmail ?? 'No email provided',
                                    'sex' => $item->sex ?? 'Not specified',
                                    'color' => $item->color ?? 'Not specified',
                                    'weight' => $item->weight ?? 'Not specified',
                                    'height' => $item->height ?? 'Not specified',
                                    'description' => $item->description ?? 'No description provided.',
                                    'imageUrl' => $item->imageUrl ?? '',
                                    'imageUrls' => $item->imageUrls ?? [],
                                    'videoUrl' => $item->videoUrl ?? '',
                                    'status' => $item->status ?? 'Not specified',
                                ];

                                $searchText = strtolower(($item->livestock_name ?? '') . ' ' . ($item->category ?? '') . ' ' . ($item->ownerName ?? '') . ' ' . ($item->id ?? ''));
                                $status = strtolower((string) ($item->status ?? 'Not specified'));
                            @endphp
                            <tr class="listing-row transition hover:bg-emerald-50/30" data-search="{{ $searchText }}" data-category="{{ strtolower((string) ($item->category ?? '')) }}" data-status="{{ $status }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                            @if(!empty($item->imageUrl))
                                                <img src="{{ $item->imageUrl }}" class="h-full w-full object-cover" alt="Livestock" loading="lazy">
                                            @else
                                                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.7" d="M12 8c-1.5-2-4-2-5 0m10 0c-1.5-2-4-2-5 0M5 14h14M7 14l-1 4m11-4 1 4M9 18h6"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $item->livestock_name ?? 'Unnamed livestock' }}</p>
                                            <p class="mt-1 text-xs text-slate-400">ID: {{ $item->id ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $item->category ?? 'Not specified' }}</span></td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $item->ownerName ?? 'Unknown seller' }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $item->ownerEmail ?? 'No email provided' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 font-extrabold text-slate-900">₱{{ number_format((float) ($item->price ?? 0), 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">{{ ucfirst($status) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" data-listing="{{ json_encode($listingData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}" class="inspect-listing rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800">Inspect</button>
                                        
                                        {{-- Trigger Removal / Deletion Reason Modal (replaces simple confirm popup) --}}
                                        <button type="button" onclick="openDeletionReasonModal('{{ route('admin.deleteListing', $item->id) }}')" class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="noSearchResults" class="hidden px-6 py-12 text-center">
                <h4 class="font-bold text-slate-900">No matching listings</h4>
            </div>
        @endif
    </div>
</div>

{{-- Deletion / Removal Reason Modal --}}
<div id="deletionReasonModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50">
            <h3 class="text-base font-extrabold text-slate-900">Provide Removal / Rejection Reason</h3>
            <button type="button" onclick="closeDeletionReasonModal()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="deletionReasonForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('DELETE')
            
            <p class="text-xs text-slate-500">Select an automated reason for removing this listing (e.g., accidental approval or policy violation) to notify the seller:</p>
            
            <div class="space-y-2">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-rose-50/30 cursor-pointer transition">
                    <input type="radio" name="rejectionReason" value="Accidental listing approval / verification error by administrator." checked class="mt-0.5 text-rose-600 focus:ring-rose-500" onclick="document.getElementById('customDeletionReason').value = this.value">
                    <span class="text-xs font-semibold text-slate-700">Accidental listing approval / verification error by administrator.</span>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-rose-50/30 cursor-pointer transition">
                    <input type="radio" name="rejectionReason" value="Listing violates marketplace safety guidelines or pricing rules." class="mt-0.5 text-rose-600 focus:ring-rose-500" onclick="document.getElementById('customDeletionReason').value = this.value">
                    <span class="text-xs font-semibold text-slate-700">Listing violates marketplace safety guidelines or pricing rules.</span>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-rose-50/30 cursor-pointer transition">
                    <input type="radio" name="rejectionReason" value="Invalid or unverified ownership certificate credentials." class="mt-0.5 text-rose-600 focus:ring-rose-500" onclick="document.getElementById('customDeletionReason').value = this.value">
                    <span class="text-xs font-semibold text-slate-700">Invalid or unverified ownership certificate credentials.</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Custom Explanation Notes</label>
                <textarea name="rejectionReason" id="customDeletionReason" rows="3" class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-rose-600 focus:ring-2 focus:ring-rose-100 outline-none">Accidental listing approval / verification error by administrator.</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeDeletionReasonModal()" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700">Confirm Deletion & Notify Seller</button>
            </div>
        </form>
    </div>
</div>

{{-- Listing Inspection Modal --}}
<div id="listingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true">
    <!-- Modal content remains identical -->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Search & filter scripts remain intact...
});

function openDeletionReasonModal(actionUrl) {
    document.getElementById('deletionReasonForm').action = actionUrl;
    document.getElementById('deletionReasonModal').classList.remove('hidden');
    document.getElementById('deletionReasonModal').classList.add('flex');
}

function closeDeletionReasonModal() {
    document.getElementById('deletionReasonModal').classList.add('hidden');
    document.getElementById('deletionReasonModal').classList.remove('flex');
}
</script>
@endpush