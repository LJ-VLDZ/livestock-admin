<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AdminController extends Controller
{
    private function initFirebase()
{
    $credentialsPath = base_path('firebase-credentials.json');

    if (!file_exists($credentialsPath)) {
        throw new \Exception('firebase-credentials.json not found in project root directory.');
    }

    $keyFile = json_decode(
        file_get_contents($credentialsPath),
        true
    );

    if (!is_array($keyFile)) {
        throw new \Exception('Invalid Firebase credentials JSON file.');
    }

    $credentials = \Google\Auth\Credentials\ServiceAccountCredentials::makeCredentials(
        ['https://www.googleapis.com/auth/datastore'],
        $keyFile
    );

    return new \Google\Cloud\Firestore\FirestoreClient([
        'projectId' => env('FIREBASE_PROJECT_ID', 'livestock-express-new-2026'),
        'credentials' => $credentials,
        'transport' => 'rest',
    ]);
}

    private function timestampValue($value): int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_object($value)) {
            if (method_exists($value, 'getTimestamp')) {
                return $value->getTimestamp();
            }
            if (method_exists($value, 'get')) {
                try {
                    $inner = $value->get();
                    if ($inner instanceof \DateTimeInterface) {
                        return $inner->getTimestamp();
                    }
                    if (is_object($inner) && method_exists($inner, 'getTimestamp')) {
                        return $inner->getTimestamp();
                    }
                } catch (\Throwable $e) {
                    // Ignore and fall through
                }
            }
            if (method_exists($value, '__toString')) {
                $parsed = strtotime((string)$value);
                if ($parsed !== false) return $parsed;
            }
        }

        if (is_numeric($value)) {
            $number = (float) $value;
            if ($number > 9999999999) {
                $number /= 1000;
            }
            return (int) $number;
        }

        if (is_string($value) && trim($value) !== '') {
            $parsed = strtotime($value);
            return $parsed === false ? 0 : $parsed;
        }

        return 0;
    }

    private function getCollectionData(string $collection): array
    {
        try {
            $database = $this->initFirebase();
            $documents = $database->collection($collection)->documents();
            $records = [];

            foreach ($documents as $document) {
                if (!$document->exists()) {
                    continue;
                }
                $data = $document->data();
                if (!is_array($data)) {
                    continue;
                }
                $data['id'] = $document->id();

                if ($collection === 'livestock') {
                    $createdAt = $data['createdAt'] ?? null;
                    $timestamp = $this->timestampValue($createdAt);
                    $thirtyDaysInSeconds = 30 * 24 * 60 * 60;

                    if ($timestamp > 0 && (time() - $timestamp) > $thirtyDaysInSeconds) {
                        $status = strtolower(trim((string)($data['status'] ?? '')));
                        if ($status !== 'expired' && $status !== 'sold') {
                            $database->collection('livestock')->document($document->id())->update([
                                ['path' => 'status', 'value' => 'expired'],
                                ['path' => 'isAvailable', 'value' => false],
                            ]);
                            $data['status'] = 'expired';
                            $data['isAvailable'] = false;
                        }
                    }
                }

                $records[] = $data;
            }

            return $records;
        } catch (\Throwable $e) {
            Log::error('Firestore collection read failed.', [
                'collection' => $collection,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function normalizeUser(array $data, string $id): array
    {
        $firstName = trim((string) ($data['firstName'] ?? ''));
        $lastName = trim((string) ($data['lastName'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            $name = trim($firstName . ' ' . $lastName);
        }

        $data['id'] = $id;
        $data['name'] = $name !== '' ? $name : 'Unnamed User';
        $data['firstName'] = $firstName;
        $data['lastName'] = $lastName;
        $data['email'] = $data['email'] ?? '';
        $data['phone'] = $data['phone'] ?? '';
        $data['barangay'] = $data['barangay'] ?? '';
        $data['city'] = $data['city'] ?? '';
        $data['idType'] = $data['idType'] ?? '';
        $data['idFrontUrl'] = $data['idFrontUrl'] ?? $data['idUrl'] ?? null;
        $data['idBackUrl'] = $data['idBackUrl'] ?? null;
        $data['verificationStatus'] = strtolower(
            trim((string) ($data['verificationStatus'] ?? 'pending'))
        );
        $data['rejectionReason'] = $data['rejectionReason'] ?? null;

        return $data;
    }

    private function sortByTimestamp(array &$records, string $field, bool $ascending = false): void
    {
        usort($records, function ($a, $b) use ($field, $ascending) {
            $timeA = $this->timestampValue($a[$field] ?? null);
            $timeB = $this->timestampValue($b[$field] ?? null);
            // Missing timestamps are always placed last.
            if ($timeA === 0 && $timeB !== 0) return 1;
            if ($timeB === 0 && $timeA !== 0) return -1;
            return $ascending ? ($timeA <=> $timeB) : ($timeB <=> $timeA);
        });
    }

    private function isTechnicalSupportTicket(array $ticket): bool
    {
        $category = strtolower(trim((string) ($ticket['category'] ?? '')));
        return $category === 'technical bug / error'
            || str_contains($category, 'technical')
            || str_contains($category, 'bug')
            || str_contains($category, 'error');
    }

    private function sendFirestoreNotification(string $userId, string $title, string $body, string $type = 'system')
    {
        try {
            $database = $this->initFirebase();
            
            $database->collection('users')
                ->document($userId)
                ->collection('notifications')
                ->add([
                    'title' => $title,
                    'body' => $body,
                    'type' => $type,
                    'isRead' => false,
                    'timestamp' => \Google\Cloud\Firestore\FieldValue::serverTimestamp(),
                ]);

            $userSnapshot = $database->collection('users')->document($userId)->snapshot();
            if ($userSnapshot->exists()) {
                $userData = $userSnapshot->data();
                $fcmToken = $userData['fcmToken'] ?? null;

                if (!empty($fcmToken)) {
                    $credentialsPath = base_path('firebase-credentials.json');
                    $factory = (new Factory)->withServiceAccount($credentialsPath);
                    $messaging = $factory->createMessaging();

                    $message = CloudMessage::withTarget('token', $fcmToken)
                        ->withNotification(Notification::create($title, $body))
                        ->withData([
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'type' => $type,
                        ]);

                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send Firestore notification & FCM push: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $usersCount = 0;
        $totalListings = 0;
        $totalRevenue = 0;
        $transactionsCount = 0;
        $pendingDeliveriesCount = 0;
        $recentListings = [];
        $recentActivities = [];
        $monthlySales = array_fill(0, 12, 0);
        $monthlyTransactions = array_fill(0, 12, 0);

        try {
            $usersRaw = $this->getCollectionData('users');
            foreach ($usersRaw as $user) {
                $email = strtolower(trim((string) ($user['email'] ?? '')));
                $role = strtolower(trim((string) ($user['role'] ?? 'user')));
                
                if (!in_array($role, ['admin', 'super_admin'], true) && $email !== 'veterinaryadminoffice@gmail.com' && $email !== 'vetoffice@livestock.com') {
                    $usersCount++;
                }
            }

            $listingsRaw = $this->getCollectionData('livestock');
            $totalListings = count($listingsRaw);
            $this->sortByTimestamp($listingsRaw, 'createdAt');
            $recentListings = array_map(fn($item) => (object)$item, array_slice($listingsRaw, 0, 5));

            $ordersRaw = $this->getCollectionData('orders');
            $transactionsCount = count($ordersRaw);

            foreach ($ordersRaw as $order) {
                $amount = (float) ($order['totalAmount'] ?? $order['amount'] ?? 0);
                $totalRevenue += $amount;

                $status = strtolower(trim((string) ($order['orderStatus'] ?? $order['status'] ?? '')));
                if (in_array($status, ['pending', 'pending verification & dispatch', 'dispatched', 'city veterinary inspection approved', 'pending online inspection review'], true)) {
                    $pendingDeliveriesCount++;
                }

                $timestamp = $order['createdAt'] ?? null;
                if ($timestamp) {
                    $timeVal = $this->timestampValue($timestamp);
                    if ($timeVal > 0) {
                        $monthIndex = (int) date('n', $timeVal) - 1;
                        if ($monthIndex >= 0 && $monthIndex < 12) {
                            $monthlyTransactions[$monthIndex]++;
                            $monthlySales[$monthIndex] += $amount;
                        }
                    }
                }
            }

            $activityLogsRaw = $this->getCollectionData('activity_logs');
            $this->sortByTimestamp($activityLogsRaw, 'timestamp');
            $recentActivities = array_map(fn($item) => (object)$item, array_slice($activityLogsRaw, 0, 5));

        } catch (\Throwable $e) {
            Log::error('Dashboard Firebase connection error: ' . $e->getMessage());
        }

        return view('admin.dashboard', compact(
            'usersCount',
            'totalListings',
            'totalRevenue',
            'transactionsCount',
            'pendingDeliveriesCount',
            'recentListings',
            'recentActivities',
            'monthlySales',
            'monthlyTransactions'
        ));
    }

    public function verifications()
    {
        $users = $this->getCollectionData('users');

        $pendingUsers = [];
        $verifiedUsers = [];
        $rejectedUsers = [];   
        $unverifiedUsers = []; 

        foreach ($users as $user) {
            $email = strtolower(trim((string) ($user['email'] ?? '')));
            $role = strtolower(trim((string) ($user['role'] ?? 'user')));

            if (in_array($role, ['admin', 'super_admin'], true) || $email === 'veterinaryadminoffice@gmail.com' || $email === 'vetoffice@livestock.com') {
                continue;
            }

            $user = $this->normalizeUser($user, $user['id']);
            $status = $user['verificationStatus'];

            $hasSubmittedId = !empty($user['idFrontUrl']) || !empty($user['idUrl']) || (!empty($user['idType']) && $user['idType'] !== 'N/A');

            if ($status === 'verified') {
                $verifiedUsers[] = (object) $user;
            } elseif ($status === 'rejected') {
                $rejectedUsers[] = (object) $user;
            } elseif ($status === 'pending' || ($hasSubmittedId && $status !== 'unverified')) {
                $pendingUsers[] = (object) $user;
            } else {
                $unverifiedUsers[] = (object) $user;
            }
        }

        // Use the dedicated ID submission timestamp; never substitute account creation time.
        $pendingArray = array_map(fn ($user) => (array) $user, $pendingUsers);
        $this->sortByTimestamp($pendingArray, 'verificationSubmittedAt', true);
        $pendingUsers = array_map(fn ($user) => (object) $user, $pendingArray);

        return view('admin.verifications', compact(
            'pendingUsers',
            'verifiedUsers',
            'rejectedUsers',
            'unverifiedUsers'
        ));
    }

    public function listings()
    {
        $records = $this->getCollectionData('livestock');
        $listings = array_map(fn ($item) => (object) $item, $records);
        return view('admin.listings', compact('listings'));
    }

    public function pendingListings()
    {
        $records = $this->getCollectionData('livestock');
        $pendingListings = [];

        foreach ($records as $item) {
            $status = strtolower(trim((string) ($item['status'] ?? 'available')));
            if ($status === 'pending') {
                $pendingListings[] = (object) $item;
            }
        }

        $pendingArray = array_map(fn ($item) => (array) $item, $pendingListings);
        $this->sortByTimestamp($pendingArray, 'createdAt', true);
        $pendingListings = array_map(fn ($item) => (object) $item, $pendingArray);

        return view('admin.pending-listings', compact('pendingListings'));
    }

    public function updateListingStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'rejectionReason' => ['nullable', 'string', 'max:500'],
        ]);

        $statusInput = trim($validated['status']);
        $statusLower = strtolower($statusInput);
        $rejectionReason = $request->input('rejectionReason');
        
        $database = $this->initFirebase();
        $docRef = $database->collection('livestock')->document($id);
        $snapshot = $docRef->snapshot();
        $listingData = $snapshot->data() ?? [];
        $sellerId = $listingData['seller_id'] ?? null;
        $livestockName = $listingData['livestock_name'] ?? 'Livestock';

        $updateData = [
            ['path' => 'status', 'value' => $statusLower === 'available' ? 'available' : $statusInput],
            ['path' => 'isAvailable', 'value' => ($statusLower === 'available' || $statusLower === 'approved')],
        ];

        if ($statusLower === 'rejected' && $rejectionReason) {
            $updateData[] = ['path' => 'rejectionReason', 'value' => $rejectionReason];
        }

        $docRef->update($updateData);

        if ($sellerId) {
            $isApproved = ($statusLower === 'available' || $statusLower === 'approved');
            $title = $isApproved ? 'Listing Approved! 🐄' : 'Listing Rejected ⚠️';
            $body = $isApproved 
                ? "Your livestock listing '$livestockName' has been approved and is now live on the marketplace."
                : "Your listing '$livestockName' was rejected. Reason: " . ($rejectionReason ?? 'Review guidelines.');
                
            $this->sendFirestoreNotification($sellerId, $title, $body, 'order');
        }

        $action = ($statusLower === 'available' || $statusLower === 'approved')
            ? 'Approved Livestock Listing'
            : ($statusLower === 'rejected' ? 'Rejected Livestock Listing' : 'Set Livestock Listing Pending');

        $this->logAdminAction($id, $action, $statusInput);

        return redirect()->back()->with('success', 'Livestock listing status updated successfully.');
    }

    public function deleteListing(Request $request, $id)
    {
        $rejectionReason = $request->input('rejectionReason', 'Accidental listing approval / verification error by administrator.');
        
        $database = $this->initFirebase();
        $docRef = $database->collection('livestock')->document($id);
        $snapshot = $docRef->snapshot();
        $listingData = $snapshot->data() ?? [];
        $sellerId = $listingData['seller_id'] ?? null;
        $livestockName = $listingData['livestock_name'] ?? 'Livestock';

        if ($sellerId) {
            $this->sendFirestoreNotification(
                $sellerId, 
                'Listing Removed ❌', 
                "Your listing '$livestockName' has been removed by admin. Reason: $rejectionReason", 
                'system'
            );
        }

        $docRef->delete();
        $this->logAdminAction($id, 'Deleted Livestock Listing', 'Removed permanently: ' . $rejectionReason);
        
        return redirect()->route('admin.listings')->with('success', 'Livestock listing successfully deleted and seller notified.');
    }

    public function transactions(Request $request)
    {
        $ordersRaw = $this->getCollectionData('orders');
        $transactionsRaw = $this->getCollectionData('transactions');

        $combinedTransactions = [];
        $orderLookup = [];

        foreach ($ordersRaw as $order) {
            $orderLookup[$order['id']] = $order;
            $combinedTransactions[] = $order;
        }

        foreach ($transactionsRaw as $txn) {
            $orderId = $txn['orderId'] ?? null;
            if ($orderId && isset($orderLookup[$orderId])) {
                $txn = array_merge($orderLookup[$orderId], $txn);
            }
            $combinedTransactions[] = $txn;
        }

        $uniqueTransactions = [];
        foreach ($combinedTransactions as $item) {
            $identifier = $item['id'] ?? ($item['orderId'] ?? uniqid());
            $uniqueTransactions[$identifier] = $item;
        }

        $transactionsList = array_values($uniqueTransactions);
        $this->sortByTimestamp($transactionsList, 'createdAt');

        $selectedMonth = $request->input('month');
        if ($selectedMonth) {
            $transactionsList = array_filter($transactionsList, function ($item) use ($selectedMonth) {
                $timestamp = $item['createdAt'] ?? null;
                if (!$timestamp) return false;
                $timeVal = $this->timestampValue($timestamp);
                if ($timeVal <= 0) return false;
                
                return date('Y-m', $timeVal) === $selectedMonth;
            });
            $transactionsList = array_values($transactionsList);
        }

        $transactions = array_map(fn($item) => (object) $item, $transactionsList);
        
        $totalRevenue = 0;
        $completedCount = 0;
        $pendingCount = 0;

        foreach ($transactionsList as $txn) {
            $status = strtolower(trim((string) ($txn['orderStatus'] ?? $txn['status'] ?? '')));
            $amount = (float) ($txn['totalAmount'] ?? $txn['amount'] ?? 0);

            if (in_array($status, ['completed', 'delivered', 'paid'], true)) {
                $totalRevenue += $amount;
                $completedCount++;
            } elseif (in_array($status, ['pending', 'pending verification & dispatch', 'city veterinary inspection approved', 'dispatched', 'pending online inspection review'], true)) {
                $pendingCount++;
            }
        }

        return view('admin.transactions', compact('transactions', 'totalRevenue', 'completedCount', 'pendingCount'));
    }

    public function exportPdf(Request $request)
    {
        $ordersRaw = $this->getCollectionData('orders');
        $transactionsRaw = $this->getCollectionData('transactions');

        $combined = [];
        $orderMap = [];
        foreach ($ordersRaw as $order) {
            $orderMap[$order['id']] = $order;
            $combined[] = $order;
        }
        foreach ($transactionsRaw as $txn) {
            $orderId = $txn['orderId'] ?? null;
            if ($orderId && isset($orderMap[$orderId])) {
                $txn = array_merge($orderMap[$orderId], $txn);
            }
            $combined[] = $txn;
        }

        $selectedMonth = $request->input('month');
        if ($selectedMonth) {
            $combined = array_filter($combined, function ($item) use ($selectedMonth) {
                $timestamp = $item['createdAt'] ?? null;
                if (!$timestamp) return false;
                
                $timeVal = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);
                if ($timeVal <= 0) return false;
                
                return date('Y-m', $timeVal) === $selectedMonth;
            });
            $combined = array_values($combined);
        }

        $totalRevenue = 0;
        foreach ($combined as $item) {
            $status = strtolower(trim((string) ($item['orderStatus'] ?? $item['status'] ?? '')));
            if (in_array($status, ['completed', 'delivered', 'paid'], true)) {
                $totalRevenue += (float) ($item['totalAmount'] ?? $item['amount'] ?? 0);
            }
        }

        $monthTitle = $selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') : 'All Time Records';

        return view('admin.transactions_pdf', [
            'transactions' => $combined,
            'totalRevenue' => $totalRevenue,
            'selectedMonthTitle' => $monthTitle,
            'generatedAt' => now()->setTimezone('Asia/Manila')->format('F d, Y h:i A')
        ]);
    }

    public function deliveries()
    {
        $records = $this->getCollectionData('orders');
        $this->sortByTimestamp($records, 'createdAt');
        $deliveries = [];

        foreach ($records as $item) {
            $item['orderStatus'] = $item['orderStatus'] ?? $item['status'] ?? 'Pending';
            $deliveries[] = (object) $item;
        }

        return view('admin.deliveries', compact('deliveries'));
    }

    /** Approve or reject a cancellation requested after CVO inspection approval. */
    public function reviewCancellation(Request $request, $id)
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'decisionReason' => ['nullable', 'string', 'max:1000'],
        ]);

        $database = $this->initFirebase();
        $orderRef = $database->collection('orders')->document($id);
        $orderSnapshot = $orderRef->snapshot();
        if (!$orderSnapshot->exists()) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $order = $orderSnapshot->data();
        $cancellationStatus = strtolower((string) ($order['cancellationStatus'] ?? ''));
        $orderStatus = strtolower((string) ($order['orderStatus'] ?? $order['status'] ?? ''));
        $inspectionStatus = strtolower((string) ($order['inspectionStatus'] ?? ''));
        $inspectionApproved = str_contains($inspectionStatus, 'approved')
            || str_contains($orderStatus, 'inspection approved')
            || str_contains($orderStatus, 'ready for dispatch')
            || str_contains($orderStatus, 'ready for pick up');

        if ($cancellationStatus !== 'pending_review' || !$inspectionApproved) {
            return redirect()->back()->with('error', 'This order is not eligible for cancellation review.');
        }
        if (str_contains($orderStatus, 'dispatch') || str_contains($orderStatus, 'transit')
            || in_array($orderStatus, ['delivered', 'completed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'This order has already entered fulfillment and cannot be reviewed for in-app cancellation.');
        }

        $now = \Google\Cloud\Firestore\FieldValue::serverTimestamp();
        $decision = $validated['decision'];
        $update = [
            ['path' => 'cancellationStatus', 'value' => $decision === 'approve' ? 'approved' : 'rejected'],
            ['path' => 'cancellationDecision', 'value' => $decision === 'approve' ? 'Approved by Admin/CVO' : 'Rejected by Admin/CVO'],
            ['path' => 'cancellationDecisionReason', 'value' => $validated['decisionReason'] ?? ''],
            ['path' => 'cancellationDecidedAt', 'value' => $now],
            ['path' => 'cancellationDecidedBy', 'value' => auth()->user()->email ?? auth()->id()],
        ];

        if ($decision === 'approve') {
            $update[] = ['path' => 'orderStatus', 'value' => 'Cancelled'];
            $update[] = ['path' => 'cancelledAt', 'value' => $now];
            $listingId = (string) ($order['listingId'] ?? $order['livestockId'] ?? '');
            if ($listingId !== '') {
                $listingRef = $database->collection('livestock')->document($listingId);
                $listingSnapshot = $listingRef->snapshot();
                if ($listingSnapshot->exists()) {
                    $listing = $listingSnapshot->data();
                    if (($listing['orderId'] ?? null) === $id
                        && strtolower((string) ($listing['status'] ?? '')) === 'ordered') {
                        $listingRef->update([
                            ['path' => 'status', 'value' => 'Available'],
                            ['path' => 'isAvailable', 'value' => true],
                            ['path' => 'orderId', 'value' => \Google\Cloud\Firestore\FieldValue::delete()],
                        ]);
                    }
                }
            }
        }

        $orderRef->update($update);
        $this->logAdminAction($id, 'Reviewed Order Cancellation', $decision === 'approve' ? 'Approved' : 'Rejected');
        return redirect()->back()->with('success', $decision === 'approve'
            ? 'Cancellation approved. The listing was released if it was still reserved by this order.'
            : 'Cancellation request rejected. The order remains active.');
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'orderStatus' => ['required', 'string', 'max:150'],
        ]);

        $status = $validated['orderStatus'];
        $database = $this->initFirebase();

        $updatePayload = [
            ['path' => 'orderStatus', 'value' => $status],
        ];

        // Record lifecycle timestamps whenever the order status advances.
        $normalizedStatus = strtolower(trim($status));
        if ($normalizedStatus === 'dispatched') {
            $updatePayload[] = [
                'path' => 'dispatchedAt',
                'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp(),
            ];
        }
        if (in_array($normalizedStatus, ['delivered', 'completed'], true)) {
            $updatePayload[] = [
                'path' => 'deliveredAt',
                'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp(),
            ];
        }

        // Business rule: Delivered/Completed means the seller has collected COD.
        if (in_array($normalizedStatus, ['delivered', 'completed'], true)) {
            $updatePayload[] = ['path' => 'paymentStatus', 'value' => 'Paid'];
            $updatePayload[] = [
                'path' => 'paidAt',
                'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp(),
            ];
        }

        $database->collection('orders')->document($id)->update($updatePayload);

        $this->logAdminAction($id, 'Updated Order/Delivery Status', $status);
        return redirect()->back()->with('success', 'Delivery and order status updated successfully.');
    }

    public function scheduleCvoInspection(Request $request, $id)
    {
        $validated = $request->validate([
            'inspectionDate' => ['required', 'date'],
            'inspectorName' => ['required', 'string', 'max:150'],
            'inspectionNotes' => ['nullable', 'string', 'max:1000'],
        ]);
        $database = $this->initFirebase();
        $database->collection('orders')->document($id)->update([
            ['path' => 'inspectionStatus', 'value' => 'Scheduled'],
            ['path' => 'inspectionDate', 'value' => $validated['inspectionDate']],
            ['path' => 'inspectorName', 'value' => $validated['inspectorName']],
            ['path' => 'inspectionNotes', 'value' => $validated['inspectionNotes'] ?? ''],
            ['path' => 'inspectionScheduledAt', 'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp()],
            ['path' => 'orderStatus', 'value' => 'CVO Inspection Scheduled'],
        ]);
        $this->logAdminAction($id, 'Scheduled CVO On-Site Inspection', $validated['inspectionDate']);
        return redirect()->back()->with('success', 'CVO on-site inspection scheduled successfully.');
    }

    public function recordCvoInspectionResult(Request $request, $id)
    {
        $validated = $request->validate([
            'inspectionResult' => ['required', 'in:approved,rejected,reinspection'],
            'inspectionNotes' => ['nullable', 'string', 'max:1000'],
            'expectedFulfillmentDate' => ['required_if:inspectionResult,approved', 'nullable', 'date', 'after_or_equal:today'],
        ]);
        $result = $validated['inspectionResult'];
        $status = $result === 'approved' ? 'CVO Inspection Approved' : ($result === 'rejected' ? 'CVO Inspection Rejected' : 'For CVO Reinspection');
        $database = $this->initFirebase();

        $updatePayload = [
            ['path' => 'inspectionStatus', 'value' => ucfirst($result)],
            ['path' => 'inspectionResult', 'value' => ucfirst($result)],
            ['path' => 'inspectionResultNotes', 'value' => $validated['inspectionNotes'] ?? ''],
            ['path' => 'inspectionSubmittedAt', 'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp()],
            ['path' => 'inspectionCompletedAt', 'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp()],
            ['path' => 'orderStatus', 'value' => $result === 'approved' ? 'Ready for Dispatch/Pick Up' : $status],
        ];

        // This is the planned delivery/pickup date, not the CVO inspection visit date.
        if ($result === 'approved') {
            $updatePayload[] = ['path' => 'expectedFulfillmentDate', 'value' => $validated['expectedFulfillmentDate']];
            $updatePayload[] = ['path' => 'expectedFulfillmentDateSetAt', 'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp()];
        } else {
            $updatePayload[] = ['path' => 'expectedFulfillmentDate', 'value' => null];
        }

        $database->collection('orders')->document($id)->update($updatePayload);
        $this->logAdminAction($id, 'Recorded CVO Inspection Result', $status);
        return redirect()->back()->with('success', 'CVO inspection result recorded.');
    }

    public function payments()
    {
        $records = $this->getCollectionData('orders');
        $users = $this->getCollectionData('users');
        $usersById = [];
        foreach ($users as $user) {
            if (!empty($user['id'])) {
                $fullName = trim((string) ($user['name'] ?? $user['fullName'] ?? (($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''))));
                $usersById[$user['id']] = $fullName;
            }
        }

        foreach ($records as &$record) {
            $buyerId = (string) ($record['buyerId'] ?? $record['buyer_id'] ?? '');
            $record['buyerName'] = $record['buyerName'] ?? $record['customerName'] ?? ($usersById[$buyerId] ?? 'Unknown Buyer');
            $record['paymentMethod'] = $record['paymentMethod'] ?? $record['payment_method'] ?? 'Not specified';
            $record['amount'] = $record['amount'] ?? $record['totalAmount'] ?? $record['total'] ?? $record['itemPrice'] ?? 0;
            // Delivered/Completed means COD was collected under the confirmed business rule.
            $orderStatus = strtolower(trim((string) ($record['orderStatus'] ?? $record['status'] ?? '')));
            $explicitPaymentStatus = $record['paymentStatus'] ?? null;

            if (!empty($explicitPaymentStatus)) {
                $record['status'] = $explicitPaymentStatus;
            } elseif (in_array($orderStatus, ['delivered', 'completed'], true)) {
                // Backward compatibility for older orders that predate paymentStatus.
                $record['status'] = 'Paid';
            } else {
                $record['status'] = 'Unpaid';
            }
        }
        unset($record);

        $this->sortByTimestamp($records, 'createdAt');
        $payments = array_map(fn ($item) => (object) $item, $records);
        return view('admin.payments', compact('payments'));
    }

    public function activityLogs(Request $request)
    {
        $allLogs = $this->getCollectionData('activity_logs');
        $userRecords = $this->getCollectionData('users');

        $usersById = [];
        $usersByEmail = [];

        foreach ($userRecords as $userRecord) {
            $documentId = trim((string) ($userRecord['id'] ?? ''));
            $email = strtolower(trim((string) ($userRecord['email'] ?? '')));

            if ($documentId !== '') $usersById[$documentId] = $userRecord;
            if ($email !== '') $usersByEmail[$email] = $userRecord;
        }

        foreach ($allLogs as &$log) {
            $action = trim((string) ($log['action'] ?? 'Activity recorded'));
            $actorType = strtolower(trim((string) ($log['actorType'] ?? $log['actor_type'] ?? '')));
            $isAdminActivity = ($actorType === 'admin' || !empty($log['admin_name']) || isset($log['admin_email']));

            if ($isAdminActivity) {$log['actorType'] = 'admin';
                $log['admin_name'] = trim((string) ($log['admin_name'] ?? $log['actor_name'] ?? 'City Veterinary Office'));$log['admin_email'] = trim((string) ($log['admin_email'] ?? $log['actor_email'] ?? 'vetoffice@livestock.com'));
            } else {
                $log['actorType'] = 'user';
                $userId = trim((string) ($log['user_id'] ?? $log['userId'] ?? ''));$userEmail = strtolower(trim((string) ($log['user_email'] ?? $log['email'] ?? '')));

                $matchedUser = [];
                if ($userId !== '' && isset($usersById[$userId])) {$matchedUser = $usersById[$userId];
                } elseif ($userEmail !== '' && isset($usersByEmail[$userEmail])) {$matchedUser = $usersByEmail[$userEmail];
                }

                $resolvedName = trim((string) ($matchedUser['name'] ?? ''));
                if ($resolvedName === '' && !empty($matchedUser['firstName'])) {$resolvedName = trim($matchedUser['firstName'] . ' ' . ($matchedUser['lastName'] ?? ''));
                }

                $log['user_name'] = trim((string) ($resolvedName !== '' ?$resolvedName : ($log['user_name'] ?? $log['name'] ?? 'Registered User')));
            }

            $targetId = trim((string) ($log['target_user_id'] ?? ''));$log['target_display_id'] = $targetId !== '' ? '#' . (strlen($targetId) > 12 ? substr($targetId, 0, 12) . '...' :$targetId) : null;
            $log['action'] = $action !== '' ?$action : 'Activity recorded';
        }
        unset($log);

        $this->sortByTimestamp($allLogs, 'timestamp');

        $perPage = 10;
        $currentPage = Paginator::resolveCurrentPage('page');$currentItems = array_slice($allLogs, ($currentPage - 1) * $perPage,$perPage);

        $activityLogs = new LengthAwarePaginator(
            array_map(fn ($item) => (object) $item,$currentItems),
            count($allLogs),
            $perPage,$currentPage,
            ['path' => $request->url(), 'query' =>$request->query()]
        );

        return view('admin.logs', compact('activityLogs'));
    }

    public function supportTickets()
    {
        $tickets = $this->getCollectionData('support_tickets');
        $isSuperAdmin = auth()->user()?->role === 'super_admin';

        // Technical Bug / Error tickets are visible only to Super Admin.
        if (!$isSuperAdmin) {
            $tickets = array_values(array_filter($tickets, fn ($ticket) => !$this->isTechnicalSupportTicket($ticket)));
        }

        // Queue open tickets first, oldest submission first; resolved tickets follow.
        usort($tickets, function ($a, $b) {
            $openA = strtolower((string) ($a['status'] ?? 'open')) === 'open';
            $openB = strtolower((string) ($b['status'] ?? 'open')) === 'open';
            if ($openA !== $openB) return $openA ? -1 : 1;
            $timeA = $this->timestampValue($a['createdAt'] ?? null);
            $timeB = $this->timestampValue($b['createdAt'] ?? null);
            if ($timeA === 0 && $timeB !== 0) return 1;
            if ($timeB === 0 && $timeA !== 0) return -1;
            return $timeA <=> $timeB;
        });

        $tickets = array_map(fn ($item) => (object) $item, $tickets);
        return view('admin.support', compact('tickets', 'isSuperAdmin'));
    }

    public function resolveSupportTicket(Request $request, $id)
    {
        $validated = $request->validate([
            'resolutionMessage' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'resolutionMessage.required' => 'Please enter a response before resolving this ticket.',
            'resolutionMessage.min' => 'The response must contain at least 5 characters.',
        ]);

        $database = $this->initFirebase();
        $ticketRef = $database->collection('support_tickets')->document($id);
        $snapshot = $ticketRef->snapshot();

        if (!$snapshot->exists()) {
            abort(404, 'Support ticket not found.');
        }

        $ticket = $snapshot->data() ?? [];
        if ($this->isTechnicalSupportTicket($ticket)) {
            $this->ensureSuperAdmin();
        }

        $currentStatus = strtolower(trim((string) ($ticket['status'] ?? 'open')));
        if ($currentStatus === 'resolved') {
            return redirect()->back()->with('success', 'This support ticket has already been resolved.');
        }

        $admin = auth()->user();
        $resolutionMessage = trim($validated['resolutionMessage']);
        $resolvedBy = $admin->name ?? $admin->email ?? 'City Veterinary Office';

        $ticketRef->update([
            ['path' => 'status', 'value' => 'Resolved'],
            ['path' => 'resolutionMessage', 'value' => $resolutionMessage],
            ['path' => 'resolvedAt', 'value' => \Google\Cloud\Firestore\FieldValue::serverTimestamp()],
            ['path' => 'resolvedBy', 'value' => $resolvedBy],
        ]);

        // Notify the ticket owner in the app and, when an FCM token exists, by push notification.
        $userId = trim((string) ($ticket['userId'] ?? $ticket['user_id'] ?? $ticket['uid'] ?? ''));
        if ($userId !== '') {
            $notificationBody = 'Your support ticket "' . ($ticket['subject'] ?? 'Support request') . '" has been resolved. Admin response: ' . $resolutionMessage;
            $this->sendFirestoreNotification(
                $userId,
                'Support Ticket Resolved',
                $notificationBody,
                'support_reply'
            );
        } else {
            Log::warning('Support ticket resolved without a user ID; notification skipped.', ['ticket_id' => $id]);
        }

        $this->logAdminAction($id, 'Resolved Support Ticket', 'Resolved');

        return redirect()->back()->with(
            'success',
            'Support ticket resolved. The response has been saved and the user has been notified when a valid user ID was available.'
        );
    }

    public function updateStatus(Request $request,$id)
    {
        $validated =$request->validate([
            'status' => ['required', 'in:pending,verified,rejected,unverified'],
            'rejectionReason' => ['nullable', 'string', 'max:500'],
        ]);

        $status =$validated['status'];
        $rejectionReason =$request->input('rejectionReason');

        $database = $this->initFirebase();$updatePayload = [
            ['path' => 'verificationStatus', 'value' => $status],
        ];

        if (($status === 'rejected' || $status === 'unverified') &&$rejectionReason) {
            $updatePayload[] = ['path' => 'rejectionReason', 'value' =>$rejectionReason];
        }

        $database->collection('users')->document($id)->update($updatePayload);

        // Record User Passed Verification in activity_logs
        if ($status === 'verified') {
            try {
                $userSnapshot = $database->collection('users')->document($id)->snapshot();
                $userData =$userSnapshot->exists() ? $userSnapshot->data() : [];$userName = $userData['name'] ?? $userData['fullName'] ?? 'Registered User';
                $userEmail =$userData['email'] ?? '';

                $database->collection('activity_logs')->add([
                    'actorType' => 'user',
                    'user_id' => $id,
                    'user_email' => $userEmail,
                    'user_name' => $userName,
                    'action' => 'Passed account verification',
                    'details' => 'User account verified by City Veterinary Office',
                    'timestamp' => \Google\Cloud\Firestore\FieldValue::serverTimestamp(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to log verification milestone: ' . $e->getMessage());
            }
        }

        $title = ucfirst($status) == 'Verified' ? 'Account Verified! 🎉' : 'Verification Update ⚠️';
        $body = ucfirst($status) == 'Verified' 
            ? 'Your seller account has been verified by the City Veterinary Office. You can now post listings.' 
            : 'Your account verification status was updated to: ' . ucfirst($status) . ($rejectionReason ? ". Reason: $rejectionReason" : '');
        
        $this->sendFirestoreNotification($id, $title,$body, 'system');

        $action =$status === 'verified'
            ? 'Approved Seller Verification'
            : ($status === 'rejected' ||$status === 'unverified' ? 'Rejected/Revoked Seller Verification' : 'Reset Seller Verification');

        $this->logAdminAction($id, $action,$status);
        return redirect()->back()->with('success', 'User verification status updated successfully.');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $admin = auth()->user();

        if (!$admin) {
            return redirect()->route('login')->withErrors([
                'email' => 'Please log in again to update your settings.',
            ]);
        }

        $admin->name =$request->input('name');
        $admin->email =$request->input('email');

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->input('password'));
        }

        $admin->save();
        return redirect()->back()->with('success', 'Admin profile settings updated successfully.');
    }

    private function logAdminAction(string $targetId, string $actionType, string$statusValue): void
    {
        try {
            $database = $this->initFirebase();$admin = auth()->user();

            $adminName = $admin ? ($admin->name ?? 'City Veterinary Office') : 'City Veterinary Office';
            $adminEmail = $admin ? ($admin->email ?? 'vetoffice@livestock.com') : 'vetoffice@livestock.com';

            $database->collection('activity_logs')->add([
                'admin_name' => $adminName,
                'admin_email' => $adminEmail,
                'actorType' => 'admin',
                'action' => "{$actionType} — Target ID: {$targetId} (Status: {$statusValue})",
                'target_user_id' => $targetId,
                'timestamp' => \Google\Cloud\Firestore\FieldValue::serverTimestamp(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to record admin activity.', [
                'target_id' => $targetId,
                'action' => $actionType,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function ensureSuperAdmin()
    {
        $user = auth()->user();
        if (!$user ||$user->role !== 'super_admin') {
            abort(403, 'Unauthorized action. This section is restricted to Super Administrators only.');
        }
    }

    public function manageAdmins()
    {
        $this->ensureSuperAdmin();$admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
        return view('admin.admins', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated =$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,super_admin'],
        ]);

        $newAdmin = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $this->logAdminAction((string) $newAdmin->id, 'Created Administrator Account',$validated['role']);

        return redirect()->route('admin.admins')->with('success', 'New administrator account successfully created.');
    }

    public function deleteAdmin($id)
    {
        $this->ensureSuperAdmin();

        $admin = \App\Models\User::findOrFail($id);

        $adminEmail =$admin->email;
        $admin->delete();

        $this->logAdminAction((string) $id, 'Revoked Administrator Access',$adminEmail);

        return redirect()->route('admin.admins')->with('success', 'Administrator account successfully removed.');
    }
}