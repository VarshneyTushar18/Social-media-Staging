<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminDomainRegistrars\Livewire\DomainRegistrarSettings;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('settings/domain-registrars', DomainRegistrarSettings::class);
});

Route::prefix('admin/settings')->middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('domain-registrars', DomainRegistrarSettings::class)->name('settings.domain-registrars');
});