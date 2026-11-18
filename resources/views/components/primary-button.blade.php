<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-red-700 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider hover:bg-red-800 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition shadow-xs ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
