@extends('layouts.app')

@section('title', 'Deliveries & Live Tracking | Livestock Express')
@section('header_title', 'Delivery Management')
@section('header_subtitle', 'Schedule CVO on-site inspections, record results, and monitor approved livestock shipments across Alaminos City.')

@section('content')
@php
    $deliveryList = collect($deliveries ?? []);

    $formatDeliveryTimestamp = function ($value) {
        if (empty($value)) return null;
        try {
            if (is_object($value) && method_exists($value, 'get')) {
                $value = $value->get();
            }
            return \Illuminate\Support\Carbon::parse($value)->format('M d, Y h:i A');
        } catch (\Throwable $e) {
            return null;
        }
    };

    $getStatus = function ($delivery) {
        $status = $delivery->orderStatus ?? $delivery->status ?? 'Pending Verification & Dispatch';
        return strtolower(trim((string) $status));
    };

    $pendingList = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), ['pending', 'pending verification & dispatch', 'pending verification', 'pending dispatch', 'inspection pending', 'pending online inspection review', 'pending cvo inspection', 'cvo inspection scheduled', 'for cvo reinspection', 'cvo inspection rejected'], true);
    });

    $dispatchedList = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), ['dispatched', 'in transit', 'in_transit', 'city veterinary inspection approved', 'ready for dispatch/pick up', 'ready for dispatch', 'ready for pick up'], true);
    });

    $deliveredList = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), ['delivered', 'completed'], true);
    });
@endphp

