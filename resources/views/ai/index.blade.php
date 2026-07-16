<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Text Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {} }
        }

        if (localStorage.getItem('darkMode') === 'true' ||
            (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <!-- Page Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">AI Text Generator</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Generate content with artificial intelligence</p>
        </div>

        <!-- Dark Mode Toggle -->
        <div class="flex justify-end mb-4">
            <button id="darkModeToggle"
                class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white flex items-center transition-colors">
                <span id="darkModeIcon" class="mr-2">🌙</span>
                <span id="darkModeText">Dark Mode</span>
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="bg-indigo-600 dark:bg-indigo-800 text-white px-6 py-4 text-lg font-medium">
                AI Text Generation
            </div>

            <div class="p-6">
                <form id="aiForm">

                    <!-- Quick Templates -->
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Quick templates:</label>
                        <div id="templateChips" class="flex flex-wrap gap-2"></div>
                        <div id="templateFields"
                            class="hidden space-y-3 mt-3 p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
                        </div>
                    </div>

                    <!-- Prompt -->
                    <div class="mb-4">
                        <label for="prompt" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                            Enter your prompt:
                        </label>
                        <textarea
                            class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                            id="prompt" name="prompt" rows="3" required></textarea>
                    </div>

                    <!-- Compare Mode -->
                    <div class="mb-5 flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" id="compareMode"
                                class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Compare Mode</span>
                        </label>
                        <div id="providerOptions" class="hidden flex flex-wrap items-center gap-4">
                            <label
                                class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox"
                                    class="provider-check rounded text-indigo-600 focus:ring-indigo-500" value="gemini"
                                    checked> Gemini
                            </label>
                            <label
                                class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox"
                                    class="provider-check rounded text-indigo-600 focus:ring-indigo-500" value="groq">
                                Groq
                            </label>
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
                </form>

                <div class="mt-6">
                    <h5 id="resultLabel" class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Result:</h5>
                    <div id="result"
                        class="p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg min-h-20 whitespace-pre-line text-gray-800 dark:text-gray-200">
                        <span class="text-gray-400 dark:text-gray-500 italic">Your generated text will appear
                            here...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let templatesData = {};
        let activeTemplate = null;

        // --- Helpers ---
        function escapeHtml(str) {
            return $('<div>').text(str ?? '').html();
        }

        function loadingDots() {
            return `<div class="flex items-center gap-1 py-1">
                <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>`;
        }

        function resetResult() {
            $('#result').html('<span class="text-gray-400 dark:text-gray-500 italic">Your generated text will appear here...</span>');
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
            $chips.append(chipButton(null, '✏️', 'Free-form'));
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
                $fields.addClass('hidden');
                return;
            }

            templatesData[key].fields.forEach(field => $fields.append(renderField(field)));
            $fields.append(`<button type="button" id="insertPromptBtn" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md text-sm hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors">Insert into prompt</button>`);
            $fields.removeClass('hidden');

            $('#insertPromptBtn').on('click', () => insertTemplatePrompt(key));
        }

        function renderField(field) {
            const id = `field_${field.name}`;
            const base = 'w-full px-3 py-2 border dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring focus:border-blue-300';
            let input;

            if (field.type === 'select') {
                input = `<select id="${id}" class="${base}">${field.options.map(o => `<option value="${o}">${o}</option>`).join('')}</select>`;
            } else if (field.type === 'textarea') {
                input = `<textarea id="${id}" rows="3" class="${base}" placeholder="${field.placeholder ?? ''}"></textarea>`;
            } else {
                input = `<input type="text" id="${id}" class="${base}" placeholder="${field.placeholder ?? ''}">`;
            }

            return `<div><label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">${field.label}</label>${input}</div>`;
        }

        async function insertTemplatePrompt(key) {
            const fields = {};
            templatesData[key].fields.forEach(f => { fields[f.name] = $(`#field_${f.name}`).val(); });

            const response = await fetch('{{ route("ai.buildPrompt") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                body: JSON.stringify({ template: key, fields })
            });

            const data = await response.json();

            if (data.success) {
                $('#prompt').val(data.prompt).focus();
                const $btn = $('#insertPromptBtn');
                const original = $btn.text();
                $btn.text('Inserted ✓').prop('disabled', true);
                setTimeout(() => $btn.text(original).prop('disabled', false), 1200);
            } else {
                $('#result').html('<div class="text-red-600 dark:text-red-400">' + escapeHtml(data.error) + '</div>');
            }
        }

        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Dark mode
            $('#darkModeToggle').click(function () {
                const isDarkMode = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDarkMode);
                updateDarkModeButton(isDarkMode);
            });

            function updateDarkModeButton(isDarkMode) {
                $('#darkModeIcon').text(isDarkMode ? '☀️' : '🌙');
                $('#darkModeText').text(isDarkMode ? 'Light Mode' : 'Dark Mode');
            }
            updateDarkModeButton(document.documentElement.classList.contains('dark'));

            // Compare Mode toggling
            function updateModeUI() {
                const isCompare = $('#compareMode').is(':checked');
                $('#providerOptions').toggleClass('hidden', !isCompare);
                $('#submitBtn').text(isCompare ? 'Compare Models' : 'Generate Text');
                $('#resultLabel').text(isCompare ? 'Results:' : 'Result:');
            }
            $('#compareMode').on('change', updateModeUI);
            updateModeUI();

            // Clear
            $('#clearBtn').on('click', function () {
                $('#prompt').val('');
                activeTemplate = null;
                renderChips();
                $('#templateFields').addClass('hidden').empty();
                resetResult();
            });

            // Submit
            $('#aiForm').submit(async function (e) {
                e.preventDefault();

                if (!$('#prompt').val().trim()) return;

                if ($('#compareMode').is(':checked')) {
                    return runCompare();
                }

                const prompt = $('#prompt').val();
                const $result = $('#result');
                const $submitBtn = $('#submitBtn');

                $result.html('<span class="inline-block w-2 h-4 bg-indigo-600 dark:bg-indigo-400 animate-pulse"></span>');
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
                        body: JSON.stringify({ prompt })
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
                            $result.text(fullText); // .text() keeps this XSS-safe
                        }
                    }

                    if (!fullText) resetResult();
                } catch (err) {
                    $result.html('<div class="text-red-600 dark:text-red-400">Error: ' + escapeHtml(err.message) + '</div>');
                } finally {
                    $submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            });

            async function runCompare() {
                const prompt = $('#prompt').val();
                const providers = $('.provider-check:checked').map((_, el) => el.value).get();
                const $result = $('#result');
                const $submitBtn = $('#submitBtn');

                if (providers.length === 0) {
                    $result.html('<div class="text-red-600 dark:text-red-400">Select at least one provider.</div>');
                    return;
                }

                $result.html(loadingDots());
                $submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

                try {
                    const response = await fetch('{{ route("ai.compare") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        body: JSON.stringify({ prompt, providers })
                    });

                    if (!response.ok) throw new Error('Request failed with status ' + response.status);

                    const data = await response.json();
                    const cards = Object.entries(data.results).map(([name, r]) => `
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between mb-2">
                                <span class="font-semibold capitalize dark:text-white">${escapeHtml(name)}</span>
                                ${r.success ? `<span class="text-xs text-gray-500 dark:text-gray-400">${r.latency_ms}ms</span>` : ''}
                            </div>
                            <div class="whitespace-pre-line dark:text-gray-200">
                                ${r.success ? escapeHtml(r.text) : `<span class="text-red-600 dark:text-red-400">Error: ${escapeHtml(r.error)}</span>`}
                            </div>
                        </div>
                    `).join('');

                    $result.html(`<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${cards}</div>`);
                } catch (err) {
                    $result.html('<div class="text-red-600 dark:text-red-400">Error: ' + escapeHtml(err.message) + '</div>');
                } finally {
                    $submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            }
        });
    </script>
</body>

</html>