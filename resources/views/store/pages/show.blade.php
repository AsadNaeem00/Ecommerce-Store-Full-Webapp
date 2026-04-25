{{-- STATIC PAGE (About, Privacy, Terms, etc.) --}}
@extends('store.layouts.app')
@section('title', $page->meta_title ?? $page->title.' — '.\App\Models\Setting::get('store_name'))
@section('meta_description', $page->meta_description ?? '')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center mb-12">
    <h1 class="text-4xl font-bold text-gray-900">{{ $page->title }}</h1>
  </div>
  <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-12">
    <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">
      {!! $page->content !!}
    </div>
  </div>
</div>
@endsection