<div class="space-y-6">

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-800 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <div><p class="font-bold">Action completed</p><p class="mt-1">{{ session('success') }}</p></div>
        </div>
    @endif

    {{-- Page Introduction --}}
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#0A3622] to-[#1B5E35] p-6 text-white shadow-sm sm:p-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">Livestock Express Operations</p>
                <h3 class="mt-2 text-2xl font-extrabold">CVO Inspection & Delivery Management</h3>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100">
                    CVO personnel visit the seller’s location. Sellers do not upload inspection photos or videos. Orders require approval before dispatch or pick up.
                </p>
            </div>
        </div>
    </section>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Total Deliveries</p>
            <h3 class="mt-3 text-3xl font-extrabold text-slate-900">{{ $deliveryList->count() }}</h3>
        </div>
        <div class="admin-card p-5">
            <p class="text-sm font-medium text-amber-700">Pending / Inspection</p>
            <h3 class="mt-3 text-3xl font-extrabold text-amber-600">{{ $pendingList->count() }}</h3>
        </div>
        <div class="admin-card p-5">
            <p class="text-sm font-medium text-blue-700">Dispatched / In Transit</p>
            <h3 class="mt-3 text-3xl font-extrabold text-blue-600">{{ $dispatchedList->count() }}</h3>
        </div>
        <div class="admin-card p-5">
            <p class="text-sm font-medium text-emerald-700">Delivered / Completed</p>
            <h3 class="mt-3 text-3xl font-extrabold text-emerald-700">{{ $deliveredList->count() }}</h3>
        </div>
    </div>

    {{-- Admin Live Tracking Map View --}}
    <section class="admin-card overflow-hidden p-6">
        <div class="flex items-center justify-between pb-4">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Alaminos City Live Tracking Radar</h3>
                <p class="text-sm text-slate-500">Live GPS markers streaming directly from active seller dispatches.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                <span class="h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span> Admin Radar Online
            </span>
        </div>
        <div id="adminAlaminosMap" class="h-[450px] w-full rounded-xl border border-slate-200 shadow-inner"></div>
    </section>

    {{-- 1. PENDING & INSPECTION BLOCK --}}
    <section class="admin-card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <div class="h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></div>
                <h3 class="text-lg font-extrabold text-slate-900">CVO On-Site Inspection Queue ({{ $pendingList->count() }})</h3>
            </div>
            <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">Requires CVO Scheduling and Physical Inspection</span>
        </div>
        @if($pendingList->isEmpty())
            <div class="p-8 text-center text-sm text-slate-400">No pending or inspection deliveries found.</div>
        @else
            <div class="admin-scrollbar overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead class="bg-slate-50/60">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Delivery ID</th>
                            <th class="px-5 py-3">Livestock / Order</th>
                            <th class="px-5 py-3">Destination</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($pendingList as $deliv)
                            @php
                                $deliveryId = $deliv->id ?? 'N/A';
                                $livestockName = $deliv->livestock_name ?? $deliv->livestockName ?? 'Livestock Order';
                                $destination = $deliv->destination ?? $deliv->deliveryAddress ?? 'Alaminos City';
                                
                                $photoUrl = $deliv->inspectionPhotoUrl ?? (isset($deliv->inspectionPhotos[0]) ? $deliv->inspectionPhotos[0] : '') ?? $deliv->imageUrl ?? '';
                                $videoUrl = $deliv->inspectionVideoUrl ?? $deliv->videoUrl ?? '';
                                $hasMedia = !empty($photoUrl) || !empty($videoUrl);

                                $mediaArray = [
                                    'id' => $deliveryId,
                                    'livestockName' => $livestockName,
                                    'inspectionPhotoUrl' => $photoUrl,
                                    'inspectionVideoUrl' => $videoUrl,
                                ];
                                $safeMediaJson = base64_encode(json_encode($mediaArray));
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-bold text-slate-600">#{{ $deliveryId }}</td>
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $livestockName }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $destination }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Pending Inspection
                                    </span>
                                    <div class="mt-2 space-y-1 text-[11px] text-slate-500">
                                        @if($createdAt = $formatDeliveryTimestamp($deliv->createdAt ?? null))
                                            <div><span class="font-semibold text-slate-600">Order placed:</span> {{ $createdAt }}</div>
                                        @endif
                                        @if($scheduledAt = $formatDeliveryTimestamp($deliv->inspectionScheduledAt ?? null))
                                            <div><span class="font-semibold text-slate-600">Inspection scheduled:</span> {{ $scheduledAt }}</div>
                                        @endif
                                        @if($submittedAt = $formatDeliveryTimestamp($deliv->inspectionSubmittedAt ?? $deliv->inspectionCompletedAt ?? null))
                                            <div><span class="font-semibold text-slate-600">Inspection submitted:</span> {{ $submittedAt }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
<div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.deliveries.scheduleInspection', $deliveryId) }}" method="POST" class="flex flex-wrap items-center justify-end gap-2">
                                            @csrf
                                            <input type="datetime-local" name="inspectionDate" required class="rounded-lg border border-slate-300 px-2 py-2 text-xs">
                                            <input type="text" name="inspectorName" required placeholder="CVO inspector" class="w-32 rounded-lg border border-slate-300 px-2 py-2 text-xs">
                                            <button type="submit" class="rounded-xl bg-amber-600 px-3 py-2 text-xs font-bold text-white">Schedule Visit</button>
                                        </form>
                                        @if(str_contains(strtolower((string)($deliv->orderStatus ?? '')), 'scheduled'))
                                            <form action="{{ route('admin.deliveries.inspectionResult', $deliveryId) }}" method="POST" class="mt-2 flex flex-wrap justify-end gap-2">
                                                @csrf
                                                <input type="text" name="inspectionNotes" placeholder="Inspection notes (optional)" class="w-40 rounded-lg border border-slate-300 px-2 py-2 text-xs">
                                                <select name="inspectionResult" required class="rounded-lg border border-slate-300 px-2 py-2 text-xs"><option value="">Record result</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="reinspection">For reinspection</option></select>
                                                <button type="submit" class="rounded-xl bg-emerald-700 px-3 py-2 text-xs font-bold text-white">Save Result</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- 2. DISPATCHED / IN TRANSIT BLOCK --}}
    <section class="admin-card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <div class="h-2.5 w-2.5 rounded-full bg-blue-500 animate-pulse"></div>
                <h3 class="text-lg font-extrabold text-slate-900">Dispatched & In Transit ({{ $dispatchedList->count() }})</h3>
            </div>
            <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">Live Tracking Active</span>
        </div>
        @if($dispatchedList->isEmpty())
            <div class="p-8 text-center text-sm text-slate-400">No active shipments in transit.</div>
        @else
            <div class="admin-scrollbar overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead class="bg-slate-50/60">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Delivery ID</th>
                            <th class="px-5 py-3">Livestock / Order</th>
                            <th class="px-5 py-3">Destination</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($dispatchedList as $deliv)
                            @php
                                $deliveryId = $deliv->id ?? 'N/A';
                                $livestockName = $deliv->livestock_name ?? $deliv->livestockName ?? 'Livestock Order';
                                $destination = $deliv->destination ?? $deliv->deliveryAddress ?? 'Alaminos City';
                                $deliveryProofUrl = $deliv->deliveryProofUrl ?? '';
                                $proofArray = ['id' => $deliveryId, 'livestockName' => $livestockName, 'deliveryProofUrl' => $deliveryProofUrl];
                                $safeProofJson = base64_encode(json_encode($proofArray));
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-bold text-slate-600">#{{ $deliveryId }}</td>
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $livestockName }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $destination }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        In Transit / Dispatched
                                    </span>
                                    @if($dispatchedAt = $formatDeliveryTimestamp($deliv->dispatchedAt ?? null))
                                        <div class="mt-2 text-[11px] text-slate-500">
                                            <span class="font-semibold text-slate-600">Dispatched:</span> {{ $dispatchedAt }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button"
                                        data-order-id="{{ $deliveryId }}"
                                        data-lat="{{ $deliv->latitude ?? $deliv->lat ?? '' }}"
                                        data-lng="{{ $deliv->longitude ?? $deliv->lng ?? '' }}"
                                        data-livestock="{{ e($livestockName) }}"
                                        onclick="focusDeliveryLocation(this)"
                                        class="rounded-xl border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-blue-800 hover:bg-blue-50 shadow-sm">
                                        View Location
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- 3. DELIVERED / COMPLETED BLOCK --}}
    <section class="admin-card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <div class="h-2.5 w-2.5 rounded-full bg-emerald-600"></div>
                <h3 class="text-lg font-extrabold text-slate-900">Delivered & Completed Shipments ({{ $deliveredList->count() }})</h3>
            </div>
            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Successfully Fulfilled</span>
        </div>
        @if($deliveredList->isEmpty())
            <div class="p-8 text-center text-sm text-slate-400">No completed deliveries recorded yet.</div>
        @else
            <div class="admin-scrollbar overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead class="bg-slate-50/60">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Delivery ID</th>
                            <th class="px-5 py-3">Livestock / Order</th>
                            <th class="px-5 py-3">Destination</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Review</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($deliveredList as $deliv)
                            @php
                                $deliveryId = $deliv->id ?? 'N/A';
                                $livestockName = $deliv->livestock_name ?? $deliv->livestockName ?? 'Livestock Order';
                                $destination = $deliv->destination ?? $deliv->deliveryAddress ?? 'Alaminos City';
                                // Match the exact field written by SellerOrdersScreen.
                                $deliveryProofUrl = $deliv->deliveryProofUrl ?? '';
                                $proofArray = [
                                    'id' => $deliveryId,
                                    'livestockName' => $livestockName,
                                    'deliveryProofUrl' => $deliveryProofUrl,
                                ];
                                $safeProofJson = base64_encode(json_encode($proofArray));
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-bold text-slate-600">#{{ $deliveryId }}</td>
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $livestockName }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $destination }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Delivered & Completed
                                    </span>
                                    <div class="mt-2 space-y-1 text-[11px] text-slate-500">
                                        @if($dispatchedAt = $formatDeliveryTimestamp($deliv->dispatchedAt ?? null))
                                            <div><span class="font-semibold text-slate-600">Dispatched:</span> {{ $dispatchedAt }}</div>
                                        @endif
                                        @if($inspectionSubmittedAt = $formatDeliveryTimestamp($deliv->inspectionSubmittedAt ?? $deliv->inspectionCompletedAt ?? null))
                                            <div><span class="font-semibold text-slate-600">Inspection submitted:</span> {{ $inspectionSubmittedAt }}</div>
                                        @endif
                                        @if($deliveredAt = $formatDeliveryTimestamp($deliv->deliveredAt ?? null))
                                            <div><span class="font-semibold text-slate-600">Delivered:</span> {{ $deliveredAt }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" data-safe-proof="{{ $safeProofJson }}" onclick="openDeliveryProofSafe(this)" class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-50 shadow-sm">View Delivery Proof</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

</div>

{{-- Inspection Media Preview Modal --}}
<div id="inspectionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Delivery Confirmation</p>
                <h3 id="modalLivestockTitle" class="text-base font-extrabold text-slate-900">Delivery Proof</h3>
            </div>
            <button type="button" onclick="closeInspectionModal()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase mb-2">Delivery Proof Photo</p>
                <img id="modalInspectPhoto" src="" alt="Inspection Photo" class="max-h-[60vh] w-full rounded-2xl object-contain border border-slate-200 bg-slate-50">
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase mb-2">Real-Time Inspection Video</p>
                <div id="modalVideoContainer" class="rounded-2xl border border-slate-200 overflow-hidden bg-black h-64 flex items-center justify-center">
                    <video id="modalInspectVideo" controls class="h-full w-full object-contain">
                        Your browser does not support the video tag.
                    </video>
                    <p id="modalNoVideo" class="text-xs text-slate-400 hidden">No real-time video submitted by seller.</p>
                </div>
            </div>
        </div>
        <div class="flex justify-end border-t border-slate-100 px-6 py-4 bg-slate-50">
            <button type="button" onclick="closeInspectionModal()" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUKpYKSAinYNN7yclU_IWE911QgBl2cXM&libraries=places"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>

<script>
const firebaseConfig = {
  apiKey: "AIzaSyBUKpYKSAinYNN7yclU_IWE911QgBl2cXM",
  authDomain: "livestock-express-new-2026.firebaseapp.com",
  projectId: "livestock-express-new-2026"
};

if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}
const db = firebase.firestore();

