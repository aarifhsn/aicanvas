@extends('layouts.app')

@section('heading', 'Your History')
@section('subheading', 'Past generations, saved automatically while signed in')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="p-6">
        @if ($generations->isEmpty())
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                No generations yet — go make something on the <a href="{{ route('ai.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Generator</a> page.
            </p>
        @else
            <div class="space-y-4">
                @foreach ($generations as $generation)
                    <div class="border dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2 gap-2 flex-wrap">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 capitalize">{{ $generation->provider }}</span>
                                @if ($generation->template_key)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $generation->template_key }}</span>
                                @endif
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $generation->created_at->diffForHumans() }}</span>
                            </div>
                            <form method="POST" action="{{ route('history.destroy', $generation) }}" onsubmit="return confirm('Delete this generation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ \Illuminate\Support\Str::limit($generation->prompt, 120) }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($generation->result, 300) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $generations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection