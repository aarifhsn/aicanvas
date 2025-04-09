<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel AI App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind dark mode configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }

        // Check for dark mode preference on page load
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
                class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white flex items-center">
                <span id="darkModeIcon" class="mr-2">🌙</span>
                <span id="darkModeText">Dark Mode</span>
            </button>
        </div>

        <div class="flex justify-center">
            <div class="w-full">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="bg-indigo-600 dark:bg-indigo-800 text-white px-6 py-4 text-lg font-medium">
                        AI Text Generation
                    </div>
                    <div class="p-6">
                        <form id="aiForm">
                            <div class="mb-4">
                                <label for="prompt"
                                    class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Enter your
                                    prompt:</label>
                                <textarea
                                    class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                                    id="prompt" name="prompt" rows="3" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="type"
                                    class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Content
                                    type:</label>
                                <select id="type" name="type"
                                    class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                                    <option value="general">General</option>
                                    <option value="linkedin">LinkedIn Post</option>
                                    <option value="summary">Summary</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-800 focus:outline-none focus:ring focus:ring-indigo-200">Generate
                                Text</button>
                        </form>

                        <div class="mt-6">
                            <h5 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Result:</h5>
                            <div id="result"
                                class="p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg min-h-20 whitespace-pre-line text-gray-800 dark:text-gray-200">
                                <!-- AI response will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Handle dark mode toggle
            $('#darkModeToggle').click(function () {
                const isDarkMode = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDarkMode);
                updateDarkModeButton(isDarkMode);
            });

            // Update dark mode button text and icon
            function updateDarkModeButton(isDarkMode) {
                if (isDarkMode) {
                    $('#darkModeIcon').text('☀️');
                    $('#darkModeText').text('Light Mode');
                } else {
                    $('#darkModeIcon').text('🌙');
                    $('#darkModeText').text('Dark Mode');
                }
            }

            // Initialize dark mode button state
            updateDarkModeButton(document.documentElement.classList.contains('dark'));

            // Handle form submission
            $('#aiForm').submit(function (e) {
                e.preventDefault();

                const prompt = $('#prompt').val();
                const type = $('#type').val();

                $('#result').html('<div class="flex flex-col items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 dark:border-indigo-400"></div><div class="mt-2 dark:text-gray-300">Processing...</div></div>');

                $.ajax({
                    url: '{{ route("ai.generate") }}',
                    method: 'POST',
                    data: {
                        prompt: prompt,
                        type: type
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#result').text(response.result);
                        } else {
                            $('#result').html('<div class="text-red-600 dark:text-red-400">Error: ' + response.error + '</div>');
                        }
                    },
                    error: function (xhr) {
                        $('#result').html('<div class="text-red-600 dark:text-red-400">Error: ' + xhr.responseJSON.error + '</div>');
                    }
                });
            });
        });
    </script>
</body>

</html>