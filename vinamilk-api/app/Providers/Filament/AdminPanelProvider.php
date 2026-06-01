<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->login()
            ->colors([
                'primary' => '#2563EB',
                'gray' => Color::Gray,
                'success' => '#22C55E',
                'warning' => '#F59E0B',
                'danger' => '#EF4444',
            ])
            ->darkMode(true) // Enable dark mode with toggle button
            ->brandName('Vinamilk Admin')
            ->brandLogo(asset('logo_vina.svg'))
            ->brandLogoHeight('3rem')
            ->font('Inter', provider: GoogleFontProvider::class, url: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('20rem')
            ->sidebarFullyCollapsibleOnDesktop()
            ->navigationGroups([
                'Bán hàng',
                'Kho hàng',
                'Chăm sóc khách hàng',
                'Khuyến mãi',
                'Tài khoản',
                'Hệ thống',
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->renderHook(
                'panels::head.start',
                fn (): string => '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            )
            ->renderHook(
                'panels::styles.after',
                fn (): string => '<style>
                    :root {
                        --fi-primary: 0.47 0.84 0.52;
                        --fi-primary-foreground: 0 0% 100%;
                    }
                    /* Center logo */
                    .fi-sidebar .fi-sidebar-brand {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        padding: 1.5rem;
                    }
                    .fi-sidebar .fi-sidebar-brand img {
                        max-height: 3rem;
                    }
                    .fi-sidebar .fi-sidebar-heading {
                        color: white;
                        font-weight: 600;
                    }
                    .fi-sidebar .fi-navigation-item-active {
                        background: rgba(255, 255, 255, 0.15);
                        border-left: 3px solid #2563EB;
                    }
                    .fi-sidebar .fi-navigation-item:hover {
                        background: rgba(255, 255, 255, 0.1);
                    }
                    .fi-table .fi-table-row:hover {
                        background-color: rgba(37, 99, 235, 0.05);
                    }
                    .fi-badge {
                        font-weight: 500;
                        padding: 0.25rem 0.75rem;
                        border-radius: 9999px;
                    }
                    /* Custom scrollbar */
                    ::-webkit-scrollbar {
                        width: 6px;
                        height: 6px;
                    }
                    ::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    ::-webkit-scrollbar-thumb {
                        background: #cbd5e1;
                        border-radius: 3px;
                    }
                    ::-webkit-scrollbar-thumb:hover {
                        background: #94a3b8;
                    }
                    /* Dark mode fixes */
                    .dark .fi-panel {
                        background: #0f172a;
                    }
                    .dark .fi-card {
                        background: #1e293b;
                    }
                    .dark .fi-sidebar {
                        background: #1e293b;
                    }
                    .dark ::-webkit-scrollbar-thumb {
                        background: #334155;
                    }
                    .dark ::-webkit-scrollbar-thumb:hover {
                        background: #475569;
                    }
                    /* Dark mode welcome banner */
                    .dark .bg-white {
                        background: #1e293b !important;
                    }
                    .dark .text-slate-900 {
                        color: #f1f5f9 !important;
                    }
                    .dark .text-slate-600 {
                        color: #cbd5e1 !important;
                    }
                    .dark .bg-slate-50 {
                        background: #334155 !important;
                    }
                    .dark .border-slate-100 {
                        border-color: #475569 !important;
                    }
                    .dark .text-slate-500 {
                        color: #94a3b8 !important;
                    }
                    .dark .bg-blue-50 {
                        background: #1e3a8a !important;
                    }
                    .dark .bg-teal-50 {
                        background: #134e4a !important;
                    }
                </style>'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetTenantMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->globalSearchKeyBindings(['mod+k'])
            ->globalSearchFieldSuffix('Search...')
            ->maxContentWidth('full');
    }
}
