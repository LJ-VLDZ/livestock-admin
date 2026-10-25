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
     * 1. Receive Flutter sign-up request and store temporarily without touching Firebase yet.
     */
    public function registerPending(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:pending_users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
            'barangay' => 'required|string',
            'birthday' => 'required|date',
            'age' => 'required|integer|min:18',
        ]);

        $token = Str::random(64);

        $pending = PendingUser::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'barangay' => $validated['barangay'],
            'birthday' => $validated['birthday'],
            'age' => $validated['age'],
            'token' => $token,
            'expires_at' => now()->addMinutes(30),
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

            // Authenticate with Google OAuth2 for Firebase REST Admin API
            $auth = new \Google\Auth\OAuth2([
                'audience' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
                'issuer' => $jsonKey['client_email'],
                'signingAlgorithm' => 'RS256',
                'signingKey' => $jsonKey['private_key'],
                'tokenCredentialUri' => $jsonKey['token_uri'],
            ]);

            $auth->fetchAuthToken();
            $authToken = $auth->getAccessToken();

            // Create user in Firebase Authentication
            $client = new \GuzzleHttp\Client();
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

            if (!$uid) {
                throw new \Exception('Failed to generate Firebase Auth UID.');
            }

            // Save user profile details to Firestore
            $firestore = new FirestoreClient([
                'projectId'   => $projectId,
                'keyFilePath' => $credentialsPath,
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
                'verificationStatus' => 'verified',
                'role' => 'user',
                'birthday' => $pending->birthday,
                'age' => (int) $pending->age,
                'createdAt' => time(),
            ]);

            // Clean up temporary pending record
            $pending->delete();

            return view('auth.success_verified');

        } catch (\Throwable $e) {
            return view('auth.error', ['message' => $e->getMessage()]);
        }
    }
}