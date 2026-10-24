<x-layout title="Masuk">
    <div class="mx-auto max-w-md">
        <x-logo variant="full" class="mx-auto mb-6 w-36" />
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Masuk ke KalaLuang</h1>

        <form method="POST" action="{{ route('login') }}" class="card mt-5 space-y-4 p-5">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" required autofocus
                       value="{{ old('email') }}" autocomplete="email">
            </div>

            <div>
                <div class="flex items-baseline justify-between">
                    <label for="password" class="label">Password</label>
                    <a href="{{ route('password.request') }}"
                       class="text-sm font-semibold text-brand-700 hover:underline">Lupa password?</a>
                </div>
                <input id="password" name="password" type="password" class="input" required
                       autocomplete="current-password">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" value="1"
                       class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                Ingat saya
            </label>

            <button type="submit" class="btn-primary w-full">Masuk</button>

            <p class="text-center text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">Daftar</a>
            </p>
        </form>
    </div>
</x-layout>
