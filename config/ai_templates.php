<?php

return [
    'blog_post' => [
        'name' => 'Blog Post',
        'icon' => '📝',
        'description' => 'Draft a structured blog post',
        'fields' => [
            ['name' => 'topic', 'label' => 'Topic', 'type' => 'text', 'placeholder' => 'e.g. Benefits of remote work', 'required' => true],
            ['name' => 'tone', 'label' => 'Tone', 'type' => 'select', 'options' => ['Professional', 'Casual', 'Persuasive', 'Informative'], 'required' => true],
            ['name' => 'length', 'label' => 'Length', 'type' => 'select', 'options' => ['Short (~200 words)', 'Medium (~500 words)', 'Long (~1000 words)'], 'required' => true],
        ],
        'template' => 'Write a {tone} blog post about "{topic}". Target length: {length}. Include a compelling headline, an engaging intro, clear subheadings, and a short conclusion.',
    ],

    'email' => [
        'name' => 'Email',
        'icon' => '✉️',
        'description' => 'Draft a professional email',
        'fields' => [
            ['name' => 'purpose', 'label' => 'Purpose', 'type' => 'text', 'placeholder' => 'e.g. Follow up on a job application', 'required' => true],
            ['name' => 'recipient', 'label' => 'Recipient context', 'type' => 'text', 'placeholder' => 'e.g. Hiring manager at TechCorp', 'required' => false],
            ['name' => 'tone', 'label' => 'Tone', 'type' => 'select', 'options' => ['Formal', 'Friendly', 'Direct', 'Apologetic'], 'required' => true],
        ],
        'template' => 'Write a {tone} email. Purpose: {purpose}. Recipient context: {recipient}. Keep it concise with a clear subject line suggestion.',
    ],

    'product_description' => [
        'name' => 'Product Description',
        'icon' => '🛍️',
        'description' => 'Generate marketing copy for a product',
        'fields' => [
            ['name' => 'product_name', 'label' => 'Product name', 'type' => 'text', 'placeholder' => 'e.g. Wireless Noise-Cancelling Headphones', 'required' => true],
            ['name' => 'features', 'label' => 'Key features', 'type' => 'textarea', 'placeholder' => 'e.g. 30hr battery, Bluetooth 5.3, foldable', 'required' => true],
            ['name' => 'tone', 'label' => 'Tone', 'type' => 'select', 'options' => ['Premium', 'Playful', 'Minimalist', 'Technical'], 'required' => true],
        ],
        'template' => 'Write a {tone} product description for "{product_name}". Key features: {features}. Make it persuasive and suitable for an e-commerce listing.',
    ],

    'summarizer' => [
        'name' => 'Summarizer',
        'icon' => '📄',
        'description' => 'Summarize a block of text',
        'fields' => [
            ['name' => 'text', 'label' => 'Text to summarize', 'type' => 'textarea', 'placeholder' => 'Paste your text here...', 'required' => true],
            ['name' => 'length', 'label' => 'Summary length', 'type' => 'select', 'options' => ['1-2 sentences', 'Short paragraph', 'Bullet points'], 'required' => true],
        ],
        'template' => 'Summarize the following text as {length}:\n\n{text}',
    ],

    'code_explainer' => [
        'name' => 'Code Explainer',
        'icon' => '💻',
        'description' => 'Explain what a piece of code does',
        'fields' => [
            ['name' => 'code', 'label' => 'Code snippet', 'type' => 'textarea', 'placeholder' => 'Paste your code...', 'required' => true],
            ['name' => 'language', 'label' => 'Language', 'type' => 'text', 'placeholder' => 'e.g. PHP, JavaScript', 'required' => false],
        ],
        'template' => 'Explain what the following {language} code does, step by step, in plain language:\n\n{code}',
    ],

    'social_caption' => [
        'name' => 'Social Caption',
        'icon' => '📱',
        'description' => 'Write a social media caption',
        'fields' => [
            ['name' => 'topic', 'label' => 'Topic', 'type' => 'text', 'placeholder' => 'e.g. Launching a new portfolio site', 'required' => true],
            ['name' => 'platform', 'label' => 'Platform', 'type' => 'select', 'options' => ['Instagram', 'LinkedIn', 'X / Twitter', 'Facebook'], 'required' => true],
            ['name' => 'tone', 'label' => 'Tone', 'type' => 'select', 'options' => ['Excited', 'Professional', 'Witty', 'Inspirational'], 'required' => true],
        ],
        'template' => 'Write a {tone} {platform} caption about "{topic}". Include relevant hashtags where appropriate for that platform.',
    ],
];