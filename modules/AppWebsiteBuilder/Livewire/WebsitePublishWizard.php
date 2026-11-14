<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\GoDaddyDomainClient;
use Modules\AppWebsiteBuilder\Support\NamecheapDomainClient;
use Modules\AppWebsiteBuilder\Support\WebsiteCustomDomain;
use Modules\AppWebsiteBuilder\Support\WebsiteCustomDomainDnsVerifier;
use Modules\AppWebsiteBuilder\Support\WebsiteDomainInputValidator;
use Modules\AppWebsiteBuilder\Support\WebsiteDomainRegistrarResolver;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;

#[Title('Publish website')]
class WebsitePublishWizard extends Component
{
    public WebsiteSite $websiteSite;

    public string $domainProvider = 'godaddy';

    public string $step = 'search';

    public string $searchQuery = '';

    /** @var list<array<string, mixed>> */
    public array $searchResults = [];

    public ?string $searchError = null;

    public ?string $searchInputError = null;

    public bool $searchCanSubmit = false;

    public ?string $selectedDomain = null;

    public ?string $checkoutUrl = null;

    public bool $embedded = false;

    /** Show the top “Selected” resume bar when returning with a saved pending domain. */
    public bool $resumePendingSelection = false;

    public bool $dnsVerified = false;

  /** @var list<array{type: string, host: string, points_to: string, status: string, observed: ?string}> */
    public array $dnsRecordStatuses = [];

    public ?string $dnsVerifyMessage = null;

    public function mount(WebsiteSite $websiteSite, bool $embedded = false, string $domainProvider = 'godaddy'): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($websiteSite);

        $domainProvider = strtolower(trim($domainProvider));
        abort_unless(WebsiteDomainRegistrarResolver::supports($domainProvider), 422);

        $this->websiteSite = $websiteSite->fresh() ?? $websiteSite;
        $this->embedded = $embedded;
        $this->domainProvider = $domainProvider;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->searchError = null;
        $this->searchInputError = null;
        $this->searchCanSubmit = false;
        $this->dnsVerified = false;
        $this->dnsRecordStatuses = [];
        $this->dnsVerifyMessage = null;

        $pending = WebsiteDomainInputValidator::normalizeRegisteredDomain(
            (string) ($websiteSite->pending_custom_domain ?? '')
        );

        if ($pending !== '') {
            $this->selectedDomain = $pending;
            $this->checkoutUrl = WebsiteDomainRegistrarResolver::resolve($domainProvider)->checkoutUrl($pending);
            $this->resumePendingSelection = true;
        }

