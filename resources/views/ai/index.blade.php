@extends('layouts.app')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="bg-indigo-600 dark:bg-indigo-800 text-white px-6 py-4 text-lg font-medium">
            AI Text Generation
        </div>

        <div class="p-6">
            <form id="aiForm">

                <!-- Step 1: Prompt -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium">1. Write your prompt</label>
                        <div id="modeToggle"
                            class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 p-0.5 bg-gray-100 dark:bg-gray-900">
                            <button type="button" data-mode="freeform"
                                class="mode-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-gray-600 dark:text-gray-300">
                                ✏️ Free-form
                            </button>
                            <button type="button" data-mode="template"
                                class="mode-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-gray-600 dark:text-gray-300">
                                📋 Guided
                            </button>
                        </div>
                    </div>

                    <!-- Free-form textarea -->
                    <textarea id="prompt" name="prompt" rows="4" placeholder="Describe what you want the AI to write..."
                        class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg focus:outline-none focus:ring focus:border-blue-300"></textarea>

                    <!-- Guided template builder -->
                    <div id="templateBuilder" class="hidden">
                        <div id="templateChips" class="flex flex-wrap gap-2 mb-4"></div>
                        <div id="templateFields"
                            class="space-y-3 p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg mb-3">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">
                                Prompt preview — edit if you like
                            </label>
                            <textarea id="templatePreview" rows="4"
                                placeholder="Choose a template above — your prompt will build itself here."
                                class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg focus:outline-none focus:ring focus:border-blue-300"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Output options -->
                <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-3">2. Output options</label>

                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" id="compareMode" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Compare multiple models side-by-side</span>
                    </label>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 ml-6">See how each model responds to the same
                        prompt, with response time for each.</p>

                    <div id="providerOptions" class="hidden mt-3 pl-6">
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-2">Models to compare:</label>
                        <div class="flex flex-wrap items-center gap-4">
                            <label
                                class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox" class="provider-check rounded text-indigo-600 focus:ring-indigo-500"
                                    value="gemini" checked> Gemini
                            </label>
                            <label
                                class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox" class="provider-check rounded text-indigo-600 focus:ring-indigo-500"
                                    value="groq"> Groq
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" id="submitBtn"
                        class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-800 focus:outline-none focus:ring focus:ring-indigo-200 transition-colors">
                        Generate Text
                    </button>
                    <button type="button" id="clearBtn"
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm">
                        Clear
                    </button>
                </div>

                @guest
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">
                        💡 <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Create
                            a free account</a> to save your generation history.
                    </p>
                @endguest
            </form>

            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <h5 id="resultLabel" class="text-lg font-medium text-gray-700 dark:text-gray-300">Result:</h5>
                    <div id="resultActions" class="hidden items-center gap-2">
                        <button type="button" id="copyBtn"
                            class="text-xs px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">📋
                            Copy</button>
                        <button type="button" id="downloadBtn"
                            class="text-xs px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">⬇️
                            Download</button>
                    </div>
                </div>
                <div id="result"
                    class="p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg min-h-20 prose prose-sm dark:prose-invert max-w-none text-gray-800 dark:text-gray-200">
                    <span class="text-gray-400 dark:text-gray-500 italic not-prose">Your generated text will appear
                        here...</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let templatesData = {};
        let activeTemplate = null;
        let currentMode = 'freeform';
        let lastResultText = '';

        function loadingDots() {
            return `<div class="flex items-center gap-1 py-1 not-prose">
                <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>`;
        }

        function resetResult() {
            $('#result').html('<span class="text-gray-400 dark:text-gray-500 italic not-prose">Your generated text will appear here...</span>');
            $('#resultActions').addClass('hidden').removeClass('flex');
            lastResultText = '';
        }

        function downloadText(filename, text) {
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            URL.revokeObjectURL(url);
        }

        // --- Mode switching ---
        function setMode(mode) {
            currentMode = mode;

            $('.mode-btn').each(function () {
                const isActive = $(this).data('mode') === mode;
                $(this)
                    .toggleClass('bg-indigo-600 text-white shadow-sm', isActive)
                    .toggleClass('text-gray-600 dark:text-gray-300', !isActive);
            });

            $('#prompt').toggleClass('hidden', mode !== 'freeform');
            $('#templateBuilder').toggleClass('hidden', mode !== 'template');
        }

        function getActivePrompt() {
            return currentMode === 'freeform'
                ? $('#prompt').val().trim()
                : $('#templatePreview').val().trim();
        }

        // --- Templates ---
        fetch('{{ route("ai.templates") }}')
            .then(r => r.json())
            .then(data => {
                templatesData = data.templates;
                renderChips();
            });

        function renderChips() {
            const $chips = $('#templateChips');
            $chips.empty();
            Object.entries(templatesData).forEach(([key, t]) => $chips.append(chipButton(key, t.icon, t.name)));
        }

        function chipButton(key, icon, name) {
            const isActive = activeTemplate === key;
            const classes = isActive
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-indigo-400';
            return $(`<button type="button" class="chip px-3 py-1.5 rounded-full text-sm border transition-colors ${classes}">${icon} ${name}</button>`)
                .on('click', () => selectTemplate(key));
        }

        function selectTemplate(key) {
            activeTemplate = key;
            renderChips();

            const $fields = $('#templateFields').empty();

            if (!key) {
                $fields.html('<p class="text-sm text-gray-400 dark:text-gray-500 italic">Choose a template above to get started.</p>');
                $('#templatePreview').val('').attr('placeholder', 'Choose a template above — your prompt will build itself here.');
                return;
            }

            templatesData[key].fields.forEach(field => $fields.append(renderField(field)));
            $('#templatePreview').attr('placeholder', 'Fill in the fields above — your prompt will build itself here.');
            regeneratePreview();
        }

        function renderField(field) {
            const id = `field_${field.name}`;
            const base = 'w-full px-3 py-2 border dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring focus:border-blue-300';
            let input;

            if (field.type === 'select') {
                input = `<select id="${id}" data-field="${field.name}" class="${base}">${field.options.map(o => `<option value="${o}">${o}</option>`).join('')}</select>`;
            } else if (field.type === 'textarea') {
                input = `<textarea id="${id}" data-field="${field.name}" rows="3" class="${base}" placeholder="${field.placeholder ?? ''}"></textarea>`;
            } else {
                input = `<input type="text" id="${id}" data-field="${field.name}" class="${base}" placeholder="${field.placeholder ?? ''}">`;
            }

            return `<div><label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">${field.label}${field.required ? ' <span class="text-red-500">*</span>' : ''}</label>${input}</div>`;
        }

        // Builds the prompt from the current field values. Returns null if a required field is still empty.
        function buildPromptFromTemplate(key) {
            const tpl = templatesData[key];
            if (!tpl) return null;

            let hasAllRequired = true;
            let result = tpl.template;

            tpl.fields.forEach(field => {
                const value = ($(`#field_${field.name}`).val() || '').trim();
                if (field.required && !value) hasAllRequired = false;
                result = result.split(`{${field.name}}`).join(value);
            });

            return hasAllRequired ? result : null;
        }

        function regeneratePreview() {
            if (!activeTemplate) return;
            const built = buildPromptFromTemplate(activeTemplate);
            $('#templatePreview').val(built ?? '');
        }

        $(document).ready(function () {

            setMode('freeform');

            $('.mode-btn').on('click', function () {
                setMode($(this).data('mode'));
            });

            // Live preview — any field change rebuilds the prompt instantly, no server round-trip
            $('#templateFields').on('input change', 'input, select, textarea', regeneratePreview);

            // Compare Mode toggling
            function updateCompareUI() {
                const isCompare = $('#compareMode').is(':checked');
                $('#providerOptions').toggleClass('hidden', !isCompare);
                $('#submitBtn').text(isCompare ? 'Compare Models' : 'Generate Text');
                $('#resultLabel').text(isCompare ? 'Results:' : 'Result:');
            }
            $('#compareMode').on('change', updateCompareUI);
            updateCompareUI();

            // Clear
            $('#clearBtn').on('click', function () {
                $('#prompt').val('');
                selectTemplate(null);
                setMode('freeform');
                resetResult();
            });

            // Copy / Download (single mode)
            $('#copyBtn').on('click', async function () {
                try {
                    await navigator.clipboard.writeText(lastResultText);
                    showToast('Copied to clipboard', 'success');
                } catch {
                    showToast('Copy failed', 'error');
                }
            });

            $('#downloadBtn').on('click', function () {
                downloadText('ai-generation.md', lastResultText);
            });

            $(document).on('click', '.compare-copy', async function () {
                const text = $(this).data('text');
                try {
                    await navigator.clipboard.writeText(text);
                    showToast('Copied to clipboard', 'success');
                } catch {
                    showToast('Copy failed', 'error');
                }
            });

            // Submit
            $('#aiForm').submit(async function (e) {
                e.preventDefault();

                const prompt = getActivePrompt();

                if (!prompt) {
                    showToast(
                        currentMode === 'freeform'
                            ? 'Write a prompt before generating.'
                            : 'Fill in the required fields above before generating.',
                        'error'
                    );
                    return;
                }

                if ($('#compareMode').is(':checked')) {
                    return runCompare(prompt);
                }

                const $result = $('#result');
                const $submitBtn = $('#submitBtn');

                $result.html('<span class="inline-block w-2 h-4 bg-indigo-600 dark:bg-indigo-400 animate-pulse not-prose"></span>');
                $('#resultActions').addClass('hidden').removeClass('flex');
                $submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

                let fullText = '';

                try {
                    const response = await fetch('{{ route("ai.generateStream") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'text/event-stream',
                        },
                        body: JSON.stringify({ prompt, template: currentMode === 'template' ? activeTemplate : null })
                    });

                    if (!response.ok) throw new Error('Request failed with status ' + response.status);

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';

                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;

                        buffer += decoder.decode(value, { stream: true });
                        const parts = buffer.split('\n\n');
                        buffer = parts.pop();

                        for (const part of parts) {
                            if (!part.startsWith('data: ')) continue;
                            const json = part.slice(6).trim();
                            if (!json) continue;

                            const payload = JSON.parse(json);
                            if (payload.error) throw new Error(payload.error);
                            if (payload.done) continue;

                            fullText += payload.text;
                            $result.html(renderMarkdown(fullText));
                        }
                    }

                    if (!fullText) {
                        resetResult();
                    } else {
                        lastResultText = fullText;
                        $('#resultActions').removeClass('hidden').addClass('flex');
                    }
                } catch (err) {
                    $result.html('<div class="text-red-600 dark:text-red-400 not-prose">Error: ' + escapeHtml(err.message) + '</div>');
                    showToast(err.message, 'error');
                } finally {
                    $submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            });

            async function runCompare(prompt) {
                const providers = $('.provider-check:checked').map((_, el) => el.value).get();
                const $result = $('#result');
                const $submitBtn = $('#submitBtn');

                if (providers.length === 0) {
                    showToast('Select at least one provider.', 'error');
                    return;
                }

                $result.html(loadingDots());
                $('#resultActions').addClass('hidden').removeClass('flex');
                $submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

                try {
                    const response = await fetch('{{ route("ai.compare") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        body: JSON.stringify({ prompt, providers, template: currentMode === 'template' ? activeTemplate : null })
                    });

                    if (!response.ok) throw new Error('Request failed with status ' + response.status);

                    const data = await response.json();
                    const cards = Object.entries(data.results).map(([name, r]) => {
                        if (!r.success) {
                            return `<div class="border dark:border-gray-700 rounded-lg p-4 not-prose">
                                <div class="font-semibold capitalize dark:text-white mb-2">${escapeHtml(name)}</div>
                                <div class="text-red-600 dark:text-red-400 text-sm">Error: ${escapeHtml(r.error)}</div>
                            </div>`;
                        }
                        return `<div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2 not-prose">
                                <span class="font-semibold capitalize dark:text-white">${escapeHtml(name)}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">${r.latency_ms}ms</span>
                                    <button type="button" class="compare-copy text-xs px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300" data-text="${escapeHtml(r.text)}">📋</button>
                                </div>
                            </div>
                            <div class="prose prose-sm dark:prose-invert max-w-none">${renderMarkdown(r.text)}</div>
                        </div>`;
                    }).join('');

                    $result.html(`<div class="grid grid-cols-1 md:grid-cols-2 gap-4 not-prose">${cards}</div>`);
                } catch (err) {
                    $result.html('<div class="text-red-600 dark:text-red-400 not-prose">Error: ' + escapeHtml(err.message) + '</div>');
                    showToast(err.message, 'error');
                } finally {
                    $submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            }
        });
    </script>
@endsection