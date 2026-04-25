<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — {{ \App\Models\Setting::get('store_name','MyStore') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); }
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-center">
        @php $logo = \App\Models\Setting::get('logo'); @endphp
        @if($logo)
          <img src="{{ asset('storage/'.$logo) }}" class="h-12 mx-auto mb-3" alt="Logo">
        @else
          <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-store text-white text-2xl"></i>
          </div>
        @endif
        <h1 class="text-white text-2xl font-bold">{{ \App\Models\Setting::get('store_name','MyStore') }}</h1>
        <p class="text-indigo-200 text-sm mt-1">Admin Panel</p>
      </div>

      <!-- Form -->
      <div class="p-8">
        @if(session('success'))
          <div class="mb-4 bg-green-50 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
          @csrf
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
              <input type="email" name="email" value="{{ old('email') }}" required
                     class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror"
                     placeholder="admin@store.com">
            </div>
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <div class="relative" x-data="{ show: false }">
              <i class="fas fa-lock absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
              <input :type="show ? 'text' : 'password'" name="password" required
                     class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                     placeholder="••••••••">
              <button type="button" @click="show=!show" class="absolute right-3.5 top-3.5 text-gray-400">
                <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
              <input type="checkbox" name="remember" class="rounded text-indigo-600"> Remember me
            </label>
          </div>

          <button type="submit"
                  class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 rounded-xl hover:opacity-90 transition-opacity text-sm">
            <i class="fas fa-sign-in-alt mr-2"></i> Sign In to Admin
          </button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-400">
          <a href="{{ route('store.home') }}" class="text-indigo-600 hover:underline">← Back to Store</a>
        </p>
      </div>
    </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