let adminMap;
let activeMarkers = {};
let routeLines = {}; // Stores the road polyline path for each delivery
let deliveryLocations = {}; // Latest GPS location per order

function initAdminMap() {
    const alaminosCenter = { lat: 16.1556, lng: 119.9844 };
    adminMap = new google.maps.Map(document.getElementById('adminAlaminosMap'), {
        center: alaminosCenter,
        zoom: 13,
        mapTypeId: 'roadmap' // Use 'roadmap' to clearly see street and road names
    });

    db.collection('orders').onSnapshot((snapshot) => {
        snapshot.docs.forEach((doc) => {
            const docId = doc.id;
            const data = doc.data();
            const status = (data.orderStatus || data.status || '').toString().trim().toLowerCase();
            
            const lat = Number(data.latitude || data.lat || 0);
            const lng = Number(data.longitude || data.lng || 0);
            const livestockName = data.livestockName || data.livestock_name || 'Livestock Order';

            // Inspection approval / ready-for-dispatch is NOT a dispatched shipment.
            const isDispatched = ['dispatched', 'in transit', 'in_transit', 'shipping'].includes(status);

            if (isDispatched && lat !== 0 && lng !== 0) {
                const position = { lat: lat, lng: lng };
                deliveryLocations[docId] = position;

                // Initialize path array for this order if it doesn't exist
                if (!window.deliveryPaths) window.deliveryPaths = {};
                if (!window.deliveryPaths[docId]) {
                    window.deliveryPaths[docId] = [];
                }

                // Add new GPS point to path history if it moved
                const pathArray = window.deliveryPaths[docId];
                const lastPoint = pathArray[pathArray.length - 1];
                if (!lastPoint || lastPoint.lat !== lat || lastPoint.lng !== lng) {
                    pathArray.push(position);
                }

                // Update or create Marker pin
                if (activeMarkers[docId]) {
                    activeMarkers[docId].setPosition(position);
                } else {
                    const marker = new google.maps.Marker({
                        position: position,
                        map: adminMap,
                        title: livestockName,
                        animation: google.maps.Animation.DROP
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `<div style='padding:8px;'><strong style='color:#0A3622;'>${livestockName}</strong><br>Status: ${data.orderStatus}<br>Road GPS: ${lat.toFixed(4)}, ${lng.toFixed(4)}</div>`
                    });

                    marker.addListener("click", () => {
                        infoWindow.open(adminMap, marker);
                    });

                    activeMarkers[docId] = marker;
                    adminMap.setCenter(position);
                    adminMap.setZoom(15);
                }

                // Draw or update the road polyline path
                if (routeLines[docId]) {
                    routeLines[docId].setPath(pathArray);
                } else {
                    const line = new google.maps.Polyline({
                        path: pathArray,
                        geodesic: true,
                        strokeColor: '#134E27', // Deep Agri-Tech green road path
                        strokeOpacity: 0.8,
                        strokeWeight: 5
                    });
                    line.setMap(adminMap);
                    routeLines[docId] = line;
                }
            } else {
                delete deliveryLocations[docId];
                // Clean up markers and paths if order is completed
                if (activeMarkers[docId]) {
                    activeMarkers[docId].setMap(null);
                    delete activeMarkers[docId];
                }
                if (routeLines[docId]) {
                    routeLines[docId].setMap(null);
                    delete routeLines[docId];
                }
            }
        });
    });
}

