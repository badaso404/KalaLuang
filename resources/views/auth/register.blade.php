<x-layout title="Daftar">
    <div class="mx-auto max-w-md">
        <x-logo variant="full" class="mx-auto mb-6 w-36" />
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Buat akun KalaLuang</h1>
        <p class="mt-1 text-sm text-slate-600">Pilih peran Anda terlebih dahulu.</p>

        <form method="POST" action="{{ route('register') }}" class="card mt-5 space-y-4 p-5">
            @csrf

            <fieldset>
                <legend class="label">Saya mendaftar sebagai</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([App\Enums\UserRole::JobSeeker, App\Enums\UserRole::Company] as $role)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-300 p-3 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                            <input type="radio" name="role" value="{{ $role->value }}" required
                                   @checked(old('role', $selectedRole->value) === $role->value)
                                   class="mt-0.5 size-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">{{ $role->label() }}</span>
                                <span class="block text-xs text-slate-600">
                                    {{ $role === App\Enums\UserRole::JobSeeker
                                        ? 'Gratis, untuk lulusan baru yang mencari kerja.'
                                        : 'Untuk UKM/startup yang ingin memasang lowongan.' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div>
                <label for="name" class="label">Nama lengkap</label>
                <input id="name" name="name" type="text" class="input" required autofocus
                       value="{{ old('name') }}" autocomplete="name">
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" required
                       value="{{ old('email') }}" autocomplete="email">
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" class="input" required
                       autocomplete="new-password">
            </div>

            <div>
                <label for="password_confirmation" class="label">Ulangi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" required
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn-primary w-full">Daftar</button>

            <p class="text-center text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Masuk</a>
            </p>
        </form>
    </div>
</x-layout>
