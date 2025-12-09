@extends('layouts.app')

@section('content')
<div class="px-8 py-6 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Add News</h1>
        <p class="text-gray-400">Create a new news article</p>
    </div>

    <div class="bg-[#211F27] rounded-2xl border border-pink-500/20 p-8">
        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Title Field -->
            <div class="space-y-2 px-4">
                <label class="block text-sm font-semibold text-white">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" placeholder="Enter news title" class="w-full bg-[#18161d] text-white border border-pink-500/30 rounded-lg px-4 py-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition" required value="{{ old('title') }}">
                @error('title')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image Field -->
            <div class="space-y-2 px-4">
                <label class="block text-sm font-semibold text-white">Image <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
                <div class="relative">
                    <input type="file" name="image" id="imageInput" class="w-full bg-[#18161d] text-white border border-pink-500/30 rounded-lg px-4 py-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition file:bg-pink-500 file:text-white file:border-0 file:rounded file:px-4 file:py-2 file:cursor-pointer file:font-semibold file:mr-4 hover:file:bg-pink-600" accept="image/*">
                </div>
                <p class="text-gray-400 text-xs mt-2">Max 2MB • JPG, PNG, GIF</p>
                @error('image')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div class="space-y-2 px-4">
                <label class="block text-sm font-semibold text-white">Description <span class="text-red-500">*</span></label>
                <textarea name="description" id="descriptionInput" placeholder="Enter news description" rows="6" class="w-full bg-[#18161d] text-white border border-pink-500/30 rounded-lg px-4 py-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition resize-none" required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Field -->
            <div class="space-y-2 px-4">
                <label class="block text-sm font-semibold text-white mb-3">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" class="w-full bg-[#18161d] text-white border border-pink-500/30 rounded-lg px-4 py-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition" required value="{{ old('date', now()->format('Y-m-d')) }}">
                @error('date')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-6 border-t border-pink-500/10">
                <a href="{{ route('admin.news.index') }}" class="px-6 py-3 rounded-lg font-semibold text-pink-500 border border-pink-500/50 bg-transparent hover:bg-pink-500/10 transition duration-200">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-lg font-semibold text-white bg-gradient-to-r from-pink-500 to-orange-500 hover:from-pink-600 hover:to-orange-600 transition duration-200 shadow-lg hover:shadow-pink-500/50">
                    Create News
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 