window.addEventListener('load', initAdminMap);

function focusDeliveryLocation(button) {
    if (!adminMap || !window.google || !google.maps) {
        alert('The tracking map is not ready yet. Please refresh the page and try again.');
        return;
    }

    const orderId = String(button.dataset.orderId || '');
    const latitude = Number(button.dataset.lat);
    const longitude = Number(button.dataset.lng);
    const livestockName = button.dataset.livestock || 'Livestock Order';

    // Prefer the latest live Firestore position; fall back to the saved dispatch coordinates.
    const livePosition = deliveryLocations[orderId];
    const position = livePosition || (
        Number.isFinite(latitude) && Number.isFinite(longitude) && latitude !== 0 && longitude !== 0
            ? { lat: latitude, lng: longitude }
            : null
    );

    if (!position) {
        alert('No GPS coordinates have been saved for this shipment. Ask the seller to dispatch the order with phone location enabled.');
        return;
    }

    adminMap.panTo(position);
    adminMap.setZoom(17);

    let marker = activeMarkers[orderId];
    if (!marker) {
        marker = new google.maps.Marker({
            position: position,
            map: adminMap,
            title: livestockName,
            animation: google.maps.Animation.DROP
        });
        marker.addListener('click', () => {
            const infoWindow = new google.maps.InfoWindow({
                content: `<div style="padding:8px;"><strong>${livestockName}</strong><br>Latest saved GPS: ${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}</div>`
            });
            infoWindow.open(adminMap, marker);
        });
        activeMarkers[orderId] = marker;
    } else {
        marker.setPosition(position);
        marker.setMap(adminMap);
    }

    google.maps.event.trigger(marker, 'click');
}

