<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Admin')
        | AI Chatbot Builder
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">

    <div class="min-h-screen">

        {{-- Mobile sidebar overlay --}}
        <div
            id="admin-sidebar-overlay"
            class="fixed inset-0 z-40 hidden bg-gray-900/50 md:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-200 md:translate-x-0"
        >
            {{-- Brand --}}
            <div class="flex h-20 items-center border-b border-gray-200 px-6">
                <a
                    href="{{ route('dashboard') }}"
                    class="text-xl font-bold tracking-tight text-indigo-600"
                >
                    Chatbot AI
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 overflow-y-auto p-4">

                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Workspace
                </p>

                <x-admin.sidebar-link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')"
                >
                    Dashboard
                </x-admin.sidebar-link>

                <x-admin.sidebar-link
                    :href="route('websites.index')"
                    :active="request()->routeIs('websites.*')"
                >
                    Websites
                </x-admin.sidebar-link>

                {{-- Add routes here as the modules are implemented. --}}
                <x-admin.sidebar-link
                    href="#"
                    :active="request()->routeIs('chatbots.*')"
                    :disabled="true"
                >
                    AI Chatbots
                </x-admin.sidebar-link>

                <x-admin.sidebar-link
                    href="#"
                    :active="request()->routeIs('knowledge-base.*')"
                    :disabled="true"
                >
                    Knowledge Base
                </x-admin.sidebar-link>

                <x-admin.sidebar-link
                    href="#"
                    :active="request()->routeIs('conversations.*')"
                    :disabled="true"
                >
                    Conversations
                </x-admin.sidebar-link>

                <x-admin.sidebar-link
                    href="#"
                    :active="request()->routeIs('analytics.*')"
                    :disabled="true"
                >
                    Analytics
                </x-admin.sidebar-link>

                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Configuration
                </p>

                <x-admin.sidebar-link
                    href="#"
                    :active="request()->routeIs('integrations.*')"
                    :disabled="true"
                >
                    Integrations
                </x-admin.sidebar-link>

                <x-admin.sidebar-link
                    href="#"
                    :active="request()->routeIs('settings.*')"
                    :disabled="true"
                >
                    Settings
                </x-admin.sidebar-link>

            </nav>

            {{-- Sidebar footer --}}
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-gray-900">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="truncate text-xs text-gray-500">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main application area --}}
        <div class="min-h-screen md:pl-64">

            {{-- Header --}}
            <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">

                <div class="flex min-w-0 items-center gap-3">

                    {{-- Mobile menu button --}}
                    <button
                        id="admin-sidebar-toggle"
                        type="button"
                        aria-label="Open navigation menu"
                        aria-controls="admin-sidebar"
                        aria-expanded="false"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 md:hidden"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                            />
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold sm:text-xl">
                            @yield('page-title', 'Dashboard')
                        </h1>

                        @hasSection('page-description')
                            <p class="mt-1 hidden truncate text-sm text-gray-500 sm:block">
                                @yield('page-description')
                            </p>
                        @endif
                    </div>

                </div>

                {{-- Header actions --}}
                <div class="ml-4 flex shrink-0 items-center gap-3">
                    @yield('header-actions')

                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-gray-800">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-gray-500">
                            Administrator
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Logout
                        </button>
                    </form>
                </div>

            </header>

            {{-- Page content --}}
            <main class="mx-auto w-full max-w-screen-2xl p-4 sm:p-6 lg:p-8">

                {{-- Flash message --}}
                @if (session('success'))
                    <div
                        role="status"
                        class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        role="alert"
                        class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

    {{-- Mobile sidebar functionality --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('admin-sidebar');
            const toggle = document.getElementById('admin-sidebar-toggle');
            const overlay = document.getElementById('admin-sidebar-overlay');

            if (!sidebar || !toggle || !overlay) {
                return;
            }

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }

            toggle.addEventListener('click', function () {
                const isOpen =
                    toggle.getAttribute('aria-expanded') === 'true';

                isOpen ? closeSidebar() : openSidebar();
            });

            overlay.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('nav a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) {
                    overlay.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                } else {
                    closeSidebar();
                }
            });
        });
    </script>

    @stack('scripts')

</body>
</html>