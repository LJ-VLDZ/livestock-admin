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

        $adminEmail = 'veterinaryadminoffice@gmail.com';
        $adminPassword = 'Vetoffice@2026';

        if (
            strtolower($credentials['email']) !== strtolower($adminEmail) ||
            $credentials['password'] !== $adminPassword
        ) {
            return back()
                ->withErrors(['email' => 'Invalid administrator email or password.'])
                ->onlyInput('email');
        }

        $user = User::firstOrNew(['email' => $adminEmail]);

        if (!$user->exists) {
            $user->name = 'System Administrator';
            $user->password = Hash::make($adminPassword);
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

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
     * 1. Receive Flutter sign-up request, clear previous unverified attempts, and store temporarily.
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

            // Automatically remove any unverified pending record for this email so testing is seamless
            PendingUser::where('email', $validated['email'])->delete();

            $token = Str::random(64);

            $pending = PendingUser::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'], // Keep plaintext temporarily for Firebase Auth
                'barangay' => $validated['barangay'],
                'birthday' => $validated['birthday'],
                'age' => $validated['age'],
                'token' => $token,
                'expires_at' => now()->addMinutes(15),
            ]);

            $verifyUrl = route('verify.registration', ['token' => $token]);

            // Sends email using your configured Gmail SMTP settings
            Mail::send('emails.verify-registration', ['user' => $pending, 'verifyUrl' => $verifyUrl], function ($message) use ($pending) {
                $message->to($pending->email)
                        ->subject('Verify your Livestock Express Account');
            });

            return response()->json([
                'message' => 'Pending registration created. Verification email sent.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Triggered when the user clicks the link in their email. Verifies token, creates Firebase account & Firestore profile.
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
                throw new \Exception('firebase-credentials.json not found.');
            }

            $jsonKey = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $jsonKey['project_id'] ?? env('FIREBASE_PROJECT_ID', 'livestock-express-new-2026');
            $clientEmail = $jsonKey['client_email'];
            $privateKey = $jsonKey['private_key'];
            $tokenUri = $jsonKey['token_uri'];

            // 1. Create a manually signed JWT for Google OAuth2 token endpoint
            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $payload = json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/identitytoolkit',
                'aud' => $tokenUri,
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
            $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

            $signature = '';
            openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $signatureInput . "." . $base64UrlSignature;

            // 2. Exchange JWT for Google OAuth access token
            $client = new \GuzzleHttp\Client();
            $tokenResponse = $client->post($tokenUri, [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            $tokenData = json_decode($tokenResponse->getBody()->getContents(), true);
            $authToken = $tokenData['access_token'] ?? null;

            if (!$authToken) {
                throw new \Exception('Failed to generate Google OAuth access token.');
            }

            $uid = null;

            // 3. Try to create user in Firebase Authentication
            try {
                $response = $client->post("https://identitytoolkit.googleapis.com/v1/projects/{$projectId}/accounts", [
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
                ]);

                $result = json_decode($response->getBody()->getContents(), true);
                $uid = $result['localId'] ?? null;

            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorCode = $errorResponse['error']['message'] ?? '';

                // If email already exists in Firebase, fetch the existing user's UID instead of failing
                if ($errorCode === 'EMAIL_EXISTS') {
                    $lookupResponse = $client->post("https://identitytoolkit.googleapis.com/v1/projects/{$projectId}/accounts:lookup", [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $authToken,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'email' => [$pending->email],
                        ],
                    ]);
                    $lookupResult = json_decode($lookupResponse->getBody()->getContents(), true);
                    $uid = $lookupResult['users'][0]['localId'] ?? null;
                } else {
                    throw $e;
                }
            }

            if (!$uid) {
                throw new \Exception('Failed to obtain Firebase Auth UID.');
            }

            // 4. Save user profile details to Firestore
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
            $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $credentialsPath;

            $firestore = new FirestoreClient([
                'projectId'   => $projectId,
                'keyFile'     => json_decode(file_get_contents($credentialsPath), true),
                'transport'   => 'rest'
            ]);

            $firestore->collection('users')->document($uid)->set([
                'uid' => $uid,
                'name' => $pending->name,
                'email' => $pending->email,
                'phone' => $pending->phone,
                'barangay' => $pending->barangay,
                'city' => 'Alaminos City',
                'authType' => 'email',
                'emailVerified' => true,
                'verificationStatus' => 'unverified', // <--- User can browse, but cannot buy/sell yet
                'role' => 'user',
                'birthday' => $pending->birthday,
                'age' => (int) $pending->age,
                'createdAt' => time(),
            ], ['merge' => true]);

            // 5. Clean up temporary pending record
            $pending->delete();

            return view('auth.success_verified');

        } catch (\Throwable $e) {
            return view('auth.error', ['message' => $e->getMessage()]);
        }
    }
}