        $this->step = 'search';
    }

    public function updatedSearchQuery(): void
    {
        if (trim($this->searchQuery) === '') {
            $this->searchResults = [];
            $this->searchError = null;
            $this->searchInputError = null;
            $this->searchCanSubmit = false;

            return;
        }

        $this->searchInputError = WebsiteDomainInputValidator::validateSearchInput($this->searchQuery);
        $this->searchCanSubmit = $this->searchInputError === null;

        if ($this->searchCanSubmit) {
            $this->searchError = null;
        }
    }

    public function searchDomains(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);
        $this->throttleDomainSearch();
        $this->searchError = null;

        if (WebsiteDomainInputValidator::cleanSearchInput($this->searchQuery) === '') {
            $this->searchResults = [];
            $this->searchInputError = __('Please enter a domain name first.');
            $this->searchCanSubmit = false;

            return;
        }

        $validationMessage = WebsiteDomainInputValidator::validateSearchInput($this->searchQuery);

        if ($validationMessage !== null) {
            $this->searchResults = [];
            $this->searchInputError = $validationMessage;
            $this->searchCanSubmit = false;

            return;
        }

        $query = WebsiteDomainInputValidator::cleanSearchInput($this->searchQuery);
        $this->searchInputError = null;
        $this->searchCanSubmit = true;

        $client = $this->registrar();

        if (! $client->isConfigured()) {
            $this->searchError = $this->configurationHelpMessage();

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
            $this->searchError = $this->domainProvider === 'namecheap'
                ? __('No available domains in this list. Try a more unique name (e.g. yourbrand-studio-2026) or type a full domain like yourbrand.com.')
                : __('No available domains found. Try another search.');
        }
    }

    public function selectDomain(string $domain, ?string $checkoutUrl = null): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($domain);

        if ($validated['error'] !== null) {
            $this->searchError = $validated['error'];
            $this->selectedDomain = null;
            $this->checkoutUrl = null;
            $this->step = 'search';

            return;
        }

        $domain = $validated['domain'];

        try {
            $this->assertDomainNotUsedByAnotherSite($domain);
        } catch (ValidationException $e) {
            $this->searchError = $e->validator->errors()->first('domain');
            $this->selectedDomain = null;
            $this->checkoutUrl = null;
            $this->step = 'search';

            return;
        }

        $client = $this->registrar();

        if (! $client->isDomainAvailable($domain)) {
            $this->searchError = __('Sorry, this domain was just taken. Please pick another.');
            $this->selectedDomain = null;
            $this->checkoutUrl = null;
            $this->step = 'search';

            return;
        }

        $this->selectedDomain = $domain;
        $this->checkoutUrl = $checkoutUrl ?: $client->checkoutUrl($domain);
        $this->dnsVerified = false;
        $this->dnsRecordStatuses = [];
        $this->dnsVerifyMessage = null;
        $this->resumePendingSelection = false;
        $this->persistPendingDomain('review');
        $this->step = 'review';
    }

    #[On('persist-publish-wizard-state')]
    public function persistPublishWizardState(): void
    {
        if (blank($this->selectedDomain)) {
            return;
        }

        $this->persistPendingDomain('search');
    }

    public function clearSelectedDomain(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);

        $this->selectedDomain = null;
        $this->checkoutUrl = null;
        $this->dnsVerified = false;
        $this->dnsRecordStatuses = [];
        $this->dnsVerifyMessage = null;
        $this->resumePendingSelection = false;
        $this->step = 'search';
        $this->clearPendingDomain();
    }

    public function continueWithSelectedDomain(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);
        abort_unless($this->selectedDomain, 422);

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($this->selectedDomain);

        if ($validated['error'] !== null) {
            $this->searchError = $validated['error'];
            $this->clearSelectedDomain();

            return;
        }

        $this->selectedDomain = $validated['domain'];
        $this->resumePendingSelection = false;
        $this->step = 'review';
        $this->persistPendingDomain('review');

        if (! filled($this->checkoutUrl)) {
            $this->checkoutUrl = $this->registrar()->checkoutUrl($this->selectedDomain);
        }
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

        if ($this->step === 'search' && filled($this->selectedDomain)) {
            $this->resumePendingSelection = true;
        }

        $this->persistPendingDomain($this->step);
    }

    public function goToRegister(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);
        abort_unless($this->selectedDomain, 422);

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($this->selectedDomain);

        if ($validated['error'] !== null) {
            throw ValidationException::withMessages(['domain' => $validated['error']]);
        }

        $domain = $validated['domain'];
        $this->assertDomainNotUsedByAnotherSite($domain);

        $client = $this->registrar();

        if (! $client->isDomainAvailable($domain)) {
            $this->searchError = __('Sorry, this domain was just taken. Please pick another.');
            $this->selectedDomain = null;
            $this->checkoutUrl = null;
            $this->step = 'search';
            $this->clearPendingDomain();

            throw ValidationException::withMessages([
                'domain' => __('Sorry, this domain was just taken. Please pick another.'),
            ]);
        }

        $this->selectedDomain = $domain;
        $this->persistPendingDomain('register');
        $this->step = 'register';
    }

    public function openRegistrarCheckout(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);
        abort_unless($this->selectedDomain, 422);

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($this->selectedDomain);

        if ($validated['error'] !== null) {
            throw ValidationException::withMessages(['domain' => $validated['error']]);
        }

        $domain = $validated['domain'];
        $client = $this->registrar();

        if (! $client->isDomainAvailable($domain)) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is no longer available for registration. Search for another name.'),
            ]);
        }

        if (! filled($this->checkoutUrl)) {
            $this->checkoutUrl = $client->checkoutUrl($domain);
        }

        $this->js('window.open('.json_encode($this->checkoutUrl).', "_blank", "noopener,noreferrer")');
        $this->persistPendingDomain('dns');
        $this->step = 'dns';
        $this->dnsVerified = false;
        $this->dnsRecordStatuses = [];
        $this->dnsVerifyMessage = __('Add the DNS records, then click Verify DNS. Changes can take 24–48 hours.');
    }

    public function verifyDns(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);
        $this->throttleDnsVerify();
        abort_unless($this->selectedDomain, 422);

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($this->selectedDomain);

        if ($validated['error'] !== null) {
            throw ValidationException::withMessages(['domain' => $validated['error']]);
        }

        $result = app(WebsiteCustomDomainDnsVerifier::class)->verify($validated['domain']);
        $this->dnsRecordStatuses = $result['records'];
        $this->dnsVerified = (bool) $result['verified'];

        $this->dnsVerifyMessage = $this->dnsVerified
            ? __('DNS looks correct. You can save and publish your site.')
            : __('Some records are still pending or incorrect. DNS can take 24–48 hours to update; try again later.');
    }

    public function connectAndPublish(): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($this->websiteSite);
        $this->throttlePublish();
        abort_unless($this->selectedDomain, 422);

        if (! $this->dnsVerified) {
            throw ValidationException::withMessages([
                'domain' => __('Verify DNS before publishing. Use Verify DNS after your records are set.'),
            ]);
        }

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($this->selectedDomain);

        if ($validated['error'] !== null) {
            throw ValidationException::withMessages(['domain' => $validated['error']]);
        }

        $domain = $validated['domain'];
        $this->assertDomainNotUsedByAnotherSite($domain);
        $this->assertPublishTargetsAreAllowed();

        $verify = app(WebsiteCustomDomainDnsVerifier::class)->verify($domain);

        if (! $verify['verified']) {
            $this->dnsRecordStatuses = $verify['records'];
            $this->dnsVerified = false;

            throw ValidationException::withMessages([
                'domain' => __('DNS verification failed. Fix your records and click Verify DNS again.'),
            ]);
        }

        $this->websiteSite->forceFill([
            'custom_domain' => $domain,
            'custom_domain_connected_at' => now(),
            'pending_custom_domain' => null,
            'publish_wizard_step' => null,
            'status' => WebsiteSite::STATUS_PUBLISHED,
            'published_at' => $this->websiteSite->published_at ?? now(),
            'subdomain_slug' => null,
        ])->save();

        $this->websiteSite->refresh();

        $this->step = 'done';

        session()->forget([
            'website_publish_site_id',
            'website_publish_mode',
            'website_publish_queued_at',
            'website_publish_provider',
            'website_publish_domain',
        ]);

        $providerLabel = WebsiteDomainRegistrarResolver::label($this->domainProvider);

        $this->dispatch(
            'app-toast',
            type: 'success',
            title: __('Site published'),
            message: __('Your site is published at :url', [
                'url' => WebsiteCustomDomain::publicUrl($domain) ?? $domain,
            ]),
        );
    }

    public function closeEmbedded(): void
    {
        $this->persistPublishWizardState();
        $this->dispatch('publish-domain-flow-closed');
    }

    public function render(): View
    {
        $registrar = $this->registrar();
        $providerLabel = WebsiteDomainRegistrarResolver::label($this->domainProvider);

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
            'domainProvider' => $this->domainProvider,
            'providerLabel' => $providerLabel,
            'displayHost' => fn (string $domain): string => $registrar->displayHost($domain),
            'bareDomain' => fn (string $domain): string => WebsiteDomainInputValidator::normalizeRegisteredDomain($domain),
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

    protected function persistPendingDomain(string $step): void
    {
        if ($this->selectedDomain === null || $this->selectedDomain === '') {
            return;
        }

        $validated = WebsiteDomainInputValidator::validateRegisteredDomain($this->selectedDomain);

        if ($validated['domain'] === null) {
            return;
        }

        $this->websiteSite->forceFill([
            'pending_custom_domain' => $validated['domain'],
            'publish_wizard_step' => $step,
        ])->save();

        $this->websiteSite->refresh();
    }

    protected function clearPendingDomain(): void
    {
        $this->websiteSite->forceFill([
            'pending_custom_domain' => null,
            'publish_wizard_step' => null,
        ])->save();

        $this->websiteSite->refresh();
    }

    protected function assertDomainNotUsedByAnotherSite(string $domain): void
    {
        $taken = WebsiteSite::query()
            ->where(function ($query) use ($domain): void {
                $query->where('custom_domain', $domain)
                    ->orWhere('pending_custom_domain', $domain);
            })
            ->whereKeyNot($this->websiteSite->id)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already connected to another site in your account.'),
            ]);
        }
    }

    protected function assertPublishTargetsAreAllowed(): void
    {
        foreach (WebsiteCustomDomain::dnsRecords() as $record) {
            $target = $record['points_to'] ?? '';

            if (! is_string($target) || str_contains($target, ' ')) {
                continue;
            }

            if (WebsiteDomainInputValidator::isForbiddenHostOrIp($target)) {
                throw ValidationException::withMessages([
                    'domain' => __('Publishing is blocked: DNS targets must not use localhost or private IPs. Set WEBSITE_PUBLISH_A_RECORD and APP_URL for production.'),
                ]);
            }
        }
    }

    protected function throttleDomainSearch(): void
    {
        $key = 'website-domain-search:'.auth()->id();
        $maxAttempts = 20;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'search' => __('Too many searches. Try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    protected function throttleDnsVerify(): void
    {
        $key = 'website-domain-dns-verify:'.auth()->id();
        $maxAttempts = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'domain' => __('Too many DNS checks. Try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    protected function throttlePublish(): void
    {
        $key = 'website-domain-publish:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 8)) {
            throw ValidationException::withMessages([
                'domain' => __('Too many publish attempts. Try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    protected function registrar(): GoDaddyDomainClient|NamecheapDomainClient
    {
        return WebsiteDomainRegistrarResolver::resolve($this->domainProvider);
    }

    protected function configurationHelpMessage(): string
    {
        return match ($this->domainProvider) {
            'namecheap' => __('Namecheap API is not configured. Add NAMECHEAP_API_USER, NAMECHEAP_API_KEY, NAMECHEAP_USERNAME, and NAMECHEAP_CLIENT_IP to .env.'),
            default => __('GoDaddy API is not configured. Add GODADDY_API_KEY and GODADDY_API_SECRET to .env.'),
        };
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