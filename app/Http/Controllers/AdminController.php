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

    private function sortByTimestamp(array &$records, string $field): void
    {
        usort($records, function ($a, $b) use ($field) {
            $timeA = $this->timestampValue($a[$field] ?? null);
            $timeB = $this->timestampValue($b[$field] ?? null);
            return $timeB <=> $timeA;
        });
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
        $deliveries = [];

        foreach ($records as $item) {
            $item['orderStatus'] = $item['orderStatus'] ?? $item['status'] ?? 'Pending';
            $deliveries[] = (object) $item;
        }

        $this->sortByTimestamp($records, 'createdAt');
        return view('admin.deliveries', compact('deliveries'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'orderStatus' => ['required', 'string', 'max:150'],
        ]);

        $status = $validated['orderStatus'];
        $database = $this->initFirebase();
        $database->collection('orders')->document($id)->update([
            ['path' => 'orderStatus', 'value' => $status],
        ]);

        $this->logAdminAction($id, 'Updated Order/Delivery Status', $status);
        return redirect()->back()->with('success', 'Delivery and order status updated successfully.');
    }

    public function payments()
    {
        $records = $this->getCollectionData('orders');
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
        $tickets =$this->getCollectionData('support_tickets');
        $this->sortByTimestamp($tickets, 'createdAt');
        $tickets = array_map(fn ($item) => (object) $item,$tickets);
        return view('admin.support', compact('tickets'));
    }

    public function resolveSupportTicket($id)
    {
        $database =$this->initFirebase();
        $database->collection('support_tickets')->document($id)->update([
            ['path' => 'status', 'value' => 'Resolved'],
        ]);

        $this->logAdminAction($id, 'Resolved Support Ticket', 'Resolved');
        return redirect()->back()->with('success', 'Support ticket marked as resolved successfully.');
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