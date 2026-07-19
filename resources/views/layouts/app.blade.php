<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Text Generator')</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked@9/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: {} } };

        // Initialize theme: prefer server-side saved preference, then localStorage, then system preference
        const serverTheme = @json(auth()->user()?->theme ?? null);
        const savedTheme = serverTheme || localStorage.getItem('darkMode') === 'true' ||
            (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (savedTheme) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-200 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('ai.index') }}"
                    class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">Generator</a>
                @auth
                    <a href="{{ route('history.index') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">History</a>
                    <a href="{{ route('documents.index') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">Documents</a>
                @endauth
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <span
                        class="text-sm text-gray-500 dark:text-gray-400 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Login</a>
                    <a href="{{ route('register') }}"
                        class="text-sm px-3 py-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Sign Up</a>
                @endauth

                <button id="darkModeToggle"
                    class="px-3 py-1.5 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white flex items-center text-sm transition-colors">
                    <span id="darkModeIcon" class="mr-1.5">🌙</span>
                    <span id="darkModeText">Dark Mode</span>
                </button>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">@yield('heading', 'AI Text Generator')</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                @yield('subheading', 'Generate content with artificial intelligence')</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-3 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </div>

    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showToast(message, type = 'info') {
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                info: 'bg-gray-800 dark:bg-gray-700',
            };
            const $toast = $(`<div class="px-4 py-2.5 rounded-lg text-white text-sm shadow-lg ${colors[type] || colors.info} opacity-0 translate-y-2 transition-all duration-200">${message}</div>`);
            $('#toastContainer').append($toast);
            requestAnimationFrame(() => $toast.removeClass('opacity-0 translate-y-2'));
            setTimeout(() => {
                $toast.addClass('opacity-0 translate-y-2');
                setTimeout(() => $toast.remove(), 200);
            }, 3000);
        }

        function escapeHtml(str) {
            return $('<div>').text(str ?? '').html();
        }

        function renderMarkdown(text) {
            const html = marked.parse(text ?? '');
            return DOMPurify.sanitize(html);
        }

        $(document).ready(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#darkModeToggle').click(function () {
                const isDarkMode = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDarkMode);
                updateDarkModeButton(isDarkMode);

                // Save theme preference to server if authenticated
                @auth
                fetch('{{ route('theme.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    body: JSON.stringify({ theme: isDarkMode ? 'dark' : 'light' })
                }).catch(() => {}); // Silent fail - localStorage already saved it
                @endauth
            });

            function updateDarkModeButton(isDarkMode) {
                $('#darkModeIcon').text(isDarkMode ? '☀️' : '🌙');
                $('#darkModeText').text(isDarkMode ? 'Light Mode' : 'Dark Mode');
            }
            updateDarkModeButton(document.documentElement.classList.contains('dark'));
        });
    </script>

    @yield('scripts')
</body>

</html>