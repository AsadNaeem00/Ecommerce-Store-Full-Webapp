{{-- PAGES INDEX --}}
@extends('admin.layouts.app')
@section('title','Pages')
@section('page-title','Content Pages')
@section('content')
<div class="py-4">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="divide-y divide-gray-50">
    @foreach($pages as $page)
    <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition">
      <div class="flex-1">
        <p class="font-medium text-gray-800">{{ $page->title }}</p>
        <p class="text-sm text-gray-400 font-mono">/page/{{ $page->slug }}</p>
      </div>
      <span class="text-xs {{ $page->is_active?'text-green-600 bg-green-100':'text-gray-400 bg-gray-100' }} px-2.5 py-0.5 rounded-full font-medium">
        {{ $page->is_active?'Active':'Inactive' }}
      </span>
      <a href="{{ route('admin.pages.edit',$page) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Edit →</a>
    </div>
    @endforeach
  </div>
</div>
</div>
@endsection
