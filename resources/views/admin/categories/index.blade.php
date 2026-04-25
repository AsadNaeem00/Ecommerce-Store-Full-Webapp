@extends('admin.layouts.app')
@section('title','Categories')
@section('page-title','Categories')

@section('content')
<div class="py-4">
<div class="grid lg:grid-cols-5 gap-6">

  {{-- Add Form --}}
  <div class="lg:col-span-2">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sticky top-24">
      <h3 class="font-semibold text-gray-800 mb-4">Add New Category</h3>
      <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Category Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Men's Clothing">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Category</label>
          <select name="parent_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">None (Top Level)</option>
            @foreach($categories as $cat)
              @if(!$cat->parent_id)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endif
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
          <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Category Image</label>
          <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs">
        </div>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" checked class="rounded text-indigo-600"> Active
          </label>
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="show_in_nav" value="1" checked class="rounded text-indigo-600"> Show in Nav
          </label>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-medium transition">
          <i class="fas fa-plus mr-1"></i> Create Category
        </button>
      </form>
    </div>
  </div>

  {{-- Categories List --}}
  <div class="lg:col-span-3">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-semibold text-gray-800">All Categories</h3>
        <span class="text-sm text-gray-400">{{ $categories->total() }} total</span>
      </div>
      <div class="divide-y divide-gray-50">
        @forelse($categories as $category)
        <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
          <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
            @if($category->image)
              <img src="{{ asset('storage/'.$category->image) }}" class="w-full h-full object-cover">
            @else
              <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fas fa-image"></i></div>
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="font-medium text-gray-800">{{ $category->name }}</p>
              <span class="text-xs font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">#{{ $category->category_code }}</span>
              @if(!$category->is_active)<span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Inactive</span>@endif
            </div>
            <p class="text-xs text-gray-400 mt-0.5">{{ $category->products_count }} products
              @if($category->parent) · Sub of {{ $category->parent->name }}@endif
            </p>
          </div>
          <div class="flex gap-1">
            <button onclick="openEdit({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->is_active?'true':'false' }}, {{ $category->show_in_nav?'true':'false' }})"
                    class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
              <i class="fas fa-edit text-sm"></i>
            </button>
            <form method="POST" action="{{ route('admin.categories.destroy',$category) }}" onsubmit="return confirm('Delete this category?')">
              @csrf @method('DELETE')
              <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                <i class="fas fa-trash text-sm"></i>
              </button>
            </form>
          </div>
        </div>
        @empty
          <p class="px-6 py-12 text-center text-gray-400">No categories yet. Add your first category.</p>
        @endforelse
      </div>
      @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $categories->links() }}</div>
      @endif
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-semibold text-gray-800">Edit Category</h3>
      <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" id="editForm" enctype="multipart/form-data" class="space-y-4">
      @csrf @method('PUT')
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
        <input type="text" id="editName" name="name" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Image (optional)</label>
        <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs">
      </div>
      <div class="flex gap-4">
        <label class="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" id="editActive" name="is_active" value="1" class="rounded text-indigo-600"> Active
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" id="editNav" name="show_in_nav" value="1" class="rounded text-indigo-600"> Show in Nav
        </label>
      </div>
      <div class="flex gap-2">
        <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-xl text-sm font-medium">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-medium">Save Changes</button>
      </div>
    </form>
  </div>
</div>
</div>

@push('scripts')
<script>
function openEdit(id, name, active, nav) {
  document.getElementById('editForm').action = `/admin/categories/${id}`;
  document.getElementById('editName').value = name;
  document.getElementById('editActive').checked = active;
  document.getElementById('editNav').checked = nav;
  document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
