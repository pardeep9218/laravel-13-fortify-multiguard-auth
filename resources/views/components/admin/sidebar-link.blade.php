@props([
    'href' => '#',
    'active' => false,
    'disabled' => false,
])

<a
    href="{{ $href }}"
    @if ($active && ! $disabled)
        aria-current="page"
    @endif
    @if ($disabled)
        aria-disabled="true"
        tabindex="-1"
    @endif
    class="{{ $disabled
        ? 'flex cursor-not-allowed items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-400'
        : ($active
            ? 'flex items-center rounded-lg bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700'
            : 'flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900')
    }}"
>
    {{ $slot }}
</a>