# AICanvas - Laravel AI Text Generator

A simple and modern web application that generates AI-powered text content using the Hugging Face API. This project uses Laravel as the backend framework and features a clean, responsive UI with dark mode support.

## Features

-   🤖 AI text generation powered by HuggingFace API
-   🌓 Dark/Light mode toggle with persistent user preference
-   🎨 Clean, modern UI built with Tailwind CSS
-   ⚡ Real-time AJAX text generation
-   🔄 Fallback responses for API failures
-   📱 Fully responsive design
-   🔒 CSRF protection for all requests

## Requirements

-   PHP 8.0+
-   Laravel 8.0+
-   Composer
-   HuggingFace API Token

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/aarifhsn/aicanvas
    cd aicanvas
    ```

2. Install dependencies:

    ```bash
    composer install
    npm install && npm run dev
    ```

3. Set up environment variables:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Add your HuggingFace API credentials to the `.env` file:

    ```
    HUGGINGFACE_API_TOKEN=your_api_token_here
    HUGGINGFACE_MODEL=mistralai/Mixtral-8x7B-Instruct-v0.1
    ```

5. Start the development server:

    ```bash
    php artisan serve
    ```

6. Access the application at http://localhost:8000

## Usage

1. Enter a prompt in the textbox
2. Click the "Generate Text" button
3. The AI-generated text will appear in the result section

## How It Works

The application uses the HuggingFace API to generate text based on user prompts. The main components include:

-   **HuggingFaceService**: Handles API communication and text formatting
-   **AIController**: Manages HTTP requests and validation
-   **Blade View**: Provides the user interface with dark mode support

## Customization

### Changing the AI Model

You can modify the default model in your `.env` file:

```
HUGGINGFACE_MODEL=your-preferred-model
```

### Text Formatting

The text formatting logic can be found in the `formatText()` method in the `HuggingFaceService` class. You can customize this method to change how the generated text is formatted.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

-   [Laravel](https://laravel.com)
-   [Tailwind CSS](https://tailwindcss.com)
-   [HuggingFace](https://huggingface.co)
-   [jQuery](https://jquery.com)
