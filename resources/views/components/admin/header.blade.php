<header class="border-b border-gray-700 bg-gray-900 text-white shadow-md">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div class="flex items-center gap-3">
            <x-brand-logo variant="color" class="h-9 w-auto sm:h-10" />
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-primary-300">Admin Portal</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="hidden text-sm text-gray-300 sm:inline">{{ auth('admin')->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-gray-600 bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-200 transition hover:bg-gray-700">
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    <nav class="border-t border-gray-800 bg-gray-800">
        <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 sm:px-6">
            <a
                href="{{ route('admin.dashboard') }}"
                @class([
                    'border-b-2 px-4 py-3 text-sm font-medium whitespace-nowrap transition',
                    'border-primary-500 text-primary-300' => request()->routeIs('admin.dashboard'),
                    'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-200' => ! request()->routeIs('admin.dashboard'),
                ])
            >
                Dashboard
            </a>
            <a
                href="{{ route('admin.applications.index') }}"
                @class([
                    'border-b-2 px-4 py-3 text-sm font-medium whitespace-nowrap transition',
                    'border-primary-500 text-primary-300' => request()->routeIs('admin.applications.index'),
                    'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-200' => ! request()->routeIs('admin.applications.index'),
                ])
            >
                New Applicants
            </a>
            <a
                href="{{ route('admin.archive.index') }}"
                @class([
                    'border-b-2 px-4 py-3 text-sm font-medium whitespace-nowrap transition',
                    'border-primary-500 text-primary-300' => request()->routeIs('admin.archive.index'),
                    'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-200' => ! request()->routeIs('admin.archive.index'),
                ])
            >
                Archive
            </a>
            <a
                href="{{ route('admin.finalized.index') }}"
                @class([
                    'border-b-2 px-4 py-3 text-sm font-medium whitespace-nowrap transition',
                    'border-primary-500 text-primary-300' => request()->routeIs('admin.finalized.*'),
                    'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-200' => ! request()->routeIs('admin.finalized.*'),
                ])
            >
                Finalized Applications
            </a>
        </div>
    </nav>
</header>
