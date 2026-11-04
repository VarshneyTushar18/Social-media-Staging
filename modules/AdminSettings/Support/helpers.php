<?php

use App\Support\Storage\StorageDriverManager;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminSettings\Support\SettingsPageRegistry;

if (! function_exists('get_option')) {
    function get_option(string $name, mixed $default = null): mixed
    {
        return app(OptionStore::class)->get($name, $default);
    }
}

if (! function_exists('settings_page_registry')) {
    function settings_page_registry(): SettingsPageRegistry
    {
        return app(SettingsPageRegistry::class);
    }
}

if (! function_exists('register_setting_section')) {
    function register_setting_section(string $key, ?string $label = null, int $order = 100): SettingsPageRegistry
    {
        return settings_page_registry()->section($key, $label, $order);
    }
}

if (! function_exists('register_settings_section')) {
    function register_settings_section(string $key, ?string $label = null, int $order = 100): SettingsPageRegistry
    {
        return register_setting_section($key, $label, $order);
    }
}

if (! function_exists('register_setting_item')) {
    function register_setting_item(string $sectionKey, array $item): SettingsPageRegistry
    {
        return settings_page_registry()->register($sectionKey, $item);
    }
}

if (! function_exists('register_settings_item')) {
    function register_settings_item(string $sectionKey, array $item): SettingsPageRegistry
    {
        return register_setting_item($sectionKey, $item);
    }
}

if (! function_exists('settings_navigation_sections')) {
    function settings_navigation_sections(): array
    {
        return settings_page_registry()->sections();
    }
}

if (! function_exists('settings_navigation_items')) {
    function settings_navigation_items(): array
    {
        return settings_page_registry()->items();
    }
}

if (! function_exists('settings_default_url')) {
    function settings_default_url(): string
    {
        return settings_page_registry()->defaultUrl();
    }
}

if (! function_exists('public_asset_from_path')) {
    /**
     * Map stored paths like "public/img/logo.png" to Laravel asset URLs ("/img/logo.png").
     */
    function public_asset_from_path(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        return asset($path);
    }
}

if (! function_exists('brand_asset_url')) {
    function brand_asset_url(string $value, string $fallbackPath = 'img/logo-brand-dark.png'): string
    {
        $storageDriverManager = app(StorageDriverManager::class);
        $activeBrandDisk = $storageDriverManager->activeDisk();
        $fallback = public_asset_from_path($fallbackPath);

        $value = trim($value);

        if ($value === '') {
            return $fallback;
        }

        if (str_starts_with($value, 'public/') || str_starts_with($value, 'img/')) {
            return public_asset_from_path($value);
        }

        if (preg_match('#^https?://#i', $value)) {
            $host = parse_url($value, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: request()->getHost();

            if ($host !== null && $appHost !== null && strcasecmp($host, $appHost) !== 0) {
                return $value;
            }
        }

        $path = parse_url($value, PHP_URL_PATH) ?: $value;
        $query = parse_url($value, PHP_URL_QUERY);
        $basePath = trim((string) request()->getBaseUrl(), '/');

        $path = ltrim($path, '/');

        if ($basePath !== '' && str_starts_with($path, $basePath.'/')) {
            $path = substr($path, strlen($basePath) + 1);
        }

        if (preg_match('#^media/(public|amazon|wasabi|contabo)/(.+)$#', $path, $matches)) {
            $url = $storageDriverManager->publicUrl($matches[1], $matches[2]);

            return $query && $url ? $url.'?'.$query : ($url ?: $fallback);
        }

        if (str_starts_with($path, 'files/')) {
            $url = $storageDriverManager->publicUrl($activeBrandDisk, $path);

            return $query && $url ? $url.'?'.$query : ($url ?: $fallback);
        }

        if (str_starts_with($path, 'public/') || str_starts_with($path, 'img/')) {
            return public_asset_from_path($path).($query ? '?'.$query : '');
        }

        return asset($path).($query ? '?'.$query : '');
    }
}

if (! function_exists('site_favicon_url')) {
    function site_favicon_url(): string
    {
        $stored = trim((string) get_option('website_favicon', ''));

        if ($stored === '') {
            $stored = 'public/img/favicon.png';
        }

        return brand_asset_url($stored, 'public/img/favicon.png');
    }
}

if (! function_exists('site_favicon_type')) {
    function site_favicon_type(?string $faviconUrl = null): string
    {
        $path = strtolower(parse_url($faviconUrl ?? site_favicon_url(), PHP_URL_PATH) ?: '');

        return str_ends_with($path, '.svg') ? 'image/svg+xml' : 'image/png';
    }
}

if (! function_exists('brand_option_url')) {
    function brand_option_url(string $optionKey, string $fallbackPath): string
    {
        $stored = trim((string) get_option($optionKey, ''));

        if ($stored === '' || $stored === $fallbackPath) {
            return brand_asset_url('', $fallbackPath);
        }

        return brand_asset_url($stored, $fallbackPath);
    }
}
