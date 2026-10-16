@extends('layouts.admin')

@section('title', 'Websites')

@section('page-title', 'Websites')

@section('page-description', 'Manage your websites and prepare them for AI chatbot creation.')

@section('content')

    {{-- Add Website --}}
    <section class="mb-8 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">

        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Add a website
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Add your website to start building its AI knowledge base.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('websites.store') }}"
            class="space-y-5"
        >
            @csrf

            <div>
                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Website name
                    <span class="font-normal text-gray-400">(optional)</span>
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    maxlength="255"
                    placeholder="My Business"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label
                    for="url"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Website URL
                </label>

                <input
                    id="url"
                    name="url"
                    type="url"
                    value="{{ old('url') }}"
                    maxlength="2048"
                    placeholder="https://example.com"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >

                @error('url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
                Add Website
            </button>
        </form>
    </section>

    {{-- Website List --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-5 sm:px-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Your websites
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Websites registered to your account.
                </p>
            </div>

            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600">
                {{ $websites->total() }} total
            </span>
        </div>

        @forelse ($websites as $website)

            <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div class="min-w-0">
                    <h3 class="font-semibold text-gray-900">
                        {{ $website->name }}
                    </h3>

                    <a
                        href="{{ $website->url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-1 block break-all text-sm text-indigo-600 hover:underline"
                    >
                        {{ $website->url }}
                    </a>

                    <p class="mt-1 text-xs text-gray-400">
                        Added {{ $website->created_at->format('M d, Y') }}
                    </p>
                </div>

                @php
                    $statusClasses = [
                        'pending' => 'bg-yellow-50 text-yellow-700',
                        'analyzing' => 'bg-blue-50 text-blue-700',
                        'completed' => 'bg-green-50 text-green-700',
                        'failed' => 'bg-red-50 text-red-700',
                    ];

                    $statusClass = $statusClasses[$website->status]
                        ?? 'bg-gray-100 text-gray-700';
                @endphp

                <div class="flex w-fit shrink-0 flex-wrap items-center gap-3">

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                        {{ ucfirst($website->status) }}
                    </span>

                    @if (in_array($website->status, ['pending', 'failed', 'completed']))
                        <form
                            method="POST"
                            action="{{ route('websites.analyze', $website) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
                            >
                                {{ $website->status === 'completed' ? 'Analyze Again' : 'Analyze' }}
                            </button>
                        </form>
                    @endif

                </div>

            </div>

        @empty

            <div class="px-5 py-14 text-center">
                <h3 class="font-semibold text-gray-900">
                    No websites yet
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    Add your first website using the form above.
                </p>
            </div>

        @endforelse

        @if ($websites->hasPages())
            <div class="border-t border-gray-200 px-5 py-4 sm:px-6">
                {{ $websites->links() }}
            </div>
        @endif

    </section>

@endsection