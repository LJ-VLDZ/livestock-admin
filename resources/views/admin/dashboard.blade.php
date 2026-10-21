@extends('layouts.app')

@section('title', 'Dashboard - Livestock Express')
@section('header_title', 'Command Center')
@section('header_subtitle', 'Overview of livestock marketplace operations.')

@section('content')
<div class="space-y-8">

    {{-- Dashboard Metrics --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Registered Users --}}
        <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 p-6 text-white shadow-lg transition-transform duration-200 hover:-translate-y-1">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-100">Registered Users</p>
            <h3 class="mt-3 text-4xl font-extrabold">
                {{ number_format($usersCount ?? 0) }}
            </h3>
            <p class="mt-4 text-sm text-blue-100">Total registered accounts</p>
        </div>

        {{-- Livestock Listings --}}
        <div class="rounded-2xl bg-gradient-to-br from-[#0A3622] to-[#1B5E20] p-6 text-white shadow-lg transition-transform duration-200 hover:-translate-y-1">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-100">Livestock Listings</p>
            <h3 class="mt-3 text-4xl font-extrabold">
                {{ number_format($totalListings ?? 0) }}
            </h3>
            <p class="mt-4 text-sm text-emerald-100">Total marketplace listings</p>
        </div>

        {{-- Transactions --}}
        <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 p-6 text-white shadow-lg transition-transform duration-200 hover:-translate-y-1">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-100">Transactions</p>
            <h3 class="mt-3 text-4xl font-extrabold">
                {{ number_format($transactionsCount ?? 0) }}
            </h3>
            <p class="mt-4 text-sm text-amber-100">Total recorded transactions</p>
        </div>

        {{-- Pending Deliveries --}}
        <div class="rounded-2xl bg-gradient-to-br from-rose-600 to-rose-800 p-6 text-white shadow-lg transition-transform duration-200 hover:-translate-y-1">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-100">Pending Deliveries</p>
            <h3 class="mt-3 text-4xl font-extrabold">
                {{ number_format($pendingDeliveriesCount ?? 0) }}
            </h3>
            <p class="mt-4 text-sm text-rose-100">Deliveries awaiting action</p>
        </div>

    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Sales Chart --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Sales Overview</h2>
                <p class="mt-1 text-sm text-slate-500">Monthly sales amount</p>
            </div>

            <div class="relative h-72 w-full">
                <canvas id="salesBarChart" aria-label="Monthly sales chart" role="img"></canvas>
                <p id="salesChartFallback" class="hidden absolute inset-0 items-center justify-center text-sm text-slate-500">
                    The sales chart could not be loaded.
                </p>
            </div>
        </section>

        {{-- Transaction Chart --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Transaction Timeline</h2>
                <p class="mt-1 text-sm text-slate-500">Monthly transaction volume</p>
            </div>

            <div class="relative h-72 w-full">
                <canvas id="transactionLineChart" aria-label="Monthly transaction chart" role="img"></canvas>
                <p id="transactionChartFallback" class="hidden absolute inset-0 items-center justify-center text-sm text-slate-500">
                    The transaction chart could not be loaded.
                </p>
            </div>
        </section>

    </div>

    {{-- Recent Activity --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Recent System Activity</h2>
                <p class="mt-1 text-sm text-slate-500">Latest recorded administrative activities</p>
            </div>

            @if (\Illuminate\Support\Facades\Route::has('admin.logs'))
                <a href="{{ route('admin.logs') }}"
                   class="inline-flex items-center text-sm font-semibold text-emerald-800 hover:text-emerald-950">
                    View All Logs
                    <span class="ml-2" aria-hidden="true">&rarr;</span>
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left">
                <thead class="bg-slate-50">
                    <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-4">Initiator</th>
                        <th class="px-5 py-4">Action</th>
                        <th class="px-5 py-4 text-right">Date and Time</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse (($recentActivities ?? collect()) as $activity)
                        <tr class="transition-colors hover:bg-slate-50">
                            <td class="px-5 py-4">
                                @php
                                    // Safely extract properties
                                    $actorType = property_exists($activity, 'actorType') ? $activity->actorType : (property_exists($activity, 'actor_type') ? $activity->actor_type : '');
                                    $adminName = property_exists($activity, 'admin_name') ? $activity->admin_name : null;
                                    $adminEmail = property_exists($activity, 'admin_email') ? $activity->admin_email : null;
                                    $userName = property_exists($activity, 'user_name') ? $activity->user_name : (property_exists($activity, 'name') ? $activity->name : null);
                                    $userEmail = property_exists($activity, 'user_email') ? $activity->user_email : (property_exists($activity, 'email') ? $activity->email : null);

                                    $isAdminActivity = in_array(strtolower($actorType), ['admin', 'administrator'], true)
                                        || ($actorType === '' && (!empty($adminEmail) || !empty($adminName)));

                                    if ($isAdminActivity) {
                                        $initiator = !empty($adminName) ? $adminName : (!empty($adminEmail) ? $adminEmail : 'City Veterinary Office');
                                        $roleLabel = 'Administrator';
                                    } else {
                                        $initiator = !empty($userName) ? $userName : (!empty($userEmail) ? $userEmail : 'Registered User');
                                        $roleLabel = 'User';
                                    }

                                    $initial = strtoupper(substr(trim($initiator), 0, 1)) ?: '?';
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-900">
                                        {{ $initial }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-800">
                                            {{ $initiator }}
                                        </p>
                                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                            {{ $roleLabel }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ property_exists($activity, 'action') ? $activity->action : 'Action recorded' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-slate-500">
                                @php
                                    $activityDate = property_exists($activity, 'timestamp') ? $activity->timestamp : (property_exists($activity, 'created_at') ? $activity->created_at : null);
                                    $displayTimestamp = null;
                                    
                                    if ($activityDate) {
                                        try {
                                            $displayTimestamp = \Carbon\Carbon::parse($activityDate)->setTimezone('Asia/Manila');
                                        } catch (\Throwable $e) {
                                            $displayTimestamp = null;
                                        }
                                    }
                                @endphp

                                @if ($displayTimestamp)
                                    <p class="font-medium text-slate-800">{{ $displayTimestamp->format('M d, Y') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $displayTimestamp->format('h:i A') }}</p>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-12 text-center text-slate-500">
                                No recent activity recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const salesCanvas = document.getElementById('salesBarChart');
    const transactionCanvas = document.getElementById('transactionLineChart');

    if (typeof Chart === 'undefined') {
        const salesFallback = document.getElementById('salesChartFallback');
        const transactionFallback = document.getElementById('transactionChartFallback');

        if (salesFallback) salesFallback.classList.remove('hidden');
        if (transactionFallback) transactionFallback.classList.remove('hidden');

        return;
    }

    const months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    const salesValues = @json($monthlySales ?? array_fill(0, 12, 0));
    const transactionValues = @json($monthlyTransactions ?? array_fill(0, 12, 0));

    if (salesCanvas) {
        new Chart(salesCanvas, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Sales Amount (₱)',
                    data: salesValues,
                    backgroundColor: '#1B5E20',
                    borderRadius: 6,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#E2E8F0' },
                        ticks: {
                            color: '#64748B',
                            callback: function (value) {
                                return '₱' + Number(value).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    if (transactionCanvas) {
        new Chart(transactionCanvas, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Transactions',
                    data: transactionValues,
                    borderColor: '#1B5E20',
                    backgroundColor: 'rgba(27, 94, 32, 0.12)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#64748B'
                        },
                        grid: { color: '#E2E8F0' }
                    }
                }
            }
        });
    }
});
</script>
@endpush