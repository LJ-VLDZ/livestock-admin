
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A3622">
    <title>Admin Login | Livestock Express</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
        }

        .login-background {
            background:
                radial-gradient(ellipse at top left, rgba(52, 211, 153, .15), transparent 40%),
                radial-gradient(ellipse at bottom right, rgba(22, 101, 52, .20), transparent 45%),
                #F4F7F4;
        }

        .login-card {
            box-shadow: 0 25px 70px rgba(10, 54, 34, .10);
        }

        .login-input:focus {
            border-color: #1B5E35;
            box-shadow: 0 0 0 4px rgba(27, 94, 53, .10);
        }
    </style>
</head>

<body class="login-background flex min-h-screen items-center justify-center px-4 py-10 text-slate-800">

    <main class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white bg-white login-card lg:grid-cols-2">

        {{-- Left Branding Panel --}}
        <section class="relative flex flex-col justify-between overflow-hidden bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E35] p-8 text-white sm:p-10 lg:min-h-[620px] lg:p-12">

            <div class="absolute -right-20 top-20 h-64 w-64 rounded-full border border-white/10"></div>
            <div class="absolute -right-10 top-32 h-44 w-44 rounded-full border border-white/10"></div>
            <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-emerald-400/10 blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-300 to-emerald-600 text-lg font-extrabold text-[#0A3622] shadow-lg shadow-black/10">
                        LE
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold tracking-tight">Livestock Express</h1>
                        <p class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-300">
                            City Vet Administration
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative z-10 my-12">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/15 bg-white/10">
                    <svg class="h-9 w-9 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3 4.5 6v5.5c0 4.5 3.2 7.5 7.5 9.5 4.3-2 7.5-5 7.5-9.5V6L12 3Z" />
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                            d="m9 12 2 2 4-4" />
                    </svg>
                </div>

                <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-300">
                    Secure Administration Portal
                </p>

                <h2 class="mt-4 max-w-md text-3xl font-extrabold leading-tight sm:text-4xl">
                    Managing a safer livestock marketplace.
                </h2>

                <p class="mt-5 max-w-md text-sm leading-7 text-emerald-50/80">
                    Access the administrative portal to review livestock listings, monitor deliveries, and manage marketplace records for Alaminos City.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-center gap-3 text-sm text-emerald-50/90">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-emerald-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        Listing and seller record management
                    </div>

                    <div class="flex items-center gap-3 text-sm text-emerald-50/90">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-emerald-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v12H3zM14 10h4l3 3v5h-7z" />
                            </svg>
                        </span>
                        Delivery and transaction monitoring
                    </div>

                    <div class="flex items-center gap-3 text-sm text-emerald-50/90">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-emerald-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        Organized administrative records
                    </div>
                </div>
            </div>

            <div class="relative z-10 border-t border-white/15 pt-5">
                <p class="text-xs leading-5 text-emerald-100/70">
                    Livestock Express · Alaminos City, Pangasinan
                </p>
            </div>
        </section>

        {{-- Login Form Panel --}}
        <section class="flex items-center justify-center p-6 sm:p-10 lg:p-12">
            <div class="w-full max-w-md">

                <div class="mb-8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-[#0F5132]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5m5 5H3" />
                        </svg>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                        Administrator Access
                    </p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">
                        Welcome back
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Sign in using your authorized administrator credentials.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                        </svg>
                        <div>
                            <p class="font-bold">Unable to sign in</p>
                            <p class="mt-1">{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                            Email address
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="admin@example.com"
                            autocomplete="username"
                            required
                            autofocus
                            class="login-input w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400">

                        @error('email')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="password" class="block text-sm font-bold text-slate-700">
                                Password
                            </label>
                        </div>

                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                                class="login-input w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400">

                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-emerald-800"
                                aria-label="Show password"
                                aria-pressed="false">
                                <svg id="showPasswordIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" stroke-width="1.8" />
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#0F5132] px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-950/10 transition hover:bg-[#146C43] focus:outline-none focus:ring-4 focus:ring-emerald-100 active:scale-[0.99]">

                        Sign In to Portal

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </button>
                </form>

                <div class="mt-8 rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3 4.5 6v5.5c0 4.5 3.2 7.5 7.5 9.5 4.3-2 7.5-5 7.5-9.5V6L12 3Z" />
                            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                        </svg>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Authorized personnel only</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Use only your assigned administrator account. Never share your password.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="mt-8 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Livestock Express
                </p>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');

            toggleButton?.addEventListener('click', function () {
                const showPassword = passwordInput.type === 'password';

                passwordInput.type = showPassword ? 'text' : 'password';
                toggleButton.setAttribute('aria-pressed', String(showPassword));
                toggleButton.setAttribute(
                    'aria-label',
                    showPassword ? 'Hide password' : 'Show password'
                );

                passwordInput.focus();
            });
        });
    </script>
</body>
</html>
