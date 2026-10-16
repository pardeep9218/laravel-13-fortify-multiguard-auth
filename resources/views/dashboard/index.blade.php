@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('page-description', 'Overview of your AI chatbot workspace.')

@section('content')

    {{-- Welcome section --}}
    <section class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">
            Welcome back, {{ auth()->user()->name }}!
        </h2>

        <p class="mt-2 text-gray-500">
            Manage your websites and build AI-powered chatbots from one place.
        </p>
    </section>

    {{-- Statistics cards --}}
    <section class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Websites
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ auth()->user()->websites()->count() }}
            </p>

            <a
                href="{{ route('websites.index') }}"
                class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:underline"
            >
                Manage websites →
            </a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                AI Chatbots
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">0</p>

            <p class="mt-3 text-sm text-gray-400">
                Coming soon
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Conversations
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">0</p>

            <p class="mt-3 text-sm text-gray-400">
                Coming soon
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Knowledge Bases
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">0</p>

            <p class="mt-3 text-sm text-gray-400">
                Coming soon
            </p>
        </div>

    </section>

    {{-- Getting started --}}
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">

        <h2 class="text-lg font-semibold text-gray-900">
            Get started
        </h2>

        <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
            Add your business website first. The next step will be to analyze
            its content and use that information to create a customized AI chatbot.
        </p>

        <a
            href="{{ route('websites.index') }}"
            class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
        >
            Add your first website
        </a>

    </section>

    @include('components.two-factor-management', ['guardName' => 'web'])

@endsection