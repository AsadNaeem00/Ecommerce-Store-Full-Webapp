{{-- CONTACT PAGE --}}
@extends('store.layouts.app')
@section('title','Contact Us — '.\App\Models\Setting::get('store_name'))
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center mb-12">
    <h1 class="text-4xl font-bold text-gray-900">Get In Touch</h1>
    <p class="text-gray-500 mt-3 text-lg">We'd love to hear from you. We're here to help!</p>
  </div>

  <div class="grid md:grid-cols-5 gap-10">
    {{-- Contact Info --}}
    <div class="md:col-span-2 space-y-6">
      @foreach([
        ['fas fa-phone','Phone',\App\Models\Setting::get('store_phone',''),'tel:'.preg_replace('/\s/','',(string)\App\Models\Setting::get('store_phone',''))],
        ['fas fa-envelope','Email',\App\Models\Setting::get('store_email',''),'mailto:'.\App\Models\Setting::get('store_email','')],
        ['fas fa-map-marker-alt','Address',\App\Models\Setting::get('store_address',''),'#'],
      ] as $c)
      <div class="flex gap-4">
        <div class="w-11 h-11 bg-primary/10 rounded-2xl flex items-center justify-center flex-shrink-0">
          <i class="{{ $c[0] }} text-primary text-sm"></i>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $c[1] }}</p>
          <a href="{{ $c[3] }}" class="text-gray-800 font-medium hover:text-primary transition text-sm">{{ $c[2] }}</a>
        </div>
      </div>
      @endforeach

      @if($wa = \App\Models\Setting::get('whatsapp_number'))
      <a href="https://wa.me/{{ preg_replace('/\D/','', $wa) }}" target="_blank"
         class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-2xl p-4 hover:bg-green-100 transition">
        <i class="fab fa-whatsapp text-green-500 text-2xl"></i>
        <div>
          <p class="font-semibold text-green-800 text-sm">Chat on WhatsApp</p>
          <p class="text-xs text-green-600">Quick replies, usually within minutes</p>
        </div>
      </a>
      @endif
    </div>

    {{-- Contact Form --}}
    <div class="md:col-span-3 bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
      <form method="POST" action="{{ route('store.contact.send') }}" class="space-y-5">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Your Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('name') border-red-400 @enderror"
                   placeholder="John Doe">
            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('email') border-red-400 @enderror"
                   placeholder="you@example.com">
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
          <input type="text" name="subject" value="{{ old('subject') }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary"
                 placeholder="What's this about?">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Message <span class="text-red-500">*</span></label>
          <textarea name="message" rows="5" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('message') border-red-400 @enderror"
                    placeholder="Tell us how we can help you...">{{ old('message') }}</textarea>
          @error('message')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full btn-primary py-3.5 rounded-2xl font-semibold flex items-center justify-center gap-2">
          <i class="fas fa-paper-plane"></i> Send Message
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
