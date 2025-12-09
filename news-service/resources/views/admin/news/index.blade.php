@extends('layouts.app')

@section('content')
<div class="px-8 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">News Management</h1>
        <a href="{{ route('admin.news.create') }}" class="bg-pink-500 text-white px-6 py-2 rounded-lg hover:bg-pink-600 font-semibold">+ Add News</a>
    </div>
    <div class="mb-6 text-gray-300">Total News: <span class="font-bold text-pink-400 text-lg">{{ $total }}</span></div>
    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($news as $item)
        <div class="bg-[#211F27] rounded-xl shadow-lg border border-pink-500/20 p-5 flex flex-col h-full hover:border-pink-500/40 transition">
            @if($item->image)
                <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" class="w-full h-40 object-cover rounded-lg mb-4 border border-pink-400/30" onerror="this.src='{{ route('news.placeholder', $item->id) }}'">
            @else
                <img src="{{ route('news.placeholder', $item->id) }}" alt="{{ $item->title }}" class="w-full h-40 object-cover rounded-lg mb-4 border border-pink-400/30">
            @endif
            <h2 class="text-lg font-bold text-white mb-2">{{ $item->title }}</h2>
            <p class="text-gray-300 text-sm mb-3 line-clamp-3 flex-grow">{{ $item->description }}</p>
            <div class="flex items-center text-xs text-gray-400 mb-3">
                <i class="fi fi-rr-calendar mr-2"></i> {{ Carbon\Carbon::parse($item->date)->format('d M Y') }}
            </div>
            <div class="flex items-center gap-4 text-pink-400 mb-4 text-sm">
                <span class="flex items-center"><i class="fi fi-rr-heart mr-1"></i> {{ $item->likes }}</span>
                <span class="flex items-center"><i class="fi fi-rr-eye mr-1"></i> {{ $item->views }}</span>
                <span class="ml-auto text-xs text-gray-500">{{ $item->creator->name ?? 'Anonymous' }}</span>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="{{ route('admin.news.edit', $item->id) }}" class="flex-1 bg-gradient-to-r from-pink-500 to-orange-500 text-white py-2 rounded-lg text-center text-sm font-semibold hover:from-pink-600 hover:to-orange-600 transition">Edit</a>
                <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full border border-pink-500 text-pink-500 py-2 rounded-lg text-sm font-semibold bg-transparent hover:bg-pink-500/10 transition" onclick="return confirm('Delete this news?')">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @if($news->count() === 0)
        <div class="text-center py-12">
            <p class="text-gray-400 text-lg mb-4">No news found</p>
            <a href="{{ route('admin.news.create') }}" class="inline-block bg-pink-500 text-white px-6 py-2 rounded-lg hover:bg-pink-600">Create First News</a>
        </div>
    @endif
    <div class="mt-8">{{ $news->links() }}</div>
</div>
@endsection 