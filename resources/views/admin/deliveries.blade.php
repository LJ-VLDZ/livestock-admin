@extends('layouts.app')

@section('title', 'Deliveries & Live Tracking | Livestock Express')
@section('header_title', 'Delivery Management')
@section('header_subtitle', 'Monitor live livestock shipments and delivery progress across Alaminos City.')

@section('content')
@php
    $deliveryList = collect($deliveries ?? []);

    $getStatus = function ($delivery) {
        $status = $delivery->orderStatus ?? $delivery->status ?? 'Pending Verification & Dispatch';
        return strtolower(trim((string) $status));
    };

    $pendingCount = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), ['pending', 'pending verification & dispatch', 'pending verification', 'pending dispatch'], true);
    })->count();

    $dispatchedCount = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), ['dispatched', 'in transit', 'in_transit'], true);
    })->count();

    $deliveredCount = $deliveryList->filter(function ($delivery) use ($getStatus) {
        return in_array($getStatus($delivery), ['delivered', 'completed'], true);
    })->count();
@endphp

<div class="space-y-6">

    {{-- Page Introduction --}}
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#0A3622] to-[#1B5E35] p-6 text-white shadow-sm sm:p-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">Livestock Express Operations</p>
                <h3 class="mt-2 text-2xl font-extrabold">Live Delivery & Map Tracking</h3>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100">
                    Real-time operational tracking for active livestock shipments moving across the 39 barangays of Alaminos City.
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
            <p class="text-sm font-medium text-slate-500">Pending</p>
            <h3 class="mt-3 text-3xl font-extrabold text-amber-600">{{ $pendingCount }}</h3>
        </div>
        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Dispatched / In Transit</p>
            <h3 class="mt-3 text-3xl font-extrabold text-blue-600">{{ $dispatchedCount }}</h3>
        </div>
        <div class="admin-card p-5">
            <p class="text-sm font-medium text-slate-500">Delivered</p>
            <h3 class="mt-3 text-3xl font-extrabold text-emerald-700">{{ $deliveredCount }}</h3>
        </div>
    </div>

    {{-- Admin Live Tracking Map View --}}
    <section class="admin-card overflow-hidden p-6">
        <div class="flex items-center justify-between pb-4">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Alaminos City Live Tracking Radar</h3>
                <p class="text-sm text-slate-500">Live GPS markers streaming directly from active seller dispatches.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                <span class="h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span> Admin Radar Online
            </span>
        </div>
        <div id="adminAlaminosMap" class="h-[450px] w-full rounded-xl border border-slate-200 shadow-inner"></div>
    </section>

    {{-- Shipment Records Table --}}
    <section class="admin-card overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-extrabold text-slate-900">Shipment Records</h3>
        </div>
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
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($deliveryList as $deliv)
                        @php
                            $status = $getStatus($deliv);
                            $deliveryId = $deliv->id ?? 'N/A';
                            $livestockName = $deliv->livestock_name ?? $deliv->livestockName ?? 'Livestock Order';
                            $destination = $deliv->destination ?? $deliv->deliveryAddress ?? 'Alaminos City';
                        @endphp
                        <tr class="transition-colors hover:bg-emerald-50/30">
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-bold text-slate-600">#{{ $deliveryId }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $livestockName }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $destination }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold bg-emerald-50 text-emerald-700">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUKpYKSAinYNN7yclU_IWE911QgBl2cXM&libraries=places"></script>

<!-- Firebase App & Firestore Web SDKs -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>

<script>
// Initialize Firebase using your project configuration
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

function initAdminMap() {
    const alaminosCenter = { lat: 16.1556, lng: 119.9844 };

    adminMap = new google.maps.Map(document.getElementById('adminAlaminosMap'), {
        center: alaminosCenter,
        zoom: 13,
        restriction: {
            latLngBounds: { north: 16.2300, south: 16.0800, west: 119.9000, east: 120.0700 },
            strictBounds: false,
        },
    });

    // Listen to real-time changes in Firestore 'orders' collection
    db.collection('orders').onSnapshot((snapshot) => {
        snapshot.docChanges().forEach((change) => {
            const docId = change.doc.id;
            const data = change.doc.data();
            const status = (data.orderStatus || data.status || '').toLowerCase();
            const lat = data.deliveryLatitude;
            const lng = data.deliveryLongitude;
            const livestockName = data.livestockName || 'Livestock Order';

            // Only show markers for active dispatches that have valid coordinates
            if ((change.type === 'added' || change.type === 'modified') && status.includes('dispatched') && lat && lng) {
                const position = { lat: lat, lng: lng };

                if (activeMarkers[docId]) {
                    // Update existing marker position live
                    activeMarkers[docId].setPosition(position);
                } else {
                    // Create new live marker pin
                    const marker = new google.maps.Marker({
                        position: position,
                        map: adminMap,
                        title: livestockName,
                        animation: google.maps.Animation.DROP
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `<div style='padding:6px;'><strong style='color:#0A3622;'>${livestockName}</strong><br>Status: Dispatched<br>Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}</div>`
                    });

                    marker.addListener("click", () => {
                        infoWindow.open(adminMap, marker);
                    });

                    activeMarkers[docId] = marker;
                }
            }

            // Remove marker if order is completed, delivered, or cancelled
            if (change.type === 'removed' || status.includes('delivered') || status.includes('completed') || status.includes('cancelled')) {
                if (activeMarkers[docId]) {
                    activeMarkers[docId].setMap(null);
                    delete activeMarkers[docId];
                }
            }
        });
    });
}

window.addEventListener('load', initAdminMap);
</script>
@endpush