<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel AI App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-center">
            <div class="w-full">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-indigo-600 text-white px-6 py-4 text-lg font-medium">
                        AI Text Generation
                    </div>
                    <div class="p-6">
                        <form id="aiForm">
                            <div class="mb-4">
                                <label for="prompt" class="block text-gray-700 font-medium mb-2">Enter your
                                    prompt:</label>
                                <textarea
                                    class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                                    id="prompt" name="prompt" rows="3" required></textarea>
                            </div>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-200">Generate
                                Text</button>
                        </form>

                        <div class="mt-6">
                            <h5 class="text-lg font-medium text-gray-700 mb-2">Result:</h5>
                            <div id="result" class="p-4 bg-gray-50 border border-gray-200 rounded-lg min-h-20">
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#aiForm').submit(function (e) {
                e.preventDefault();

                const prompt = $('#prompt').val();
                $('#result').html('<div class="flex flex-col items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><div class="mt-2">Processing...</div></div>');

                $.ajax({
                    url: '{{ route("ai.generate") }}',
                    method: 'POST',
                    data: {
                        prompt: prompt
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#result').text(response.result);
                        } else {
                            $('#result').html('<div class="text-red-600">Error: ' + response.error + '</div>');
                        }
                    },
                    error: function (xhr) {
                        $('#result').html('<div class="text-red-600">Error: ' + xhr.responseJSON.error + '</div>');
                    }
                });
            });
        });
    </script>
</body>

</html>