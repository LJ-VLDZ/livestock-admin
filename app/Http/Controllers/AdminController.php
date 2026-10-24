<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Connect to Firestore directly via REST transport
     * with explicit environment credentials to prevent Windows gRPC crashes.
     */
    private function initFirebase()
    {
        $credentialsPath = base_path('firebase-credentials.json');

        if (!file_exists($credentialsPath)) {
            throw new \Exception('firebase-credentials.json not found in project root directory.');
        }

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
        $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] =$credentialsPath;
        $_SERVER['GOOGLE_APPLICATION_CREDENTIALS'] =$credentialsPath;

        return new \Google\Cloud\Firestore\FirestoreClient([
            'projectId'   => env('FIREBASE_PROJECT_ID', 'livestock-express-new-2026'),
            'keyFilePath' => $credentialsPath,
            'transport'   => 'rest'
        ]);
    }

    private function timestampValue($value): int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_object($value) && method_exists($value, 'get')) {
            try {
                $value =$value->get();

                if ($value instanceof \DateTimeInterface) {
                    return $value->getTimestamp();
                }
            } catch (\Throwable $e) {
                return 0;
            }
        }

        if (is_numeric($value)) {
            $number = (float)$value;

            if ($number > 9999999999) {$number /= 1000;
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
            $database = $this->initFirebase();$documents = $database->collection($collection)->documents();

            $records = [];

            foreach ($documents as$document) {
                if (!$document->exists()) {
                    continue;
                }

                $data =$document->data();

                if (!is_array($data)) {
                    continue;
                }

                $data['id'] =$document->id();
                $records[] =$data;
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

    private function normalizeUser(array $data, string$id): array
    {
        $firstName = trim((string) ($data['firstName'] ?? ''));
        $lastName = trim((string) ($data['lastName'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {$name = trim($firstName . ' ' .$lastName);
        }

        $data['id'] =$id;
        $data['name'] = $name !== '' ?$name : 'Unnamed User';
        $data['firstName'] =$firstName;
        $data['lastName'] =$lastName;
        $data['email'] = $data['email'] ?? '';$data['phone'] = $data['phone'] ?? '';$data['barangay'] = $data['barangay'] ?? '';$data['city'] = $data['city'] ?? '';$data['idType'] = $data['idType'] ?? '';$data['idFrontUrl'] = $data['idFrontUrl'] ?? $data['idUrl'] ?? null;
        $data['idBackUrl'] =$data['idBackUrl'] ?? null;
        $data['verificationStatus'] = strtolower(
            trim((string) ($data['verificationStatus'] ?? 'pending'))
        );

        return $data;
    }

    private function sortByTimestamp(array &$records, string$field): void
    {
        usort($records, function ($a, $b) use ($field) {
            $timeA =$this->timestampValue($a[$field] ?? null);
            $timeB =$this->timestampValue($b[$field] ?? null);

            return $timeB <=>$timeA;
        });
    }

    public function dashboard()
    {
        $usersCount = 0;
        $totalListings = 0;
        $totalRevenue = 0;
        $transactionsCount = 0;
        $pendingDeliveriesCount = 0;
        $recentListings = [];$recentActivities = [];
        $monthlySales = array_fill(0, 12, 0);$monthlyTransactions = array_fill(0, 12, 0);

        try {
            $usersRaw =$this->getCollectionData('users');
            foreach ($usersRaw as$user) {
                $email = strtolower(trim((string) ($user['email'] ?? '')));
                $role = strtolower(trim((string) ($user['role'] ?? 'user')));
                
                if ($role !== 'admin' && $email !== 'veterinaryadminoffice@gmail.com' && $email !== 'vetoffice@livestock.com') {
                    $usersCount++;
                }
            }

            $listingsRaw =$this->getCollectionData('livestock');
            $totalListings = count($listingsRaw);
            $this->sortByTimestamp($listingsRaw, 'createdAt');
            $recentListings = array_map(fn($item) => (object)$item, array_slice($listingsRaw, 0, 5));

            $ordersRaw =$this->getCollectionData('orders');
            $transactionsCount = count($ordersRaw);

            foreach ($ordersRaw as $order) {$amount = (float) ($order['totalAmount'] ?? $order['amount'] ?? 0);
                $totalRevenue +=$amount;

                $status = strtolower(trim((string) ($order['orderStatus'] ?? $order['status'] ?? '')));
                if (in_array($status, ['pending', 'pending verification & dispatch', 'dispatched', 'city veterinary inspection approved'], true)) {$pendingDeliveriesCount++;
                }

                $timestamp =$order['createdAt'] ?? null;
                if ($timestamp) {$timeVal = $this->timestampValue($timestamp);
                    if ($timeVal > 0) {
                        $monthIndex = (int) date('n',$timeVal) - 1;
                        if ($monthIndex >= 0 && $monthIndex < 12) {$monthlyTransactions[$monthIndex]++;$monthlySales[$monthIndex] +=$amount;
                        }
                    }
                }
            }

            $activityLogsRaw =$this->getCollectionData('activity_logs');
            $this->sortByTimestamp($activityLogsRaw, 'timestamp');
            $recentActivities = array_map(fn($item) => (object)$item, array_slice($activityLogsRaw, 0, 5));

        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error('Dashboard Firebase connection error: ' .$e->getMessage());
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
        $users =$this->getCollectionData('users');

        $pendingUsers = [];$verifiedUsers = [];
        $rejectedUsers = [];$unverifiedUsers = []; 

        foreach ($users as$user) {
            $email = strtolower(trim((string) ($user['email'] ?? '')));
            $role = strtolower(trim((string) ($user['role'] ?? 'user')));

            if ($role === 'admin' || $email === 'veterinaryadminoffice@gmail.com' \vert{}\vert{} $email === 'vetoffice@livestock.com') {
                continue;
            }

            $user =$this->normalizeUser($user,$user['id']);
            $status =$user['verificationStatus'];

            $hasSubmittedId = !empty($user['idFrontUrl']) \vert{}\vert{} !empty($user['idUrl']) || (!empty($user['idType']) &&$user['idType'] !== 'N/A');

            if ($status === 'verified') {
                $verifiedUsers[] = (object)$user;
            } elseif ($status === 'rejected') {
                $rejectedUsers[] = (object)$user;
            } elseif ($status === 'pending' || ($hasSubmittedId &&$status !== 'unverified')) {
                $pendingUsers[] = (object)$user;
            } else {
                $unverifiedUsers[] = (object)$user;
            }
        }

        return view('admin.verifications', compact(
            'pendingUsers',
            'verifiedUsers',
            'rejectedUsers',
            'unverifiedUsers'
        ));
    }

    /**
     * Step 1: Check uniqueness of email/phone, generate OTP, and send via Resend.
     */
    public function sendRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required',
            'name' => 'required',
        ]);

        $email = strtolower(trim($request->email));
        $phone = trim($request->phone);

        // Check if Email or Phone number already exists in Firestore users collection
        try {
            $database =$this->initFirebase();
            $usersRef =$database->collection('users')->documents();

            foreach ($usersRef as$doc) {
                if (!$doc->exists()) continue;
                $userData =$doc->data();
                
                $existingEmail = strtolower(trim($userData['email'] ?? ''));
                $existingPhone = trim($userData['phone'] ?? '');

                if ($existingEmail ===$email) {
                    return response()->json([
                        'message' => 'This email address is already bound to another account.'
                    ], 400);
                }

                if ($existingPhone !== '' &&$existingPhone === $phone) { return response()->json([ 'message' => 'This mobile number is already bound to another account.' ], 400); } } } catch (\Throwable$e) {
            Log::error('Duplicate user check failed: ' . $e->getMessage());
        }

        $otpCode = rand(100000, 999999);

        // Store registration payload and code in Laravel Cache for 10 minutes
        Cache::put('otp_' . $email, [
            'otp' => $otpCode,
            'data' => $request->all()
        ], now()->addMinutes(10));

        try {
            Mail::to($email)->send(new SendOtpMail($otpCode));

            return response()->json([
                'message' => 'OTP sent successfully to your email address.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Resend OTP failure: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 2: Verify the 6-digit code entered by the user.
     */
    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ]);

        $email = strtolower(trim($request->email));
        $cachedData = Cache::get('otp_' . $email);

        if (!$cachedData || (string)$cachedData['otp'] !== (string)$request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP code. Please try again.'
            ], 400);
        }

        Cache::forget('otp_' . $email);

        return response()->json([
            'message' => 'OTP verified successfully!',
            'registration_data' => $cachedData['data']
        ], 200);
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
        ]);

        $statusInput = trim($validated['status']);
        $statusLower = strtolower($statusInput);
        
        $database = $this->initFirebase();
        $database->collection('livestock')->document($id)->update([
            ['path' => 'status', 'value' => $statusLower === 'available' ? 'available' : $statusInput],
            ['path' => 'isAvailable', 'value' => ($statusLower === 'available' || $statusLower === 'approved')],
        ]);

        $action = ($statusLower === 'available' || $statusLower === 'approved')
            ? 'Approved Livestock Listing'
            : ($statusLower === 'rejected' ? 'Rejected Livestock Listing' : 'Set Livestock Listing Pending');

        $this->logAdminAction($id, $action, $statusInput);

        return redirect()->back()->with('success', 'Livestock listing status updated successfully.');
    }

    public function deleteListing($id)
    {
        $database = $this->initFirebase();
        $database->collection('livestock')->document($id)->delete();
        $this->logAdminAction($id, 'Deleted Livestock Listing', 'Removed permanently');
        return redirect()->route('admin.listings')->with('success', 'Livestock listing successfully deleted.');
    }

    public function transactions()
    {
        $ordersRaw = $this->getCollectionData('orders');
        $this->sortByTimestamp($ordersRaw, 'createdAt');

        $transactions = [];
        $totalRevenue = 0;
        $completedCount = 0;
        $pendingCount = 0;

        foreach ($ordersRaw as $order) {
            $transactions[] = (object) $order;
            $status = strtolower(trim((string) ($order['orderStatus'] ?? '')));
            
            if (in_array($status, ['completed', 'delivered'], true)) {$totalRevenue += (float) ($order['totalAmount'] ?? 0);$completedCount++;
            } elseif (in_array($status, ['pending', 'pending verification & dispatch', 'city veterinary inspection approved', 'dispatched'], true)) {$pendingCount++;
            }
        }

        return view('admin.transactions', compact('transactions', 'totalRevenue', 'completedCount', 'pendingCount'));
    }

    public function deliveries()
    {
        $records = $this->getCollectionData('orders');$deliveries = [];

        foreach ($records as $item) {$item['orderStatus'] = $item['orderStatus'] ?? $item['status'] ?? 'Pending';
            $deliveries[] = (object)$item;
        }

        $this->sortByTimestamp($records, 'createdAt');
        return view('admin.deliveries', compact('deliveries'));
    }

    public function updateOrderStatus(Request $request,$id)
    {
        $validated =$request->validate([
            'orderStatus' => [
                'required',
                'string',
                'max:100',
                'in:Pending,Pending Verification & Dispatch,City Veterinary Inspection Approved,Dispatched,Delivered,Completed,Cancelled',
            ],
        ]);

        $status =$validated['orderStatus'];
        $database =$this->initFirebase();
        $database->collection('orders')->document($id)->update([
            ['path' => 'orderStatus', 'value' => $status],
        ]);

        $this->logAdminAction($id, 'Updated Order/Delivery Status',$status);
        return redirect()->back()->with('success', 'Delivery and order status updated successfully.');
    }

    public function payments()
    {
        $records =$this->getCollectionData('orders');
        $this->sortByTimestamp($records, 'createdAt');
        $payments = array_map(fn ($item) => (object) $item,$records);
        return view('admin.payments', compact('payments'));
    }

    public function activityLogs(Request $request)
    {
        $allLogs =$this->getCollectionData('activity_logs');
        $userRecords =$this->getCollectionData('users');

        $usersById = [];$usersByEmail = [];

        foreach ($userRecords as$userRecord) {
            $documentId = trim((string) ($userRecord['id'] ?? ''));
            $email = strtolower(trim((string) ($userRecord['email'] ?? '')));

            if ($documentId !== '')$usersById[$documentId] =$userRecord;
            if ($email !== '')$usersByEmail[$email] =$userRecord;
        }

        foreach ($allLogs as &$log) {$action = trim((string) ($log['action'] ?? 'Activity recorded'));$actorType = strtolower(trim((string) ($log['actorType'] ?? $log['actor_type'] ?? '')));
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
        ]);

        $status =$validated['status'];
        $database =$this->initFirebase();
        $database->collection('users')->document($id)->update([
            ['path' => 'verificationStatus', 'value' => $status],
        ]);

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
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to record admin activity.', [
                'target_id' => $targetId,
                'action' => $actionType,
                'message' => $e->getMessage(),
            ]);
        }
    }
}