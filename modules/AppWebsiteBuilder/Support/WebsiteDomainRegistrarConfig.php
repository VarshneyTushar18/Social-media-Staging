<?php

namespace Modules\AppWebsiteBuilder\Support;

use Modules\AdminSettings\Support\OptionStore;

class WebsiteDomainRegistrarConfig
{
    public static function godaddyConfigured(): bool
    {
        $config = self::godaddy();

        return $config['api_key'] !== '' && $config['api_secret'] !== '';
    }

    public static function namecheapConfigured(): bool
    {
        $config = self::namecheap();

        return $config['api_user'] !== ''
            && $config['api_key'] !== ''
            && $config['username'] !== ''
            && $config['client_ip'] !== '';
    }

    public static function hostingerConfigured(): bool
    {
        return self::hostinger()['api_token'] !== '';
    }

    public static function namesiloConfigured(): bool
    {
        return self::namesilo()['api_key'] !== '';
    }

    /**
     * @return array{env: string, api_key: string, api_secret: string}
     */
    public static function godaddy(): array
    {
        $env = strtolower(self::option('domain_godaddy_env', 'ote'));

        return [
            'env' => $env === 'production' ? 'production' : 'ote',
            'api_key' => self::option('domain_godaddy_api_key'),
            'api_secret' => self::option('domain_godaddy_api_secret'),
        ];
    }

    /**
     * @return array{sandbox: bool, api_user: string, api_key: string, username: string, client_ip: string}
     */
    public static function namecheap(): array
    {
        return [
            'sandbox' => self::option('domain_namecheap_sandbox', '1') === '1',
            'api_user' => self::option('domain_namecheap_api_user'),
            'api_key' => self::option('domain_namecheap_api_key'),
            'username' => self::option('domain_namecheap_username'),
            'client_ip' => self::option('domain_namecheap_client_ip'),
        ];
    }

    /**
     * @return array{api_token: string}
     */
    public static function hostinger(): array
    {
        return [
            'api_token' => self::option('domain_hostinger_api_token'),
        ];
    }

    /**
     * @return array{api_key: string}
     */
    public static function namesilo(): array
    {
        return [
            'api_key' => self::option('domain_namesilo_api_key'),
        ];
    }

    protected static function option(string $key, string $default = ''): string
    {
        if (! class_exists(OptionStore::class) || ! app()->bound(OptionStore::class)) {
            return $default;
        }

        return trim((string) app(OptionStore::class)->get($key, $default));
    }
}