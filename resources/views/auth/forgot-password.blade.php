<x-layout title="Lupa Password">
    <div class="mx-auto max-w-md">
        <x-logo variant="full" class="mx-auto mb-6 w-36" />
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Lupa password</h1>
        <p class="mt-2 text-sm text-slate-600">
            Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat password baru.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="card mt-5 space-y-4 p-5">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" required autofocus
                       value="{{ old('email') }}" autocomplete="email">
            </div>

            <button type="submit" class="btn-primary w-full">Kirim tautan reset</button>

            <p class="text-center text-sm text-slate-600">
                Ingat password Anda?
                <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Masuk</a>
            </p>
        </form>
    </div>
</x-layout>
