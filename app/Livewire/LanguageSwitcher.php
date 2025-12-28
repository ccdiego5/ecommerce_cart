<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public function mount()
    {
        $this->currentLocale = App::getLocale();
    }

    public function switchLanguage($locale)
    {
        if (in_array($locale, ['en', 'es'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->currentLocale = $locale;
            
            // Refrescar la página para aplicar los cambios
            $this->dispatch('language-changed');
            return $this->redirect(request()->header('Referer'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
