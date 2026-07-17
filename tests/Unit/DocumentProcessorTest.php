<?php

use App\Services\AI\DocumentProcessor;
use App\Services\AI\EmbeddingService;

it('chunks text with overlap', function () {
    $processor = new DocumentProcessor();
    $chunks = $processor->chunk(str_repeat('word ', 500));

    expect($chunks)->not->toBeEmpty();
    expect(count($chunks))->toBeGreaterThan(1);
});

it('returns an empty array for blank text', function () {
    $processor = new DocumentProcessor();

    expect($processor->chunk('   '))->toBe([]);
});

it('computes cosine similarity correctly', function () {
    $embeddings = new EmbeddingService();

    expect($embeddings->cosineSimilarity([1, 0, 0], [1, 0, 0]))->toBe(1.0);
    expect($embeddings->cosineSimilarity([1, 0, 0], [0, 1, 0]))->toBe(0.0);
});