@extends('layouts.app')

@section('title', 'Pending Listings - Livestock Express')
@section('header_title', 'Pending Approvals')
@section('header_subtitle', 'Review livestock listings and verify seller submissions before publishing.')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            <p class="font-bold">Action completed</p>
            <p class="mt-1">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
            <p class="font-bold">Please check the following:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <span class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                    Listing moderation
                </span>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Review before publishing</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/90">
                    Inspect livestock details, check private ownership certificates, and approve or reject submissions.
                </p>
            </div>

            <div class="flex h-20 w-20 shrink-0 items-center justify-center self-start rounded-2xl border border-white/15 bg-white/10 sm:self-center">
                <svg class="h-10 w-10 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12l2 2 4-4m5-3v6a9 9 0 11-18 0V7l9-4 9 4z"/>
                </svg>
            </div>
        </div>
        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/10"></div>
        <div class="absolute -right-2 -top-8 h-40 w-40 rounded-full border border-white/10"></div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Awaiting review</p>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ count($pendingListings) }}</p>
            <p class="mt-1 text-xs text-slate-400">Listings in this queue</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Review status</p>
            <p class="mt-3 text-xl font-extrabold text-slate-900">
                {{ count($pendingListings) === 0 ? 'All clear' : 'Action needed' }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Based on the current queue</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Admin checklist</p>
            <p class="mt-3 text-xl font-extrabold text-slate-900">Verify certificates</p>
            <p class="mt-1 text-xs text-slate-400">Inspect ownership papers before approval</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Pending listing queue</h3>
                <p class="mt-1 text-sm text-slate-500">Inspect each submission and confidential document before it appears in the marketplace.</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                {{ count($pendingListings) }} pending
            </span>
        </div>

        @if(count($pendingListings) === 0)
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="mt-4 text-base font-extrabold text-slate-900">No pending listings</h4>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                    There are no livestock submissions waiting for review. New submissions will appear here.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
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

                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingListings as $item)
                            @php
                                $listingData = [
                                    'id' => $item->id ?? '',
                                    'name' => $item->livestock_name ?? 'Unnamed livestock',
                                    'category' => $item->category ?? 'Not specified',
                                    'price' => (float) ($item->price ?? 0),
                                    'ownerName' => $item->ownerName ?? 'Unknown seller',
                                    'ownerEmail' => $item->ownerEmail ?? 'No email provided',
                                    'sex' => $item->sex ?? 'Not specified',
                                    'color' => $item->color ?? 'Not specified',
                                    'weight' => $item->weight ?? 'Not specified',
                                    'height' => $item->height ?? 'Not specified',
                                    'description' => $item->description ?? 'No description provided.',
                                    'imageUrl' => $item->imageUrl ?? '',
                                    'ownershipCertificateUrl' => $item->ownershipCertificateUrl ?? '',
                                ];
                            @endphp

                            <tr class="transition hover:bg-emerald-50/30">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                            @if(!empty($item->imageUrl))
                                                <img src="{{ $item->imageUrl }}" class="h-full w-full object-cover" alt="Livestock listing">
                                            @else
                                                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 8c-1.5-2-4-2-5 0m10 0c-1.5-2-4-2-5 0M5 14h14M7 14l-1 4m11-4 1 4M9 18h6"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $item->livestock_name ?? 'Unnamed livestock' }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Listing #{{ $item->id ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ $item->category ?? 'Not specified' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $item->ownerName ?? 'Unknown seller' }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $item->ownerEmail ?? 'No email provided' }}</p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-extrabold text-slate-900">
                                    ₱{{ number_format((float) ($item->price ?? 0), 2) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Pending review
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            data-listing="{{ base64_encode(json_encode($listingData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) }}"
                                            onclick="inspectListingFromButton(this)"
                                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800">
                                            Inspect
                                        </button>

                                        <form action="{{ route('admin.pending.update', $item->id) }}" method="POST" onsubmit="return confirm('Approve this livestock listing?')">
                                            @csrf
                                            <input type="hidden" name="status" value="Available">
                                            <button type="submit" class="rounded-xl bg-[#1B5E20] px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#144c19]">
                                                Approve
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.pending.update', $item->id) }}" method="POST" onsubmit="return confirm('Reject this livestock listing?')">
                                            @csrf
                                            <input type="hidden" name="status" value="Rejected">
                                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                                Reject
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
    </div>
</div>

<!-- Inspection Modal including Private Certificate Review -->
<div id="listingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-white px-5 py-4 sm:px-7">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">Marketplace moderation</p>
                <h3 id="modalTitle" class="mt-1 text-lg font-extrabold text-slate-900 sm:text-xl">Listing & Ownership Inspection</h3>
            </div>
            <button type="button" onclick="closeListingModal()" aria-label="Close inspection" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-800">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5 sm:p-7 space-y-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-[220px_1fr]">
                <div class="flex min-h-48 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 sm:min-h-56">
                    <img id="modalListingImg" src="" alt="Livestock listing preview" class="hidden h-full max-h-64 w-full object-cover">
                    <span id="modalNoImgText" class="px-4 text-center text-sm font-semibold text-slate-400">No image available</span>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Livestock name / breed</p>
                        <p id="modalListingName" class="mt-1 text-xl font-extrabold text-slate-900">-</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Category and asking price</p>
                        <p id="modalListingCategoryPrice" class="mt-1 text-lg font-extrabold text-[#0F5132]">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Seller</p>
                        <p id="modalOwnerName" class="mt-1 font-bold text-slate-800">-</p>
                        <p id="modalOwnerEmail" class="mt-1 break-all text-sm text-slate-500">-</p>
                    </div>
                </div>
            </div>

            <!-- Confidential Owner's Certificate Verification Section -->
            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-amber-900">🔒 Confidential Proof of Ownership</p>
                        <p class="text-xs text-amber-700 mt-0.5">Submitted by seller. Hidden from public buyers in the marketplace.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white p-3 rounded-xl border border-amber-200">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                        <img id="modalCertImg" src="" alt="Certificate" class="hidden h-full w-full object-cover">
                        <svg id="modalCertIcon" class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p id="modalCertStatus" class="text-xs font-bold text-slate-800">Certificate attached</p>
                        <a id="modalCertLink" href="#" target="_blank" class="mt-1 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:underline">
                            View full certificate document ↗
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="mb-3 text-sm font-extrabold text-slate-900">Livestock details</h4>
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

            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Description</p>
                <p id="modalDescription" class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-600">-</p>
            </div>
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-7">
            <button type="button" onclick="closeListingModal()" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                Close inspection
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function inspectListingFromButton(button) {
        try {
            const encoded = button.dataset.listing;
            const listing = JSON.parse(atob(encoded));
            openListingModal(listing);
        } catch (error) {
            console.error('Unable to read listing details:', error);
            alert('Unable to open listing details. Please refresh the page and try again.');
        }
    }

    function setModalText(id, value) {
        const element = document.getElementById(id);
        if (!element) return;

        element.textContent =
            value === null || value === undefined || value === ''
                ? 'Not specified'
                : String(value);
    }

    function openListingModal(listing) {
        setModalText('modalListingName', listing.name);
        setModalText(
            'modalListingCategoryPrice',
            (listing.category || 'Not specified') + ' • ₱' +
            Number(listing.price || 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        );

        setModalText('modalOwnerName', listing.ownerName);
        setModalText('modalOwnerEmail', listing.ownerEmail);
        setModalText('modalSex', listing.sex);
        setModalText('modalColor', listing.color);

        const weight = listing.weight;
        setModalText(
            'modalWeight',
            weight === 'Not specified' || weight === 'N/A'
                ? weight
                : String(weight) + ' kg'
        );

        const height = listing.height;
        setModalText(
            'modalHeight',
            height === 'Not specified' || height === 'N/A'
                ? height
                : String(height) + ' cm'
        );

        setModalText(
            'modalDescription',
            listing.description || 'No description provided.'
        );

        // Livestock Photo
        const image = document.getElementById('modalListingImg');
        const noImage = document.getElementById('modalNoImgText');
        const imageUrl = typeof listing.imageUrl === 'string' ? listing.imageUrl.trim() : '';

        if (imageUrl) {
            image.src = imageUrl;
            image.classList.remove('hidden');
            noImage.classList.add('hidden');
        } else {
            image.removeAttribute('src');
            image.classList.add('hidden');
            noImage.classList.remove('hidden');
        }

        // Ownership Certificate Preview
        const certImg = document.getElementById('modalCertImg');
        const certLink = document.getElementById('modalCertLink');
        const certStatus = document.getElementById('modalCertStatus');
        const certUrl = typeof listing.ownershipCertificateUrl === 'string' ? listing.ownershipCertificateUrl.trim() : '';

        if (certUrl) {
            certImg.src = certUrl;
            certImg.classList.remove('hidden');
            certLink.href = certUrl;
            certLink.style.display = 'inline-flex';
            certStatus.textContent = 'Verification document attached';
        } else {
            certImg.removeAttribute('src');
            certImg.classList.add('hidden');
            certLink.style.display = 'none';
            certStatus.textContent = 'No certificate attached by seller';
        }

        const modal = document.getElementById('listingModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeListingModal() {
        const modal = document.getElementById('listingModal');
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeListingModal();
        }
    });

    document.getElementById('listingModal')?.addEventListener('click', function(event) {
        if (event.target === this) {
            closeListingModal();
        }
    });
</script>
@endpush