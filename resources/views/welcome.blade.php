@extends('layouts.app')

@section('content')
    <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center">

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                @foreach ($products as $product)
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="ml-2 text-lg leading-7 font-semibold">
                                <a href="{{ route('product', $product) }}" class="underline text-gray-900 dark:text-white">
                                    {{ $product->title }}
                                </a>
                            </div>
                        </div>

                        <div class="ml-2">
                            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                {{ $product->description }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $products->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
