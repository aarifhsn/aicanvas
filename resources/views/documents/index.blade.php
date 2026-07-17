@extends('layouts.app')

@section('heading', 'Documents')
@section('subheading', 'Upload a PDF or text file and ask questions grounded in its content')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data"
                class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                @csrf
                <input type="file" name="file" accept=".pdf,.txt" required
                    class="text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 file:text-sm">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm whitespace-nowrap">
                    Upload & Embed
                </button>
            </form>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">PDF or .txt, max 5MB. Processing runs synchronously —
                larger files take longer.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            @if ($documents->isEmpty())
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No documents yet — upload one above to get started.
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($documents as $doc)
                        <div class="flex justify-between items-center border dark:border-gray-700 rounded-lg p-4">
                            <div>
                                <a href="{{ route('documents.show', $doc) }}"
                                    class="font-medium text-gray-800 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">{{ $doc->title }}</a>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $doc->chunk_count }} chunks &middot;
                                    {{ $doc->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('documents.show', $doc) }}"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ask</a>
                                <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                    onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection