<x-layout title="Buat Password Baru">
    <div class="mx-auto max-w-md">
        <x-logo variant="full" class="mx-auto mb-6 w-36" />
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Buat password baru</h1>

        <form method="POST" action="{{ route('password.store') }}" class="card mt-5 space-y-4 p-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" required
                       value="{{ old('email', $email) }}" autocomplete="email">
            </div>

            <div>
                <label for="password" class="label">Password baru</label>
                <input id="password" name="password" type="password" class="input" required autofocus
                       autocomplete="new-password">
            </div>

            <div>
                <label for="password_confirmation" class="label">Ulangi password baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" required
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn-primary w-full">Simpan password baru</button>
        </form>
    </div>
</x-layout>
