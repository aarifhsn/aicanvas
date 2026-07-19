<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\DocumentProcessor;
use App\Services\AI\EmbeddingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    protected int $maxChunks = 40;

    public function index(Request $request)
    {
        $documents = $request->user()->documents()->latest()->get();

        return view('documents.index', compact('documents'));
    }

    public function show(Document $document)
    {
        abort_unless($document->user_id === auth()->id(), 403);

        return view('documents.show', compact('document'));
    }

    public function store(Request $request, DocumentProcessor $processor, EmbeddingService $embeddings)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt|max:5120', // 5MB
        ]);

        $file = $request->file('file');

        try {
            $text = $processor->extractText($file);
            $chunks = $processor->chunk($text);

            if (empty($chunks)) {
                return back()->withErrors(['file' => 'No readable text found in that file.']);
            }

            if (count($chunks) > $this->maxChunks) {
                $chunks = array_slice($chunks, 0, $this->maxChunks);
            }

            $document = Document::create([
                'user_id' => $request->user()->id,
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_filename' => $file->getClientOriginalName(),
                'chunk_count' => count($chunks),
            ]);

            foreach ($chunks as $index => $content) {
                $vector = $embeddings->embed($content, 'RETRIEVAL_DOCUMENT');

                DocumentChunk::create([
                    'document_id' => $document->id,
                    'chunk_index' => $index,
                    'content' => $content,
                    'embedding' => $vector,
                ]);
            }

            return redirect()->route('documents.show', $document)
                ->with('status', 'Document processed — ' . count($chunks) . ' chunks embedded.');
        } catch (\Exception $e) {

            return back()->withErrors(['file' => 'Failed to process document: ' . $e->getMessage()]);
        }
    }

    public function ask(Request $request, Document $document, EmbeddingService $embeddings, AIProviderInterface $ai)
    {
        abort_unless($document->user_id === auth()->id(), 403);

        $request->validate(['question' => 'required|string|max:500']);

        $questionVector = $embeddings->embed($request->question, 'RETRIEVAL_QUERY');

        $scored = $document->chunks->map(function ($chunk) use ($embeddings, $questionVector) {
            return [
                'chunk' => $chunk,
                'score' => $embeddings->cosineSimilarity($questionVector, $chunk->embedding),
            ];
        })->sortByDesc('score')->take(4)->values();

        $context = $scored->map(fn($item) => $item['chunk']->content)->implode("\n\n---\n\n");

        $prompt = <<<PROMPT
Answer the question using ONLY the context below. If the answer isn't in the context, say "I couldn't find that in the document."

Context:
{$context}

Question: {$request->question}
PROMPT;

        try {
            $answer = $ai->generate($prompt);

            return response()->json([
                'success' => true,
                'answer' => $answer,
                'sources' => $scored->map(fn($item) => [
                    'index' => $item['chunk']->chunk_index,
                    'preview' => Str::limit($item['chunk']->content, 150),
                    'score' => round($item['score'], 3),
                ]),
            ]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Document $document)
    {
        abort_unless($document->user_id === auth()->id(), 403);

        $document->delete(); // chunks cascade via FK

        return redirect()->route('documents.index')->with('status', 'Document deleted.');
    }
}