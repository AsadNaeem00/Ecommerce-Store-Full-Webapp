@extends('admin.layouts.app')
@section('title','Payment Setup')
@section('page-title','Payment Setup')

@section('content')
<div class="py-4 space-y-6">

<div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex gap-3">
  <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
  <p class="text-sm text-blue-700">Configure your payment gateways below. Credentials are <strong>encrypted in the database</strong>. Enable test mode during development and switch to live before going live.</p>
</div>

@php
  $gatewayMeta = [
    'cod'       => ['label'=>'Cash on Delivery', 'icon'=>'fas fa-money-bill-wave', 'color'=>'green',  'description'=>'Customers pay when they receive their order. No integration needed.'],
    'easypaisa' => ['label'=>'EasyPaisa',         'icon'=>'fas fa-mobile-alt',      'color'=>'emerald','description'=>'Pakistan\'s leading mobile payment platform. Supports MA (Mobile Account) and OTC payments.'],
    'jazzcash'  => ['label'=>'JazzCash',           'icon'=>'fas fa-mobile-alt',      'color'=>'red',    'description'=>'JazzCash mobile wallet and card payments. Hosted checkout page for maximum compatibility.'],
    'card'      => ['label'=>'Visa / Mastercard',  'icon'=>'fas fa-credit-card',     'color'=>'indigo', 'description'=>'Accept international card payments via Stripe. Requires a Stripe account registered in Pakistan.'],
  ];
@endphp

@foreach($gatewayMeta as $gateway => $meta)
@php $config = $configs[$gateway] ?? null; @endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ open: {{ $config && $config->is_enabled ? 'true' : 'false' }} }">
  {{-- Header --}}
  <div class="px-6 py-5 flex items-center gap-4 cursor-pointer select-none" @click="open=!open">
    <div class="w-12 h-12 bg-{{ $meta['color'] }}-100 rounded-xl flex items-center justify-center">
      <i class="{{ $meta['icon'] }} text-{{ $meta['color'] }}-600 text-lg"></i>
    </div>
    <div class="flex-1">
      <div class="flex items-center gap-3">
        <h3 class="font-semibold text-gray-800">{{ $meta['label'] }}</h3>
        @if($config && $config->is_enabled)
          <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-0.5 rounded-full">Enabled</span>
        @else
          <span class="bg-gray-100 text-gray-500 text-xs font-medium px-2.5 py-0.5 rounded-full">Disabled</span>
        @endif
        @if($config && $config->is_enabled && $config->is_test_mode)
          <span class="bg-amber-100 text-amber-700 text-xs font-medium px-2.5 py-0.5 rounded-full">Test Mode</span>
        @endif
      </div>
      <p class="text-sm text-gray-500 mt-0.5">{{ $meta['description'] }}</p>
    </div>
    <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
  </div>

  {{-- Form --}}
  <div x-show="open" x-cloak class="border-t border-gray-100 px-6 py-5">
    <form method="POST" action="{{ route('admin.payments.update', $gateway) }}" class="space-y-5">
      @csrf @method('PUT')

      {{-- Enable + Test Mode --}}
      <div class="flex gap-6 flex-wrap">
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" name="is_enabled" value="1" {{ $config && $config->is_enabled ? 'checked' : '' }}
                 class="w-4 h-4 rounded text-indigo-600">
          <span class="font-medium">Enable {{ $meta['label'] }}</span>
        </label>
        @if($gateway !== 'cod')
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" name="is_test_mode" value="1" {{ $config && $config->is_test_mode ? 'checked' : '' }}
                 class="w-4 h-4 rounded text-amber-500">
          <span class="font-medium text-amber-700">Test / Sandbox Mode</span>
        </label>
        @endif
      </div>

      @if($gateway === 'easypaisa')
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Store / Merchant ID <span class="text-red-500">*</span></label>
          <input type="text" name="merchant_id" value="{{ $config->merchant_id ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. 12345">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Store Password (API Key)</label>
          <input type="password" name="api_key" placeholder="Leave blank to keep existing"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Hash / Integrity Key</label>
          <input type="password" name="hash_key" value="{{ $config->extra_config['hash_key'] ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Account Number</label>
          <input type="text" name="account_number" value="{{ $config->extra_config['account_number'] ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="03XX-XXXXXXX">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Return / Callback URL</label>
          <input type="text" name="return_url" value="{{ $config->extra_config['return_url'] ?? route('store.payment.callback',['gateway'=>'easypaisa']) }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono text-xs">
          <p class="text-xs text-gray-400 mt-1">Register this URL in your EasyPaisa merchant portal</p>
        </div>
      </div>

      @elseif($gateway === 'jazzcash')
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Merchant ID <span class="text-red-500">*</span></label>
          <input type="text" name="merchant_id" value="{{ $config->merchant_id ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. MC12345">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
          <input type="password" name="api_key" placeholder="Leave blank to keep existing"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Integrity Salt (Hash Key)</label>
          <input type="password" name="hash_key" value="{{ $config->extra_config['hash_key'] ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Account Number</label>
          <input type="text" name="account_number" value="{{ $config->extra_config['account_number'] ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="03XX-XXXXXXX">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Return URL</label>
          <input type="text" name="return_url" value="{{ $config->extra_config['return_url'] ?? route('store.payment.callback',['gateway'=>'jazzcash']) }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono text-xs">
        </div>
      </div>

      @elseif($gateway === 'card')
      <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-sm text-indigo-700 mb-2">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Stripe for Pakistan:</strong> You need a Stripe account. Pakistani businesses can register via Stripe's supported partners. Supports PKR currency with VISA/Mastercard.
      </div>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Secret Key (sk_...) <span class="text-red-500">*</span></label>
          <input type="password" name="api_key" placeholder="sk_live_... or sk_test_..."
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <p class="text-xs text-gray-400 mt-1">Server-side secret key — never expose this</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Publishable Key (pk_...)</label>
          <input type="text" name="publishable_key" value="{{ $config->extra_config['publishable_key'] ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="pk_live_... or pk_test_...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Merchant ID (Stripe Account ID)</label>
          <input type="text" name="merchant_id" value="{{ $config->merchant_id ?? '' }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="acct_...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Webhook Secret (whsec_...)</label>
          <input type="password" name="webhook_secret" placeholder="whsec_..."
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <p class="text-xs text-gray-400 mt-1">Webhook URL: <span class="font-mono">{{ route('store.payment.webhook',['gateway'=>'card']) }}</span></p>
        </div>
      </div>
      @endif

      <div class="flex justify-end">
        <button type="submit"
                class="bg-{{ $meta['color'] }}-600 hover:bg-{{ $meta['color'] }}-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
          <i class="fas fa-save"></i> Save {{ $meta['label'] }} Settings
        </button>
      </div>
    </form>
  </div>
</div>
@endforeach

</div>
@endsection
