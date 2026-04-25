@extends('admin.layouts.app')
@section('title','Edit Page: '.$page->title)
@section('page-title','Edit Page: '.$page->title)
@section('content')
<div class="py-4 max-w-4xl">
<form method="POST" action="{{ route('admin.pages.update',$page) }}">
  @csrf @method('PUT')
  <div class="space-y-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Page Title</label>
        <input type="text" name="title" value="{{ old('title',$page->title) }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO Title</label>
          <input type="text" name="meta_title" value="{{ old('meta_title',$page->meta_title) }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO Description</label>
          <input type="text" name="meta_description" value="{{ old('meta_description',$page->meta_description) }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Content</label>
        <textarea name="content" id="content" rows="20"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono">{{ old('content',$page->content) }}</textarea>
      </div>
    </div>
    <div class="flex gap-3">
      <a href="{{ route('admin.pages.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-medium transition">← Back</a>
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
        <i class="fas fa-save mr-1"></i> Save Changes
      </button>
      <a href="{{ route('store.page',$page->slug) }}" target="_blank" class="bg-gray-100 text-gray-600 px-5 py-2.5 rounded-xl text-sm font-medium transition hover:bg-gray-200">
        <i class="fas fa-eye mr-1"></i> Preview
      </a>
    </div>
  </div>
</form>
</div>
@endsection
