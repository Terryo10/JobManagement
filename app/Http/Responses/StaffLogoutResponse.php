<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class StaffLogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect('/');
    }
}
