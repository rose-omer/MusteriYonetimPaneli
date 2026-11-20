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
            // "Harita ve Liste" tasarım dili. Ayrıntı: DESIGN.md.
            ->colors([
                // Mürekkep: #17323E = 600. Düğme, bağlantı, seçili durum.
                'primary' => [
                    50 => 'oklch(0.965 0.010 225)',
                    100 => 'oklch(0.930 0.020 225)',
                    200 => 'oklch(0.880 0.030 226)',
                    300 => 'oklch(0.800 0.045 226)',
                    400 => 'oklch(0.700 0.058 226)',
                    500 => 'oklch(0.520 0.055 228)',
                    600 => 'oklch(0.302 0.039 229)',
                    700 => 'oklch(0.270 0.036 229)',
                    800 => 'oklch(0.240 0.032 229)',
                    900 => 'oklch(0.210 0.028 229)',
                    950 => 'oklch(0.170 0.022 229)',
                ],
                // Mavi-gri nötr: 50 = #EEF3F4 zemin, 200 = #D5DEE2 kenarlık, 600 = #4C6470 ikincil metin.
                'gray' => [
                    50 => 'oklch(0.961 0.006 211)',
                    100 => 'oklch(0.930 0.009 220)',
                    200 => 'oklch(0.895 0.011 226)',
                    300 => 'oklch(0.820 0.016 226)',
                    400 => 'oklch(0.680 0.024 228)',
                    500 => 'oklch(0.525 0.031 229)',
                    600 => 'oklch(0.488 0.035 230)',
                    700 => 'oklch(0.400 0.035 230)',
                    800 => 'oklch(0.320 0.032 230)',
                    900 => 'oklch(0.255 0.028 230)',
                    950 => 'oklch(0.205 0.024 230)',
                ],
                // Erik: #6E2A55 = 600. Tek vurgu: açık kayıt, ödenmemiş, onay bekleyen, toplu seçim.
                'vurgu' => [
                    50 => 'oklch(0.965 0.010 345)',
                    100 => 'oklch(0.940 0.015 343)',
                    200 => 'oklch(0.885 0.027 341)',
                    300 => 'oklch(0.800 0.050 343)',
                    400 => 'oklch(0.720 0.075 343)',
                    500 => 'oklch(0.560 0.095 344)',
                    600 => 'oklch(0.398 0.110 344)',
                    700 => 'oklch(0.350 0.100 344)',
                    800 => 'oklch(0.300 0.085 344)',
                    900 => 'oklch(0.260 0.070 344)',
                    950 => 'oklch(0.200 0.050 344)',
                ],
                // Tehlike sınıfı tek tonlu, koyulaşan sıra. Rozet yüzeyi theme.css içinde (--mtp-hz-*).
                'tehlike-az' => Color::hex('#cfe3e8'),
                'tehlike-orta' => Color::hex('#427485'),
                'tehlike-cok' => Color::hex('#133543'),
                // Sönük kırmızı: #B3261E = 600. Yalnız yıkıcı işlem ve hata; saf kırmızı paletin dışında kalıyordu.
                'danger' => [
                    50 => 'oklch(0.970 0.012 25)',
                    100 => 'oklch(0.935 0.028 25)',
                    200 => 'oklch(0.880 0.055 26)',
                    300 => 'oklch(0.800 0.090 27)',
                    400 => 'oklch(0.705 0.135 28)',
                    500 => 'oklch(0.600 0.165 28.5)',
                    600 => 'oklch(0.501 0.178 28.7)',
                    700 => 'oklch(0.440 0.155 28.7)',
                    800 => 'oklch(0.380 0.130 28.7)',
                    900 => 'oklch(0.320 0.105 28.7)',
                    950 => 'oklch(0.240 0.075 28.7)',
                ],
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Sky,
            ])
            // Yazı tipleri yerel olarak sunulur; üçüncü taraf font isteği yok (KVKK).
            ->font('Instrument Sans Variable', provider: LocalFontProvider::class)
            ->monoFont('Spline Sans Mono Variable', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode()
            ->spa()
            ->unsavedChangesAlerts()
            // Tasarımda tek üst çubuk var; mobilde menü çekmeceye iner.
            ->topNavigation()
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
