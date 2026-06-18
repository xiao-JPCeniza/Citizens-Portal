@props(['paginator'])

@if ($paginator->total() > 0)
    <div class="border-t border-gray-100 bg-gray-50/60 px-4 py-3 sm:px-6">
        @if ($paginator->hasPages())
            {{ $paginator->links('pagination.admin') }}
        @else
            <p class="text-xs text-gray-500">
                Showing
                <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
            </p>
        @endif
    </div>
@endif
