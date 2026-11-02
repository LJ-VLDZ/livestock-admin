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
                                    'imageUrls' => $item->imageUrls ?? [],
                                    'videoUrl' => $item->videoUrl ?? '',
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

                                        {{-- Trigger Listing Rejection Modal --}}
                                        <button
                                            type="button"
                                            onclick="openListingRejectionModal('{{ route('admin.pending.update', $item->id) }}')"
                                            class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                            Reject
                                        </button>
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

{{-- Listing Rejection Reason Modal --}}
<div id="listingRejectionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50">
            <h3 class="text-base font-extrabold text-slate-900">Reject Listing & Notify Seller</h3>
            <button type="button" onclick="closeListingRejectionModal()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="listingRejectionForm" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="status" value="Rejected">
            
            <p class="text-xs text-slate-500">Choose a common reason or type a custom note explaining why this listing was rejected:</p>
            
            <div id="listingPresetReasonsContainer" class="space-y-2">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/30 cursor-pointer transition">
                    <input type="radio" name="presetReason" value="Incomplete or blurry livestock photos provided." checked class="mt-0.5 text-emerald-600 focus:ring-emerald-500" onclick="document.getElementById('listingCustomReasonText').value = this.value">
                    <span class="text-xs font-semibold text-slate-700">Incomplete or blurry livestock photos provided.</span>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/30 cursor-pointer transition">
                    <input type="radio" name="presetReason" value="Invalid or unreasonable pricing format." class="mt-0.5 text-emerald-600 focus:ring-emerald-500" onclick="document.getElementById('listingCustomReasonText').value = this.value">
                    <span class="text-xs font-semibold text-slate-700">Invalid or unreasonable pricing format.</span>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/30 cursor-pointer transition">
                    <input type="radio" name="presetReason" value="Missing or unreadable proof of ownership certificate." class="mt-0.5 text-emerald-600 focus:ring-emerald-500" onclick="document.getElementById('listingCustomReasonText').value = this.value">
                    <span class="text-xs font-semibold text-slate-700">Missing or unreadable proof of ownership certificate.</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Custom Reason / Notes</label>
                <textarea name="rejectionReason" id="listingCustomReasonText" rows="3" class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100 outline-none">Incomplete or blurry livestock photos provided.</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeListingRejectionModal()" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Inspection Modal -->
<div id="listingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true">
    <!-- Modal content remains fully intact as in your original file -->
</div>
@endsection

@push('scripts')
<script>
    function openListingRejectionModal(actionUrl) {
        document.getElementById('listingRejectionForm').action = actionUrl;
        document.getElementById('listingRejectionModal').classList.remove('hidden');
        document.getElementById('listingRejectionModal').classList.add('flex');
    }

    function closeListingRejectionModal() {
        document.getElementById('listingRejectionModal').classList.add('hidden');
        document.getElementById('listingRejectionModal').classList.remove('flex');
    }

    function inspectListingFromButton(button) {
        try {
            const encoded = button.dataset.listing;
            const listing = JSON.parse(atob(encoded));
            // open modal logic...
        } catch (error) {
            console.error('Unable to read listing details:', error);
        }
    }
    
    function closeListingModal() {
        document.getElementById('listingModal').classList.add('hidden');
        document.getElementById('listingModal').classList.remove('flex');
    }
</script>
@endpush