function openDeliveryProofSafe(button) {
    try {
        const encoded = button.getAttribute('data-safe-proof');
        const proof = JSON.parse(atob(encoded));
        const title = document.getElementById('modalLivestockTitle');
        const image = document.getElementById('modalInspectPhoto');
        const video = document.getElementById('modalInspectVideo');
        const noVideo = document.getElementById('modalNoVideo');
        const videoContainer = document.getElementById('modalVideoContainer');
        if (title) title.textContent = (proof.livestockName || 'Livestock') + ' — Delivery Proof (#' + proof.id + ')';
        if (image) {
            image.src = proof.deliveryProofUrl || '';
            image.style.display = proof.deliveryProofUrl ? 'block' : 'none';
        }
        const photoLabel = image?.previousElementSibling;
        if (photoLabel) photoLabel.textContent = 'Delivery Proof Photo';
        if (videoContainer) videoContainer.style.display = 'none';
        if (video) { video.pause(); video.src = ''; }
        if (noVideo) noVideo.style.display = 'none';
        if (!proof.deliveryProofUrl) {
            alert('No delivery proof has been submitted for this order yet. Confirm that the seller completed the camera upload and that deliveryProofUrl exists on this Firestore order.');
            return;
        }
        const modal = document.getElementById('inspectionModal');
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    } catch (error) {
        console.error('Unable to open delivery proof:', error);
        alert('Unable to open delivery proof.');
    }
}

function openInspectionModalSafe(button) {
    try {
        const encodedData = button.getAttribute('data-safe-media');
        const decodedJson = atob(encodedData);
        const media = JSON.parse(decodedJson);

        const titleEl = document.getElementById('modalLivestockTitle');
        if (titleEl) {
            titleEl.textContent = (media.livestockName || 'Livestock') + ' (# ' + media.id + ')';
        }
        
        const photoImg = document.getElementById('modalInspectPhoto');
        if (photoImg) {
            photoImg.src = media.inspectionPhotoUrl || '';
        }

        const videoEl = document.getElementById('modalInspectVideo');
        const noVideoEl = document.getElementById('modalNoVideo');

        if (videoEl && media.inspectionVideoUrl) {
            videoEl.src = media.inspectionVideoUrl;
            videoEl.style.display = 'block';
            if (noVideoEl) noVideoEl.style.display = 'none';
        } else {
            if (videoEl) {
                videoEl.pause();
                videoEl.src = '';
                videoEl.style.display = 'none';
            }
            if (noVideoEl) noVideoEl.style.display = 'block';
        }

        const modal = document.getElementById('inspectionModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    } catch (e) {
        console.error('Error opening inspection media modal:', e);
    }
}

function closeInspectionModal() {
    const videoEl = document.getElementById('modalInspectVideo');
    if (videoEl) {
        videoEl.pause();
        videoEl.src = '';
    }
    const modal = document.getElementById('inspectionModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
</script>
@endpush