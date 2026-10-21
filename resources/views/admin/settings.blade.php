
@extends('layouts.app')

@section('title', 'Settings - Livestock Express')
@section('header_title', 'Admin Account Settings')
@section('header_subtitle', 'Manage your City Veterinary Office portal account and security.')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    {{-- Page Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0A3622] via-[#0F5132] to-[#1B5E20] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-emerald-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-3a2 2 0 00-2-2H6a2 2 0 00-2 2v3a2 2 0 002 2zm10-9V7a4 4 0 00-8 0v5"/>
                    </svg>
                    Account & security
                </span>
                <h2 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl">Portal settings</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/90">
                    Keep your administrator information accurate and protect access to the Livestock Express management portal.
                </p>
            </div>

            <div class="flex h-16 w-16 shrink-0 items-center justify-center self-start rounded-2xl border border-white/15 bg-white/10 sm:self-center">
                <svg class="h-9 w-9 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15v2m-6 4h12a2 2 0 002-2v-3a2 2 0 00-2-2H6a2 2 0 00-2 2v3zm10-9V7a4 4 0 00-8 0v5"/>
                </svg>
            </div>
        </div>
        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/10"></div>
        <div class="absolute -right-2 -top-8 h-40 w-40 rounded-full border border-white/10"></div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <p class="font-bold">Settings saved</p>
                <p class="mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-800" role="alert">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3l-8.5-14.1a2 2 0 00-3.4 0z"/>
                </svg>
                <div>
                    <p class="font-bold">Please check the following</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">

        {{-- Settings Form --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <h3 class="text-lg font-extrabold text-slate-900">Administrator information</h3>
                <p class="mt-1 text-sm text-slate-500">Update the name and email associated with your account.</p>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 p-6 sm:p-8">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-bold text-slate-700">
                        Office / Account Name
                    </label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 0114 0"/>
                        </svg>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', auth()->user()->name) }}"
                            autocomplete="name"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"
                            placeholder="Enter office or account name"
                        >
                    </div>
                    @error('name')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                        Email Address
                    </label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                        </svg>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            autocomplete="email"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"
                            placeholder="admin@example.com"
                        >
                    </div>
                    @error('email')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <div class="mb-5">
                        <h4 class="text-base font-extrabold text-slate-900">Change password</h4>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Leave both password fields blank if you do not want to change your current password.
                        </p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="password" class="mb-2 block text-sm font-bold text-slate-700">
                                New Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-800 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"
                                    placeholder="Enter a new password"
                                >
                                <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Show or hide new password">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zm10-3a3 3 0 100 6 3 3 0 000-6z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">
                                Confirm New Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-800 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"
                                    placeholder="Re-enter your new password"
                                >
                                <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Show or hide password confirmation">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zm10-3a3 3 0 100 6 3 3 0 000-6z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-5 text-slate-400">
                        Only save changes you intended to make.
                    </p>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0F5132] px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-emerald-900/10 transition hover:bg-[#0A3622] focus:outline-none focus:ring-4 focus:ring-emerald-700/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Account Information Sidebar --}}
        <aside class="space-y-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-[#0F5132]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-3a2 2 0 00-2-2H6a2 2 0 00-2 2v3zm10-9V7a4 4 0 00-8 0v5"/>
                    </svg>
                </div>
                <h3 class="mt-4 font-extrabold text-slate-900">Account security</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Use a strong, unique password and keep your administrator login details private.
                </p>
            </div>

            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/70 p-6">
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5-3v6a9 9 0 11-18 0V7l9-4 9 4z"/>
                    </svg>
                    <h3 class="font-extrabold">Admin reminder</h3>
                </div>
                <p class="mt-3 text-sm leading-6 text-emerald-900/80">
                    Changes to your account details do not automatically verify livestock listings or change marketplace permissions.
                </p>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const shouldShow = input.type === 'password';
        input.type = shouldShow ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(shouldShow));
    }
</script>
@endpush
