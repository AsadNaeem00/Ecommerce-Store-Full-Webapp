<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Under Maintenance — {{ \App\Models\Setting::get('store_name','Store') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 to-indigo-900 flex items-center justify-center p-6 text-center">
  <div>
    <div class="w-24 h-24 bg-white/10 rounded-3xl flex items-center justify-center mx-auto mb-8">
      <i class="fas fa-tools text-white text-4xl"></i>
    </div>
    <h1 class="text-4xl font-bold text-white mb-4">We'll Be Back Soon</h1>
    <p class="text-indigo-200 text-lg mb-3 max-w-md">{{ \App\Models\Setting::get('store_name') }} is currently undergoing scheduled maintenance.</p>
    <p class="text-indigo-300 text-sm">Please check back shortly. We apologize for any inconvenience.</p>
    @if($phone = \App\Models\Setting::get('store_phone'))
      <p class="mt-6 text-indigo-200 text-sm"><i class="fas fa-phone mr-2"></i>{{ $phone }}</p>
    @endif
  </div>
</body>
</html>
