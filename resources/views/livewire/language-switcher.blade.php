<div class="flex items-center gap-2">
    <button wire:click="switchLanguage('en')"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $currentLocale === 'en' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        English
    </button>
    <button wire:click="switchLanguage('es')"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $currentLocale === 'es' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        Español
    </button>
</div>
