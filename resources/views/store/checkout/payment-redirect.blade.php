{{-- PAYMENT REDIRECT (Auto POST to gateway) --}}
@extends('store.layouts.app')
@section('title','Redirecting to Payment...')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center" x-data x-init="document.getElementById('paymentForm').submit()">
  <div class="text-center">
    <div class="w-20 h-20 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin mx-auto mb-6"></div>
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Redirecting to Payment...</h2>
    <p class="text-gray-500 text-sm">Please wait. You're being securely redirected to <strong>{{ strtoupper($gateway) }}</strong>.</p>
    <p class="text-gray-400 text-xs mt-2">Do not close this window.</p>
  </div>
</div>

<form id="paymentForm" method="POST" action="{{ $redirect_url }}" class="hidden">
  @foreach($form_data as $key => $value)
    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
  @endforeach
</form>
@endsection
