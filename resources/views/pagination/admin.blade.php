@php
if (! isset($scrollTo)) {
    $scrollTo = false;
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-500">
            Showing
            <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5">
            {{-- Mobile: prev / next --}}
            <div class="flex items-center gap-1.5 sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-7 items-center rounded-md px-2.5 text-xs font-medium text-gray-300">
                        Previous
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="inline-flex h-7 items-center rounded-md border border-gray-200 bg-white px-2.5 text-xs font-medium text-gray-600 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 disabled:opacity-50"
                    >
                        Previous
                    </button>
                @endif

                <span class="px-1 text-xs tabular-nums text-gray-400">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <button
                        type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="inline-flex h-7 items-center rounded-md border border-gray-200 bg-white px-2.5 text-xs font-medium text-gray-600 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 disabled:opacity-50"
                    >
                        Next
                    </button>
                @else
                    <span class="inline-flex h-7 items-center rounded-md px-2.5 text-xs font-medium text-gray-300">
                        Next
                    </span>
                @endif
            </div>

            {{-- Desktop: numbered pills --}}
            <div class="hidden items-center gap-1 sm:flex">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-300" aria-hidden="true">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 disabled:opacity-50"
                        aria-label="{{ __('pagination.previous') }}"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-7 min-w-7 items-center justify-center px-1 text-xs text-gray-400">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span
                                        aria-current="page"
                                        class="inline-flex h-7 min-w-7 items-center justify-center rounded-md bg-primary-50 px-2 text-xs font-semibold text-primary-700 ring-1 ring-primary-200"
                                    >
                                        {{ $page }}
                                    </span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        class="inline-flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-xs font-medium text-gray-500 transition hover:bg-white hover:text-gray-800 hover:shadow-sm"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <button
                        type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 disabled:opacity-50"
                        aria-label="{{ __('pagination.next') }}"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                @else
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-300" aria-hidden="true">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
