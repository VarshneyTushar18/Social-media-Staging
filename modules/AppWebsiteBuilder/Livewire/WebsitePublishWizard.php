<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\GoDaddyDomainClient;
use Modules\AppWebsiteBuilder\Support\WebsiteCustomDomain;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;
use Modules\AppWebsiteBuilder\Support\WebsiteSubdomain;

#[Title('Publish website')]
class WebsitePublishWizard extends Component
{
    public WebsiteSite $websiteSite;

    public string $step = 'search';

    public string $searchQuery = '';

    /** @var list<array<string, mixed>> */
    public array $searchResults = [];

    public ?string $searchError = null;

    public ?string $selectedDomain = null;

    public ?string $selectedPriceLabel = null;

    public ?string $checkoutUrl = null;

    public bool $embedded = false;

    public function mount(WebsiteSite $websiteSite, bool $embedded = false): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($websiteSite);

        $this->websiteSite = $websiteSite;
        $this->embedded = $embedded;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->searchError = null;
        $this->step = 'search';
    }

    public function searchDomains(): void
    {
        $this->guardWebsiteAgent();
        $this->searchError = null;
        $this->searchResults = [];

        $query = trim($this->searchQuery);

        if ($query === '') {
            $this->searchError = __('Enter a domain or business name to search.');

            return;
        }

        $client = app(GoDaddyDomainClient::class);

        if (! $client->isConfigured()) {
            $this->searchError = __('GoDaddy API is not configured. Add GODADDY_API_KEY and GODADDY_API_SECRET to .env.');

            return;
        }

        try {
            $rows = $client->searchWithAvailability($query, 12);
            $this->searchResults = array_values(array_filter(
                $rows,
                fn (array $row): bool => (bool) ($row['available'] ?? false)
            ));
        } catch (\Throwable $e) {
            $this->searchError = $e->getMessage();
        }

        if ($this->searchResults === [] && $this->searchError === null) {
            $this->searchError = __('No available domains found. Try another search.');
        }
    }

    public function selectDomain(string $domain, ?string $priceLabel = null, ?string $checkoutUrl = null): void
    {
        $this->guardWebsiteAgent();

        $client = app(GoDaddyDomainClient::class);
        $domain = $client->normalizeDomain($domain);

        if ($domain === '') {
            return;
        }

        $this->selectedDomain = $domain;
        $this->selectedPriceLabel = $priceLabel;
        $this->checkoutUrl = $checkoutUrl ?: $client->checkoutUrl($domain);
        $this->step = 'review';
    }

    public function publishStepNumber(): int
    {
        return match ($this->step) {
            'search' => 1,
            'review' => 2,
            'register' => 3,
            'dns' => 4,
            default => 4,
        };
    }

    public function goBack(): void
    {
        $this->guardWebsiteAgent();

        $this->step = match ($this->step) {
            'review' => 'search',
            'register' => 'review',
            'dns' => 'register',
            default => $this->step,
        };
    }

    public function goToRegister(): void
    {
        $this->guardWebsiteAgent();
        abort_unless($this->selectedDomain, 422);
        $this->step = 'register';
    }

    public function openGodaddyCheckout(): void
    {
        $this->guardWebsiteAgent();
        abort_unless($this->selectedDomain, 422);

        if (! filled($this->checkoutUrl)) {
            $this->checkoutUrl = app(GoDaddyDomainClient::class)->checkoutUrl($this->selectedDomain);
        }

        $this->js('window.open('.json_encode($this->checkoutUrl).', "_blank", "noopener,noreferrer")');
        $this->step = 'dns';
    }

    public function connectAndPublish(): void
    {
        $this->guardWebsiteAgent();
        abort_unless($this->selectedDomain, 422);

        $client = app(GoDaddyDomainClient::class);
        $domain = $client->normalizeDomain($this->selectedDomain);

        $taken = WebsiteSite::query()
            ->where('custom_domain', $domain)
            ->whereKeyNot($this->websiteSite->id)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already connected to another site in your account.'),
            ]);
        }

        $slug = (string) $this->websiteSite->subdomain_slug;

        if ($slug === '') {
            $slug = WebsiteSubdomain::normalize(str_replace('.', '-', strtok($domain, '.')));

            if ($slug !== '' && WebsiteSubdomain::validationError($slug) === null) {
                $slugTaken = WebsiteSite::query()
                    ->where('subdomain_slug', $slug)
                    ->whereKeyNot($this->websiteSite->id)
                    ->exists();

                if ($slugTaken) {
                    $slug .= '-'.substr(sha1($domain), 0, 6);
                }
            } else {
                $slug = '';
            }
        }

        $this->websiteSite->forceFill([
            'custom_domain' => $domain,
            'custom_domain_connected_at' => now(),
            'status' => WebsiteSite::STATUS_PUBLISHED,
            'published_at' => $this->websiteSite->published_at ?? now(),
            'subdomain_slug' => $slug !== '' ? $slug : $this->websiteSite->subdomain_slug,
        ])->save();

        $this->step = 'done';

        session()->forget([
            'website_publish_site_id',
            'website_publish_mode',
            'website_publish_queued_at',
            'website_publish_provider',
            'website_publish_domain',
        ]);

        $this->dispatch(
            'app-toast',
            type: 'success',
            title: __('Site published'),
            message: __('Connect DNS at GoDaddy, then visitors can open :url', [
                'url' => WebsiteCustomDomain::publicUrl($domain) ?? $domain,
            ]),
        );

    }

    public function closeEmbedded(): void
    {
        $this->dispatch('publish-domain-flow-closed');
    }

    public function render(): View
    {
        $view = view('appwebsitebuilder::livewire.website-publish-wizard', [
            'embedded' => $this->embedded,
            'publishStepNumber' => $this->publishStepNumber(),
            'dnsRecords' => WebsiteCustomDomain::dnsRecords(),
            'platformUrl' => $this->websiteSite->subdomain_slug
                ? WebsitePublishConfig::publicUrl((string) $this->websiteSite->subdomain_slug)
                : null,
            'customUrl' => $this->selectedDomain
                ? WebsiteCustomDomain::publicUrl($this->selectedDomain)
                : null,
        ]);

        if ($this->embedded) {
            return $view;
        }

        return $view->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Publish website'),
        ]);
    }

    protected function guardWebsiteAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    protected function guardSiteAccess(WebsiteSite $site): void
    {
        $user = auth()->user();
        abort_unless($user && (int) $site->owner_user_id === (int) $user->id, 403);

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);
        $projectId = $project?->id;

        if ($site->agency_project_id !== null && $projectId !== null && (int) $site->agency_project_id !== (int) $projectId) {
            abort(403);
        }
    }
}
