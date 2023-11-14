<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\UserMenuItem;
use Illuminate\Http\Request;

class UserMenuItemMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
//        if (auth()->user()->hasRole(['dashboard.user', 'superadmin'])) {
//            Filament::registerNavigationItems([
//                NavigationItem::make('Users')
//                    ->url(route('filament.resources.users.index'), shouldOpenInNewTab: false)
//                    ->icon('heroicon-o-users')
//                    ->activeIcon('heroicon-s-users')
//                    ->sort(3),
//            ]);
//            Filament::registerNavigationItems([
//                NavigationItem::make('Countries')
//                    ->url(route('filament.resources.countries.index'), shouldOpenInNewTab: false)
//                    ->icon('heroicon-o-map')
//                    ->group(__('Localization'))
//                    ->sort(3),
//            ]);
//            Filament::registerNavigationItems([
//                NavigationItem::make('Currencies')
//                    ->url(route('filament.resources.currencies.index'), shouldOpenInNewTab: false)
//                    ->icon('heroicon-o-cash')
//                    ->group(__('Localization'))
//                    ->sort(3),
//            ]);
//        }
//        if (auth()->user()->hasRole(['dashboard.user', 'superadmin'])) {
//            Filament::registerNavigationItems([
//                NavigationItem::make('Developers')
//                    ->url(route('filament.resources.developers.index'), shouldOpenInNewTab: false)
//                    ->icon('heroicon-o-users')
//                    ->sort(3),
//            ]);
//        }
//
        return $next($request);
    }
}
