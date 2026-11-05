<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PendingUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Google\Cloud\Firestore\FirestoreClient;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Find user in the local database and verify admin role.
        $user = User::where('email', $credentials['email'])->first();

        if (
            !$user ||
            !Hash::check($credentials['password'], $user->password) ||
            !in_array($user->role, ['admin', 'super_admin'], true)
        ) {
            return back()
                ->withErrors([
                    'email' => 'Invalid administrator credentials or unauthorized role.'
                ])
                ->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Record administrator login activity.
        try {
            $credentialsPath = base_path('firebase-credentials.json');

            if (file_exists($credentialsPath)) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

                $database = new FirestoreClient([
                    'projectId' => env(
                        'FIREBASE_PROJECT_ID',
                        'livestock-express-new-2026'
                    ),
                    'keyFilePath' => $credentialsPath,
                    'transport' => 'rest'
                ]);

                $roleTitle = $user->role === 'super_admin'
                    ? 'Super Administrator'
                    : 'Standard Administrator';

                $database->collection('activity_logs')->add([
                    'admin_name' => $user->name,
                    'admin_email' => $user->email,
                    'actorType' => 'admin',
                    'action' => "Administrator Login — {$roleTitle} logged into the portal",
                    'target_user_id' => (string) $user->id,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'Failed to record admin login activity: ' . $e->getMessage()
            );
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Receive Flutter sign-up request,
     * clear previous pending registrations,
     * and temporarily store registration information.
     */
    public function registerPending(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'password' => 'required|string|min:8',
                'barangay' => 'required|string',
                'birthday' => 'required|date',
                'age' => 'required|integer|min:18',
            ]);

            // Remove previous pending registration for the same email.
            PendingUser::where('email', $validated['email'])->delete();

            $token = Str::random(64);

            $pending = PendingUser::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'barangay' => $validated['barangay'],
                'birthday' => $validated['birthday'],
                'age' => $validated['age'],
                'token' => $token,
                'expires_at' => now()->addMinutes(15),
            ]);

            $verifyUrl = route('verify.registration', [
                'token' => $token
            ]);

            Mail::send(
                'emails.verify-registration',
                [
                    'user' => $pending,
                    'verifyUrl' => $verifyUrl
                ],
                function ($message) use ($pending) {
                    $message->to($pending->email)
                        ->subject('Verify your Livestock Express Account');
                }
            );

            return response()->json([
                'message' => 'Pending registration created. Verification email sent.'
            ], 200);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'Pending registration error: ' . $e->getMessage()
            );

            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Triggered when the user clicks the email verification link.
     * Creates the Firebase account and Firestore user profile.
     * Records successful user registration in activity_logs.
     */
    public function verifyRegistration(Request $request)
    {
        $token = $request->query('token');

        $pending = PendingUser::where('token', $token)->first();

        if (!$pending || now()->greaterThan($pending->expires_at)) {
            return view('auth.expired');
        }

        try {
            $credentialsPath = base_path('firebase-credentials.json');

            if (!file_exists($credentialsPath)) {
                throw new \Exception(
                    'firebase-credentials.json not found.'
                );
            }

            $jsonKey = json_decode(
                file_get_contents($credentialsPath),
                true
            );

            $projectId = $jsonKey['project_id']
                ?? env('FIREBASE_PROJECT_ID', 'livestock-express-new-2026');

            $clientEmail = $jsonKey['client_email'];
            $privateKey = $jsonKey['private_key'];
            $tokenUri = $jsonKey['token_uri'];

            // Generate Google OAuth JWT.
            $now = time();

            $header = json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT'
            ]);

            $payload = json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/identitytoolkit',
                'aud' => $tokenUri,
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64UrlHeader = str_replace(
                ['+', '/', '='],
                ['-', '_', ''],
                base64_encode($header)
            );

            $base64UrlPayload = str_replace(
                ['+', '/', '='],
                ['-', '_', ''],
                base64_encode($payload)
            );

            $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

            $signature = '';

            openssl_sign(
                $signatureInput,
                $signature,
                $privateKey,
                OPENSSL_ALGO_SHA256
            );

            $base64UrlSignature = str_replace(
                ['+', '/', '='],
                ['-', '_', ''],
                base64_encode($signature)
            );

            $jwt = $signatureInput . "." . $base64UrlSignature;

            $client = new \GuzzleHttp\Client();

            // Obtain Google OAuth access token.
            $tokenResponse = $client->post($tokenUri, [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            $tokenData = json_decode(
                $tokenResponse->getBody()->getContents(),
                true
            );

            $authToken = $tokenData['access_token'] ?? null;

            if (!$authToken) {
                throw new \Exception(
                    'Failed to generate Google OAuth access token.'
                );
            }

            $uid = null;

            // Create Firebase Authentication account.
            try {
                $response = $client->post(
                    "https://identitytoolkit.googleapis.com/v1/projects/{$projectId}/accounts",
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $authToken,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'email' => $pending->email,
                            'password' => $pending->password,
                            'displayName' => $pending->name,
                            'emailVerified' => true,
                        ],
                    ]
                );

                $result = json_decode(
                    $response->getBody()->getContents(),
                    true
                );

                $uid = $result['localId'] ?? null;

            } catch (\GuzzleHttp\Exception\ClientException $e) {

                $errorResponse = json_decode(
                    $e->getResponse()->getBody()->getContents(),
                    true
                );

                $errorCode = $errorResponse['error']['message'] ?? '';

                if ($errorCode === 'EMAIL_EXISTS') {

                    // Retrieve existing Firebase UID.
                    $lookupResponse = $client->post(
                        "https://identitytoolkit.googleapis.com/v1/projects/{$projectId}/accounts:lookup",
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $authToken,
                                'Content-Type' => 'application/json',
                            ],
                            'json' => [
                                'email' => [$pending->email],
                            ],
                        ]
                    );

                    $lookupResult = json_decode(
                        $lookupResponse->getBody()->getContents(),
                        true
                    );

                    $uid = $lookupResult['users'][0]['localId'] ?? null;

                } else {
                    throw $e;
                }
            }

            if (!$uid) {
                throw new \Exception(
                    'Failed to obtain Firebase Auth UID.'
                );
            }

            // Initialize Firestore.
            putenv(
                'GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath
            );

            $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $credentialsPath;

            $firestore = new FirestoreClient([
                'projectId' => $projectId,
                'keyFile' => json_decode(
                    file_get_contents($credentialsPath),
                    true
                ),
                'transport' => 'rest'
            ]);

            // Save user profile in Firestore.
            $firestore->collection('users')
                ->document($uid)
                ->set([
                    'uid' => $uid,
                    'name' => $pending->name,
                    'email' => $pending->email,
                    'phone' => $pending->phone,
                    'barangay' => $pending->barangay,
                    'city' => 'Alaminos City',
                    'authType' => 'email',
                    'emailVerified' => true,
                    'verificationStatus' => 'unverified',
                    'role' => 'user',
                    'birthday' => $pending->birthday,
                    'age' => (int) $pending->age,
                    'createdAt' => time(),
                ], ['merge' => true]);

            // ==========================================
            // NEW: RECORD USER REGISTRATION ACTIVITY
            // ==========================================
            try {
                $firestore->collection('activity_logs')->add([
                    'actorType' => 'user',
                    'user_id' => $uid,
                    'user_email' => $pending->email,
                    'user_name' => $pending->name,
                    'target_user_id' => $uid,
                    'action' => 'New user registered',
                    'details' => 'User completed email verification and successfully created a Livestock Express account.',
                    'timestamp' => now()->toIso8601String(),
                ]);

            } catch (\Throwable $logException) {
                // Activity logging must not interrupt successful registration.
                \Illuminate\Support\Facades\Log::error(
                    'Failed to record user registration activity for UID '
                    . $uid . ': ' . $logException->getMessage()
                );
            }

            // Delete pending registration after successful account creation.
            $pending->delete();

            return view('auth.success_verified');

        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error(
                'Firebase registration verification failed: ' . $e->getMessage()
            );

            return view('auth.error', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
