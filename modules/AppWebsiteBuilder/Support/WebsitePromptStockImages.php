<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebsitePromptStockImages
{
    /**
     * Resolve a stock photo URL from natural-language context (user prompt + AI image_query).
     */
    public static function url(string $searchText, int $width = 1200, int $variant = 0): string
    {
        $searchText = self::normalizeSearchText($searchText);
        if ($searchText === '') {
            return self::fallbackUrl($width);
        }

        $apiKey = trim((string) env('UNSPLASH_ACCESS_KEY', ''));
        if ($apiKey !== '') {
            $apiUrl = self::urlFromUnsplashApi($searchText, $width, $variant, $apiKey);
            if ($apiUrl !== null) {
                return $apiUrl;
            }
        }

        return self::urlFromKeywordPools($searchText, $width, $variant);
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public static function visualContext(array $content, string $siteType, string $userPrompt): string
    {
        $parts = array_filter([
            trim($userPrompt),
            (string) ($content['site_name'] ?? ''),
            trim($siteType),
            (string) ($content['tagline'] ?? ''),
        ]);

        return self::normalizeSearchText(implode(' ', $parts));
    }

    protected static function normalizeSearchText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';

        return Str::limit($text, 240, '');
    }

    protected static function fallbackUrl(int $width): string
    {
        return 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w='.$width.'&q=80';
    }

    protected static function urlFromUnsplashApi(string $query, int $width, int $variant, string $apiKey): ?string
    {
        $cacheKey = 'website_stock_img:'.md5($query.'|'.$width.'|'.$variant);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $page = (abs(crc32($query.'|'.$variant)) % 5) + 1;

        try {
            $verify = is_file(storage_path('ssl/cacert.pem'))
                ? (realpath(storage_path('ssl/cacert.pem')) ?: storage_path('ssl/cacert.pem'))
                : true;

            $response = Http::timeout(20)
                ->withOptions(['verify' => $verify])
                ->withHeaders(['Authorization' => 'Client-ID '.$apiKey])
                ->get('https://api.unsplash.com/search/photos', [
                    'query' => Str::limit($query, 100),
                    'per_page' => 1,
                    'page' => $page,
                    'orientation' => 'landscape',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $raw = (string) data_get($response->json(), 'results.0.urls.regular', '');
            if ($raw === '') {
                return null;
            }

            $url = $raw.(str_contains($raw, '?') ? '&' : '?').'w='.$width.'&q=80&auto=format&fit=crop';
            Cache::put($cacheKey, $url, now()->addDays(7));

            return $url;
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function urlFromKeywordPools(string $searchText, int $width, int $variant): string
    {
        $text = strtolower($searchText);
        $bestPool = 'default';
        $bestScore = 0;

        foreach (self::keywordPools() as $poolName => $meta) {
            $score = 0;
            foreach ($meta['keywords'] as $keyword) {
                if (str_contains($text, strtolower($keyword))) {
                    $score += strlen($keyword) > 5 ? 3 : 2;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestPool = $poolName;
            }
        }

        $photos = self::keywordPools()[$bestPool]['photos'] ?? self::keywordPools()['default']['photos'];
        $index = abs(crc32($searchText.'|'.$variant)) % count($photos);
        $photoId = $photos[$index];

        return 'https://images.unsplash.com/'.$photoId.'?auto=format&fit=crop&w='.$width.'&q=80';
    }

    /**
     * @return array<string, array{keywords: list<string>, photos: list<string>}>
     */
    protected static function keywordPools(): array
    {
        return [
            'fashion_retail' => [
                'keywords' => ['fashion', 'clothing', 'apparel', 'boutique', 'wear', 'wardrobe', 'style', 'dress'],
                'photos' => [
                    'photo-1445205170230-053b83016050',
                    'photo-1483986768273-34bed2e87f5d',
                    'photo-1558171813-4e0906c85d44',
                    'photo-1523381210434-271996314d48',
                ],
            ],
            'ecommerce' => [
                'keywords' => ['ecommerce', 'e-commerce', 'online store', 'shop', 'retail', 'marketplace', 'product', 'checkout', 'cart'],
                'photos' => [
                    'photo-1556742049-0cfed4f6a45d',
                    'photo-1556740758-90de374c12ad',
                    'photo-1523275335684-37898b6baf30',
                    'photo-1563013544-824ae1b704d3',
                ],
            ],
            'restaurant' => [
                'keywords' => ['restaurant', 'cafe', 'dining', 'food', 'pizza', 'menu', 'chef', 'kitchen', 'bistro', 'bakery', 'catering'],
                'photos' => [
                    'photo-1517248135467-4c7edcad34c4',
                    'photo-1414235077428-338989a2e8c0',
                    'photo-1559339352-11d035aa65de',
                    'photo-1504674900247-0877df9cc836',
                ],
            ],
            'beauty' => [
                'keywords' => ['beauty', 'salon', 'spa', 'makeup', 'facial', 'hair', 'nails', 'wellness'],
                'photos' => [
                    'photo-1560066984-138dadb4c035',
                    'photo-1522337360788-8a13fefbd509',
                    'photo-1519415511895-9538b6367186',
                    'photo-1595476108010-bfae6e1376a5',
                ],
            ],
            'pet' => [
                'keywords' => ['pet', 'dog', 'cat', 'grooming', 'veterinary', 'animal', 'puppy'],
                'photos' => [
                    'photo-1450778869180-41d0601e046e',
                    'photo-1601758228041-f3b2795255f1',
                    'photo-1530281700549-e82e7bf110d6',
                    'photo-1583511655823-05700d7b51bd',
                ],
            ],
            'cleaning' => [
                'keywords' => ['cleaning', 'janitorial', 'maid', 'sanitize', 'housekeeping'],
                'photos' => [
                    'photo-1581578731548-c64695cc6952',
                    'photo-1628177142298-32f66d991423',
                    'photo-1558618666-fcd25c85cd64',
                ],
            ],
            'construction' => [
                'keywords' => ['construction', 'contractor', 'plumber', 'electrician', 'roofing', 'hvac', 'renovation', 'building'],
                'photos' => [
                    'photo-1504307651254-35680f356dfd',
                    'photo-1503387762-592deb58ef4e',
                    'photo-1541888946425-d81bb19240f5',
                ],
            ],
            'tech' => [
                'keywords' => ['software', 'saas', 'app', 'tech', 'startup', 'digital', 'platform', 'ai', 'developer'],
                'photos' => [
                    'photo-1498050108023-c5249f4df085',
                    'photo-1460925895917-afdab827c52f',
                    'photo-1551434678-e076c223a692',
                    'photo-1522071820081-009f0129c71c',
                ],
            ],
            'health' => [
                'keywords' => ['health', 'medical', 'clinic', 'doctor', 'fitness', 'gym', 'yoga', 'therapy'],
                'photos' => [
                    'photo-1576091160399-112ba8d25d1d',
                    'photo-1518611012118-696072aa579a',
                    'photo-1544367567-0f2fcb009e0b',
                ],
            ],
            'real_estate' => [
                'keywords' => ['real estate', 'property', 'home', 'house', 'apartment', 'realtor', 'interior'],
                'photos' => [
                    'photo-1560518883-ce09059eeffa',
                    'photo-1484154210962-1977ffeb9ba9',
                    'photo-1502672260266-1c1ef1d93788',
                ],
            ],
            'education' => [
                'keywords' => ['school', 'education', 'course', 'training', 'learning', 'tutor', 'academy'],
                'photos' => [
                    'photo-1523050854058-8df90110c9f1',
                    'photo-1523240795612-9a054b0db644',
                    'photo-1427504034308-ef06aad3c15d',
                ],
            ],
            'travel' => [
                'keywords' => ['travel', 'hotel', 'tour', 'vacation', 'resort', 'adventure'],
                'photos' => [
                    'photo-1488646953014-85cb44e25828',
                    'photo-1476514525535-07fb3b4ae5f1',
                    'photo-1506905925346-21bda4d32df4',
                ],
            ],
            'default' => [
                'keywords' => [],
                'photos' => [
                    'photo-1497366216548-37526070297c',
                    'photo-1497366811353-6870744d04b2',
                    'photo-1552664739-d307ca884978',
                    'photo-1553877522-43269d4ea984',
                ],
            ],
        ];
    }
}
