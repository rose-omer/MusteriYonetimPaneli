<?php

namespace App\Providers\Filament;

use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Müşteri Yönetim Paneli')
            ->favicon(asset('favicon.svg'))
            // Kayıt kapalı: kullanıcıyı yönetici ekler.
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            ->colors([
                // Petrol mavisi: tehlike ve ödeme rozetlerinin kırmızı/turuncu/yeşilinden ayrışır.
                // Otomatik palet fazla doygun (camgöbeği) çıktığı için basamaklar elle verildi.
                'primary' => [
                    50 => 'oklch(0.975 0.012 218)',
                    100 => 'oklch(0.945 0.025 218)',
                    200 => 'oklch(0.89 0.045 218)',
                    300 => 'oklch(0.80 0.07 218)',
                    400 => 'oklch(0.69 0.088 218)',
                    500 => 'oklch(0.55 0.09 218)',
                    600 => 'oklch(0.495 0.085 218)',
                    700 => 'oklch(0.43 0.075 218)',
                    800 => 'oklch(0.37 0.064 218)',
                    900 => 'oklch(0.32 0.052 218)',
                    950 => 'oklch(0.235 0.04 218)',
                ],
                'gray' => Color::Slate,
                'danger' => Color::Red,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Sky,
            ])
            // Yazı tipi yerel olarak sunulur; üçüncü taraf font isteği yok (KVKK).
            ->font('IBM Plex Sans Variable', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode()
            ->spa()
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->navigationGroups([
                NavigationGroup::make('Yönetim'),
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn () => app()->environment('demo') ? view('filament.auth.demo-note') : '',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
