
@extends('layouts.app')

@section('title', 'Manage Listings | Livestock Express')
@section('header_title', 'Livestock Listing Management')
@section('header_subtitle', 'Review and manage livestock posted by farmers and sellers across Alaminos City.')

@section('content')
@php
    // Convert the controller's array into a Laravel Collection.
    $listingCollection = collect($listings ?? []);

    $totalListings = $listingCollection->count();

    $categoryCount = $listingCollection
        ->pluck('category')
        ->filter()
        ->unique()
        ->count();

    $totalValue = $listingCollection->sum(function ($item) {
        return (float) ($item->price ?? 0);
    });
@endphp

<div class="space-y-6">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <p class="font-bold">Action completed</p>
                <p class="mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Error Messages --}}
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

                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                    Livestock Listings
                </h2>

                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/90">
                    Monitor published livestock, review listing information, and remove listings when necessary.
                </p>
            </div>

            <div class="flex h-20 w-20 shrink-0 items-center justify-center self-start rounded-2xl border border-white/15 bg-white/10 sm:self-center">
                <svg class="h-10 w-10 text-emerald-100" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="1.6"
                          d="M3 10l9-7 9 7v10a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V10z"/>
                </svg>
            </div>
        </div>

        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/10"></div>
        <div class="absolute -right-2 -top-8 h-40 w-40 rounded-full border border-white/10"></div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Total listings</p>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">
                {{ $totalListings }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Livestock records retrieved</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Livestock categories</p>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v6H4zM14 15h6v6h-6z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">
                {{ $categoryCount }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Unique categories in the listings</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">Combined asking value</p>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M12 8c-1.5 0-3 .7-3 2s1.5 2 3 2 3 1 3 2-1.5 2-3 2m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 break-words text-2xl font-extrabold text-slate-900">
                ₱{{ number_format($totalValue, 2) }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Sum of listing prices, not sales revenue</p>
        </div>

    </div>

    {{-- Listings Table --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:p-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">All livestock listings</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Search listings and inspect their details.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                    </svg>

                    <input
                        id="listingSearch"
                        type="search"
                        placeholder="Search livestock or seller..."
                        class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                </div>

                <select
                    id="categoryFilter"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">All categories</option>
                    @foreach($listingCollection->pluck('category')->filter()->unique()->sort() as $category)
                        <option value="{{ strtolower($category) }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <p id="listingResultCount" class="text-xs font-medium text-slate-500">
                Showing {{ $totalListings }} listings
            </p>
        </div>

        @if($listingCollection->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </div>
                <h4 class="mt-4 text-base font-extrabold text-slate-900">
                    No listings found
                </h4>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                    There are currently no livestock records to display.
                </p>
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
                                    'status' => $item->status ?? 'Not specified',
                                ];

                                $searchText = strtolower(
                                    ($item->livestock_name ?? '') . ' ' .
                                    ($item->category ?? '') . ' ' .
                                    ($item->ownerName ?? '') . ' ' .
                                    ($item->ownerEmail ?? '') . ' ' .
                                    ($item->id ?? '')
                                );

                                $status = strtolower((string) ($item->status ?? 'Not specified'));
                            @endphp

                            <tr
                                class="listing-row transition hover:bg-emerald-50/30"
                                data-search="{{ $searchText }}"
                                data-category="{{ strtolower((string) ($item->category ?? '')) }}"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                            @if(!empty($item->imageUrl))
                                                <img
                                                    src="{{ $item->imageUrl }}"
                                                    class="h-full w-full object-cover"
                                                    alt="{{ $item->livestock_name ?? 'Livestock' }}"
                                                    loading="lazy"
                                                >
                                            @else
                                                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="1.7"
                                                          d="M12 8c-1.5-2-4-2-5 0m10 0c-1.5-2-4-2-5 0M5 14h14M7 14l-1 4m11-4 1 4M9 18h6"/>
                                                </svg>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="font-bold text-slate-900">
                                                {{ $item->livestock_name ?? 'Unnamed livestock' }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-400">
                                                ID: {{ $item->id ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ $item->category ?? 'Not specified' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        {{ $item->ownerName ?? 'Unknown seller' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $item->ownerEmail ?? 'No email provided' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-extrabold text-slate-900">
                                    ₱{{ number_format((float) ($item->price ?? 0), 2) }}
                                </td>

                                <td class="px-5 py-4">
                                    @if(in_array($status, ['available', 'approved', 'active']))
                                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ ucfirst($status) }}
                                        </span>
                                    @elseif($status === 'pending')
                                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Pending
                                        </span>
                                    @elseif($status === 'rejected')
                                        <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                                            {{ ucfirst($status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            data-listing="{{ json_encode($listingData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
                                            class="inspect-listing rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800"
                                        >
                                            Inspect
                                        </button>

                                        <form
                                            action="{{ route('admin.deleteListing', $item->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to permanently delete this livestock listing? This action cannot be undone.')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="noSearchResults" class="hidden px-6 py-12 text-center">
                <h4 class="font-bold text-slate-900">No matching listings</h4>
                <p class="mt-2 text-sm text-slate-500">
                    Try another search term or select a different category.
                </p>
            </div>
        @endif
    </div>
</div>

{{-- Listing Inspection Modal --}}
<div
    id="listingModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTitle"
>
    <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-7">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">
                    Marketplace management
                </p>
                <h3 id="modalTitle" class="mt-1 text-lg font-extrabold text-slate-900 sm:text-xl">
                    Listing inspection
                </h3>
            </div>

            <button
                type="button"
                id="closeListingModal"
                aria-label="Close inspection"
                class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-800"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5 sm:p-7">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-[220px_1fr]">
                <div class="flex min-h-48 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 sm:min-h-56">
                    <img
                        id="modalListingImg"
                        src=""
                        alt="Livestock preview"
                        class="hidden max-h-64 w-full object-cover"
                    >
                    <span id="modalNoImgText" class="px-4 text-center text-sm font-semibold text-slate-400">
                        No image available
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Livestock name
                        </p>
                        <p id="modalListingName" class="mt-1 break-words text-xl font-extrabold text-slate-900">-</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                            Category and asking price
                        </p>
                        <p id="modalListingCategoryPrice" class="mt-1 text-lg font-extrabold text-[#0F5132]">-</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Seller</p>
                        <p id="modalOwnerName" class="mt-1 break-words font-bold text-slate-800">-</p>
                        <p id="modalOwnerEmail" class="mt-1 break-all text-sm text-slate-500">-</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Listing status</p>
                        <p id="modalStatus" class="mt-1 font-semibold text-slate-700">-</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="mb-3 text-sm font-extrabold text-slate-900">
                    Livestock details
                </h4>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sex</p>
                        <p id="modalSex" class="mt-1 break-words text-sm font-bold text-slate-800">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Color</p>
                        <p id="modalColor" class="mt-1 break-words text-sm font-bold text-slate-800">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Weight</p>
                        <p id="modalWeight" class="mt-1 break-words text-sm font-bold text-slate-800">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Height</p>
                        <p id="modalHeight" class="mt-1 break-words text-sm font-bold text-slate-800">-</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4 sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Description</p>
                <p id="modalDescription" class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-600">-</p>
            </div>
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-7">
            <button
                type="button"
                id="closeListingModalFooter"
                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
            >
                Close inspection
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('listingSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const listingRows = Array.from(document.querySelectorAll('.listing-row'));
    const resultCount = document.getElementById('listingResultCount');
    const noResults = document.getElementById('noSearchResults');

    function filterListings() {
        const searchTerm = (searchInput?.value || '').trim().toLowerCase();
        const selectedCategory = (categoryFilter?.value || '').toLowerCase();
        let visibleCount = 0;

        listingRows.forEach(function (row) {
            const searchableText = row.dataset.search || '';
            const rowCategory = row.dataset.category || '';

            const matchesSearch = searchableText.includes(searchTerm);
            const matchesCategory = !selectedCategory || rowCategory === selectedCategory;
            const shouldShow = matchesSearch && matchesCategory;

            row.classList.toggle('hidden', !shouldShow);

            if (shouldShow) {
                visibleCount++;
            }
        });

        if (resultCount) {
            resultCount.textContent = 'Showing ' + visibleCount + ' of ' + listingRows.length + ' listings';
        }

        if (noResults) {
            noResults.classList.toggle('hidden', visibleCount !== 0 || listingRows.length === 0);
        }
    }

    searchInput?.addEventListener('input', filterListings);
    categoryFilter?.addEventListener('change', filterListings);

    const modal = document.getElementById('listingModal');
    const modalImage = document.getElementById('modalListingImg');
    const noImageText = document.getElementById('modalNoImgText');

    function setText(id, value) {
        const element = document.getElementById(id);
        if (!element) return;

        if (value === null || value === undefined || String(value).trim() === '') {
            element.textContent = 'Not specified';
        } else {
            element.textContent = String(value);
        }
    }

    function openListingModal(listing) {
        setText('modalListingName', listing.name);
        setText(
            'modalListingCategoryPrice',
            (listing.category || 'Not specified') + ' • ₱' + (listing.price || '0.00')
        );
        setText('modalOwnerName', listing.ownerName);
        setText('modalOwnerEmail', listing.ownerEmail);
        setText('modalSex', listing.sex);
        setText('modalColor', listing.color);
        setText('modalStatus', listing.status);
        setText(
            'modalWeight',
            listing.weight && listing.weight !== 'Not specified'
                ? listing.weight + ' kg'
                : 'Not specified'
        );
        setText(
            'modalHeight',
            listing.height && listing.height !== 'Not specified'
                ? listing.height + ' cm'
                : 'Not specified'
        );
        setText('modalDescription', listing.description || 'No description provided.');

        const imageUrl = typeof listing.imageUrl === 'string'
            ? listing.imageUrl.trim()
            : '';

        if (imageUrl) {
            modalImage.src = imageUrl;
            modalImage.classList.remove('hidden');
            noImageText.classList.add('hidden');

            modalImage.onerror = function () {
                modalImage.removeAttribute('src');
                modalImage.classList.add('hidden');
                noImageText.classList.remove('hidden');
            };
        } else {
            modalImage.removeAttribute('src');
            modalImage.classList.add('hidden');
            noImageText.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        document.getElementById('closeListingModal')?.focus();
    }

    function closeListingModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.inspect-listing').forEach(function (button) {
        button.addEventListener('click', function () {
            try {
                const listing = JSON.parse(this.dataset.listing || '{}');
                openListingModal(listing);
            } catch (error) {
                console.error('Could not read listing details:', error);
                alert('Unable to open this listing. Please refresh the page and try again.');
            }
        });
    });

    document.getElementById('closeListingModal')?.addEventListener('click', closeListingModal);
    document.getElementById('closeListingModalFooter')?.addEventListener('click', closeListingModal);

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeListingModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeListingModal();
        }
    });
});
</script>
@endpush
