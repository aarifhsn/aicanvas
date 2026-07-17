@extends('layouts.app')

@section('heading', $document->title)
@section('subheading', "Ask a question — answers are grounded in this document's content")

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <form id="askForm" class="flex gap-3 mb-6">
                <input type="text" id="question" placeholder="Ask something about this document..." required
                    class="flex-1 px-3 py-2 border dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring focus:border-blue-300">
                <button type="submit" id="askBtn"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 whitespace-nowrap">Ask</button>
            </form>

            <div id="answer"
                class="prose prose-sm dark:prose-invert max-w-none p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg min-h-16">
                <span class="text-gray-400 dark:text-gray-500 italic not-prose">Ask a question above to get a grounded
                    answer from this document.</span>
            </div>

            <div id="sources" class="hidden mt-4">
                <h6 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Sources used:</h6>
                <div id="sourcesList" class="space-y-2"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function escapeHtml(str) {
            return $('<div>').text(str ?? '').html();
        }

        $('#askForm').submit(async function (e) {
            e.preventDefault();

            const question = $('#question').val();
            const $answer = $('#answer');
            const $btn = $('#askBtn');

            $answer.html('<div class="flex items-center gap-1 py-1 not-prose"><span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></span><span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></span><span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></span></div>');
            $('#sources').addClass('hidden');
            $btn.prop('disabled', true).addClass('opacity-50');

            try {
                const response = await fetch('{{ route("documents.ask", $document) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    body: JSON.stringify({ question })
                });

                const data = await response.json();

                if (!data.success) throw new Error(data.error || 'Something went wrong.');

                $answer.html(renderMarkdown(data.answer));

                const sourcesHtml = data.sources.map(s => `
                    <div class="text-xs border dark:border-gray-700 rounded p-2 text-gray-600 dark:text-gray-400">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Chunk ${s.index + 1}</span>
                        <span class="text-gray-400 dark:text-gray-500">(${(s.score * 100).toFixed(1)}% match)</span>
                        <p class="mt-1">${escapeHtml(s.preview)}</p>
                    </div>
                `).join('');
                $('#sourcesList').html(sourcesHtml);
                $('#sources').removeClass('hidden');
            } catch (err) {
                $answer.html('<div class="text-red-600 dark:text-red-400 not-prose">Error: ' + escapeHtml(err.message) + '</div>');
            } finally {
                $btn.prop('disabled', false).removeClass('opacity-50');
            }
        });
    </script>
@endsection