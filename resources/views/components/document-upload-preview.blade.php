@props([
    'file' => null,
    'target' => '',
    'label' => 'Document preview',
])

@php
    $previewUrl = null;
    $fileName = null;
    $fileSizeKb = null;
    $isOnServer = false;

    if ($file) {
        $fileName = method_exists($file, 'getClientOriginalName')
            ? $file->getClientOriginalName()
            : 'Uploaded file';

        try {
            $bytes = method_exists($file, 'getSize') ? (int) $file->getSize() : 0;
            $fileSizeKb = $bytes > 0 ? number_format($bytes / 1024, 1) : null;
        } catch (\Throwable) {
            $fileSizeKb = null;
        }

        try {
            if (method_exists($file, 'isPreviewable') && $file->isPreviewable()) {
                $previewUrl = $file->temporaryUrl();
            } elseif (method_exists($file, 'temporaryUrl')) {
                $previewUrl = $file->temporaryUrl();
            }
        } catch (\Throwable) {
            $previewUrl = null;
        }

        try {
            $isOnServer = method_exists($file, 'exists')
                ? (bool) $file->exists()
                : (method_exists($file, 'getRealPath') && filled($file->getRealPath()) && is_file($file->getRealPath()));
        } catch (\Throwable) {
            $isOnServer = false;
        }
    }
@endphp

<div {{ $attributes->class('mt-3') }}>
    <div
        wire:loading
        wire:target="{{ $target }}"
        class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
    >
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Uploading to server...
    </div>

    <div wire:loading.remove wire:target="{{ $target }}">
        @if ($file)
            <div class="overflow-hidden rounded-xl border border-accent-200 bg-accent-50/60 shadow-sm">
                @if ($previewUrl)
                    <div class="border-b border-accent-100 bg-white p-3">
                        <img
                            src="{{ $previewUrl }}"
                            alt="{{ $label }}"
                            class="mx-auto max-h-64 w-full object-contain"
                        >
                    </div>
                @endif

                <div class="space-y-1 px-3 py-3 text-sm">
                    <p class="font-medium text-accent-900">
                        @if ($isOnServer)
                            Uploaded to server
                        @else
                            File selected
                        @endif
                    </p>
                    <p class="break-all text-accent-800">{{ $fileName }}</p>
                    @if ($fileSizeKb)
                        <p class="text-xs text-accent-700">{{ $fileSizeKb }} KB</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
