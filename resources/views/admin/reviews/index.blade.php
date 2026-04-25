@extends('admin.layouts.app')
@section('title','Reviews')
@section('page-title','Reviews')
@section('content')
<div class="py-4 space-y-4">
<div class="flex gap-2 mb-4">
  @foreach([''=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$label)
    <a href="{{ route('admin.reviews.index', $val ? ['status'=>$val] : []) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ request('status')===$val ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
      {{ $label }}
    </a>
  @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="divide-y divide-gray-50">
    @forelse($reviews as $review)
    <div class="p-5 flex gap-4">
      <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
        {{ strtoupper(substr($review->reviewer_name,0,1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap gap-2 items-start justify-between mb-1">
          <div>
            <span class="font-semibold text-gray-800">{{ $review->reviewer_name }}</span>
            <span class="text-gray-400 text-sm ml-2">{{ $review->reviewer_email }}</span>
          </div>
          <div class="flex items-center gap-2">
            @php $sc=['pending'=>'yellow','approved'=>'green','rejected'=>'red']; $c=$sc[$review->status]??'gray'; @endphp
            <span class="bg-{{ $c }}-100 text-{{ $c }}-700 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ ucfirst($review->status) }}</span>
            <span class="text-xs text-gray-400">{{ $review->created_at->format('M d, Y') }}</span>
          </div>
        </div>
        <div class="flex gap-0.5 mb-1">
          @for($i=1;$i<=5;$i++)<i class="fas fa-star text-{{ $i<=$review->rating?'amber':'gray' }}-400 text-xs"></i>@endfor
        </div>
        @if($review->title)<p class="font-medium text-gray-700 text-sm">{{ $review->title }}</p>@endif
        <p class="text-gray-600 text-sm mt-0.5">{{ $review->body }}</p>
        <p class="text-xs text-gray-400 mt-1">Product: <a href="{{ route('admin.products.edit', $review->product_id) }}" class="text-indigo-600 hover:underline">{{ $review->product->name ?? 'Deleted' }}</a></p>
      </div>
      <div class="flex flex-col gap-1 flex-shrink-0">
        @if($review->status !== 'approved')
          <form method="POST" action="{{ route('admin.reviews.approve',$review) }}">@csrf @method('PUT')
            <button class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg text-xs font-medium transition w-full"><i class="fas fa-check mr-1"></i>Approve</button>
          </form>
        @endif
        @if($review->status !== 'rejected')
          <form method="POST" action="{{ route('admin.reviews.reject',$review) }}">@csrf @method('PUT')
            <button class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-medium transition w-full"><i class="fas fa-times mr-1"></i>Reject</button>
          </form>
        @endif
        <form method="POST" action="{{ route('admin.reviews.destroy',$review) }}" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')
          <button class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-medium transition w-full"><i class="fas fa-trash mr-1"></i>Delete</button>
        </form>
      </div>
    </div>
    @empty
      <p class="px-6 py-12 text-center text-gray-400"><i class="fas fa-star text-4xl text-gray-200 mb-3 block"></i>No reviews found.</p>
    @endforelse
  </div>
  @if($reviews->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $reviews->links() }}</div>
  @endif
</div>
</div>
@endsection
