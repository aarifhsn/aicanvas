@extends('layouts.app')

@section('heading', 'Welcome Back')
@section('subheading', 'Log in to save and view your generation history')

@section('content')
    <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3 py-2 border dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2 border dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="remember" class="rounded"> Remember me
            </label>
            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Log
                In</button>
        </form>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4 text-center">
            Don't have an account? <a href="{{ route('register') }}"
                class="text-indigo-600 dark:text-indigo-400 hover:underline">Sign up</a>
        </p>
    </div>
@endsection