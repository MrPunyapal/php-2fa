<x-guest-layout>
    <div class="max-w-md mx-auto mt-8 p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Two-Factor Verification</h2>

        <p class="text-gray-600 mb-6">
            Enter the code from your authenticator app, or use a recovery code.
        </p>

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf

            <div class="mb-4">
                <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    autofocus
                    autocomplete="one-time-code"
                    inputmode="numeric"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    placeholder="000000 or recovery code"
                />

                @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                Verify
            </button>
        </form>
    </div>
</x-guest-layout>
