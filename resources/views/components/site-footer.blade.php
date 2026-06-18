@php
    use App\Support\ManoloFortich;
@endphp

<footer class="border-t border-gray-200 bg-white py-6 text-center text-sm text-gray-500">
    <p>Have a problem? You may contact</p>
    <p class="mt-1 font-medium text-gray-700">{{ ManoloFortich::SUPPORT_OFFICE }}</p>
    <p class="mt-1">
        <a href="tel:{{ ManoloFortich::SUPPORT_PHONE }}" class="text-primary-700 hover:underline">{{ ManoloFortich::SUPPORT_PHONE }}</a>
        <span class="mx-1" aria-hidden="true">&middot;</span>
        <a href="mailto:{{ ManoloFortich::SUPPORT_EMAIL }}" class="text-primary-700 hover:underline">{{ ManoloFortich::SUPPORT_EMAIL }}</a>
    </p>
    <p class="mt-4">&copy; {{ date('Y') }} Municipality of Manolo Fortich, Bukidnon. All rights reserved.</p>
</footer>
