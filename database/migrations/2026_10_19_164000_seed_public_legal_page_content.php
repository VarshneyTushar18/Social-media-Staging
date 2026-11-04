<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'privacy_policy_title' => 'Privacy Policy',
            'privacy_policy_content' => <<<'HTML'
<p class="meta">Effective date: October 19, 2026 &nbsp;·&nbsp; Last updated: October 19, 2026</p>
<h2>1. Who we are</h2>
<p>Tech2Globe Tech ("Tech2Globe Tech," "we," "us," or "our") operates the software platform available at <a href="https://social.tech2globe.tech/portal/dashboard">https://social.tech2globe.tech/portal/dashboard</a> ("the Platform"), which provides tools for managing workspaces, connected channels, media, and social publishing workflows. This Privacy Policy explains the information processed through the Platform, how it is used, and the choices available to you.</p>
<p>This policy applies to visitors to our website and registered Platform users. If you use the Platform to manage a client's or another person's information, you are responsible for having the authority and permissions needed to provide that information.</p>
<h2>2. Information we collect</h2>
<p>We collect the following categories of information:</p>
<table>
<tr><th>Category</th><th>Examples</th></tr>
<tr><td>Account information</td><td>Name, email address, password (stored in protected form), and account settings</td></tr>
<tr><td>Connected channel information</td><td>Channel details and credentials or tokens you provide or authorize the Platform to store for supported integrations</td></tr>
<tr><td>Content you provide</td><td>Media, post content, captions, schedules, and other information you create or upload using available features</td></tr>
<tr><td>Service and usage data</td><td>Information generated when you use the Platform, such as account activity and technical logs</td></tr>
<tr><td>Support data</td><td>Information you provide when contacting support, including messages and attachments</td></tr>
</table>
<h2>3. How we use your information</h2>
<p>We use the information we collect to:</p>
<ul>
<li>Provide, operate, and maintain the Platform, including channel management and social publishing features you use</li>
<li>Authenticate you and keep your account secure</li>
<li>Support your publishing workflows and manage your workspace</li>
<li>Respond to support requests and communicate service updates</li>
<li>Improve and troubleshoot the Platform</li>
<li>Comply with legal obligations and enforce our Terms of Use</li>
</ul>
<p>We do not sell your personal information. We use connected-account information only to provide and maintain the features you use, subject to this policy and applicable third-party platform terms.</p>
<h2>4. Third-party platform data</h2>
<p>When you connect a supported third-party account, you authorize Tech2Globe Tech to process the information and permissions made available through the connection to provide the features you choose, such as managing a channel or publishing content.</p>
<div class="callout">
We use connected-account information to provide the functionality you request inside the Platform. We do not sell it. Information may be shared only as described in Section 5.
</div>
<p>Your use of connected platforms remains subject to each provider's own terms and privacy policy.</p>
<h2>5. How we share information</h2>
<p>We share information only in the following circumstances:</p>
<ul>
<li><strong>Service providers</strong> — vendors that support hosting, storage, email delivery, or other Platform operations</li>
<li><strong>Your team</strong> — information you share is visible to other members of your agency workspace, based on the roles and permissions you configure</li>
<li><strong>Legal requirements</strong> — where disclosure is required by law or necessary to protect the rights, property, or safety of Tech2Globe Tech, our users, or others</li>
<li><strong>Business transfers</strong> — if the operation of the Platform is transferred as part of a business transaction</li>
</ul>
<p>We do not share personal information with third parties for their independent marketing.</p>
<h2>6. Data retention</h2>
<p>We retain information while it is needed to provide the Platform, maintain your account, or meet legal obligations. If you delete a channel in the Platform, its stored channel record is removed from the application. If you delete your user account, the application processes the account deletion and deletion of its associated resources. Information may remain where required by law. The Platform's deletion controls do not delete your account with a third-party provider or guarantee revocation of permissions at that provider; revoke access with the provider as well when needed.</p>
<h2>7. Your rights and choices</h2>
<p>Depending on your location, you may have the right to request access to, correction of, or deletion of your personal information. You can remove a channel or delete your user account using the available in-app controls, or contact us using the details below. For connected third-party accounts, you may also need to revoke access directly with that provider. See our <a href="/delete-data">Data Deletion page</a>.</p>
<p>Users in the European Economic Area, UK, and similar jurisdictions have rights under applicable data protection law (such as the GDPR), including the right to lodge a complaint with a supervisory authority.</p>
<h2>8. Data security</h2>
<p>We take reasonable measures designed to protect information processed through the Platform. No system can be guaranteed completely secure.</p>
<h2>9. Children's privacy</h2>
<p>The Platform is intended for business use and is not directed to children. If you believe a child has provided personal information to us, contact us using the details below.</p>
<h2>10. International data transfers</h2>
<p>Your information may be processed in countries other than your own. Applicable data-protection requirements continue to apply to such processing.</p>
<h2>11. Changes to this policy</h2>
<p>We may update this Privacy Policy from time to time. Material changes will be communicated via email or an in-app notice. Continued use of the Platform after changes take effect constitutes acceptance of the revised policy.</p>
<h2>12. Contact us</h2>
<p>Questions about this policy or your data can be sent to:</p>
<p><strong>Email:</strong> <a href="mailto:support@tech2globe.tech">support@tech2globe.tech</a><br>
<strong>Website:</strong> <a href="https://social.tech2globe.tech/portal/dashboard">https://social.tech2globe.tech/portal/dashboard</a></p>
<footer>
© 2026 Tech2Globe Tech. All rights reserved. &nbsp;·&nbsp;
<a href="/terms-of-use">Terms of Use</a> &nbsp;·&nbsp;
<a href="/delete-data">Data Deletion</a>
</footer>
HTML,
            'terms_of_use_title' => 'Terms of Use',
            'terms_of_use_content' => <<<'HTML'
<p class="meta">Effective date: October 19, 2026 &nbsp;·&nbsp; Last updated: October 19, 2026</p>
<h2>1. Agreement to terms</h2>
<p>These Terms of Use ("Terms") govern your access to and use of the Tech2Globe Tech platform at <a href="https://social.tech2globe.tech/portal/dashboard">https://social.tech2globe.tech/portal/dashboard</a> ("the Platform"), operated by Tech2Globe Tech ("we," "us," "our"). The Platform provides tools for managing workspaces, connected channels, media, and social publishing workflows. By creating an account or using the Platform, you agree to these Terms. If you do not agree, do not use the Platform.</p>
<h2>2. Who can use Tech2Globe Tech</h2>
<p>The Platform is intended for business use by marketing agencies, freelancers, and organizations managing digital marketing services on behalf of clients. You must be at least 18 years old and able to form a binding contract to use the Platform. If you use the Platform on behalf of an organization, you confirm you have authority to bind that organization to these Terms.</p>
<h2>3. Your account</h2>
<p>You are responsible for maintaining the confidentiality of your account credentials and for activity that occurs under your account. Notify us at <a href="mailto:support@tech2globe.tech">support@tech2globe.tech</a> if you suspect unauthorized use of your account.</p>
<h2>4. Connected third-party accounts</h2>
<p>The Platform may allow you to connect supported third-party social accounts for features such as managing channels and publishing content. Available providers and capabilities may change. By connecting an account, you:</p>
<ul>
<li>Confirm you have the legal right and authority to connect and manage that account</li>
<li>Authorize Tech2Globe Tech to access and use the information and permissions you provide as needed to operate the features you use</li>
<li>Agree to comply with the terms of service and content policies of each connected platform</li>
</ul>
<p>Tech2Globe Tech is not responsible for actions taken by third-party platforms against your accounts, including suspensions, changes to their APIs or policies, or content removal.</p>
<h2>5. Acceptable use</h2>
<p>You agree not to use the Platform to:</p>
<ul>
<li>Post or schedule content that is unlawful, defamatory, infringing, or violates a connected platform's policies</li>
<li>Attempt to gain unauthorized access to the Platform, other accounts, or connected systems</li>
<li>Reverse-engineer, resell, or white-label the Platform except as expressly permitted under a separate agreement</li>
<li>Use the Platform to send spam or engage in deceptive or manipulative marketing practices</li>
<li>Interfere with or disrupt the integrity or performance of the Platform</li>
</ul>
<p>We reserve the right to suspend or terminate accounts that violate this section.</p>
<h2>6. Subscription plans and billing</h2>
<p>Certain features require a paid subscription. Fees are billed in advance on a recurring basis as described at checkout. Except where required by law, fees are non-refundable. We may change pricing with reasonable advance notice; continued use after a price change constitutes acceptance of the new pricing.</p>
<h2>7. Client and team data</h2>
<p>If you use the Platform to manage clients or invite team members, you are responsible for obtaining any necessary consent from those clients or team members for the data you input into the Platform on their behalf, and for managing the permissions you grant them within your workspace.</p>
<h2>8. Intellectual property</h2>
<p>The Platform and its software, design, and branding are protected by intellectual property laws and are provided to you under the applicable license and these Terms. You retain ownership of the content you create and upload. You grant us a limited license to host, process, and transmit that content as needed to provide the service.</p>
<h2>9. Third-party platform changes</h2>
<p>Features that depend on third-party services or APIs may change, be limited, or become unavailable if a provider changes its API, policies, or access terms. We cannot guarantee uninterrupted availability of any third-party integration.</p>
<h2>10. Disclaimers</h2>
<p>The Platform is provided "as is" and "as available" without warranties of any kind, express or implied. We do not warrant that the Platform will be uninterrupted, error-free, or that any specific marketing or business result will be achieved through its use.</p>
<h2>11. Limitation of liability</h2>
<p>To the maximum extent permitted by law, Tech2Globe Tech will not be liable for indirect, incidental, special, or consequential damages, or for loss of profits, data, or business, arising from your use of the Platform.</p>
<h2>12. Termination</h2>
<p>You may cancel your account at any time. We may suspend or terminate your access to the Platform if you violate these Terms or if required by law. Upon termination, your right to use the Platform ends, and Sections 8, 10, 11, and 13 survive termination.</p>
<h2>13. Governing law</h2>
<p>These Terms are governed by the laws applicable to Tech2Globe Tech, without regard to conflict-of-law principles, unless otherwise required by applicable law.</p>
<h2>14. Changes to these Terms</h2>
<p>We may update these Terms from time to time. Material changes will be communicated via email or an in-app notice. Continued use of the Platform after changes take effect constitutes acceptance of the revised Terms.</p>
<h2>15. Contact us</h2>
<p><strong>Email:</strong> <a href="mailto:support@tech2globe.tech">support@tech2globe.tech</a><br>
<strong>Website:</strong> <a href="https://social.tech2globe.tech/portal/dashboard">https://social.tech2globe.tech/portal/dashboard</a></p>
<div class="callout">This page is general information and is not legal advice. Have these Terms reviewed by a lawyer qualified in your jurisdiction before relying on them as a binding agreement with customers.</div>
<footer>
© 2026 Tech2Globe Tech. All rights reserved. &nbsp;·&nbsp;
<a href="/privacy-policy">Privacy Policy</a> &nbsp;·&nbsp;
<a href="/delete-data">Data Deletion</a>
</footer>
HTML,
            'delete_data_title' => 'Data Deletion',
            'delete_data_content' => <<<'HTML'
<a class="brand" href="https://social.tech2globe.tech/portal/dashboard">Tech2Globe Tech</a>
<div class="card">
<h2>Request Data Deletion</h2>
<p>You can remove connected-channel information from Tech2Globe Tech or request deletion of your user account and associated information.</p>
<div class="steps">
<p><strong>Remove a channel:</strong> Sign in, open Channels, select the channel, and use the available Delete channel control. This removes the channel record from the application. It does not delete your account with the third-party provider or necessarily revoke that provider's authorization; revoke access with the provider directly as well.</p>
<p><strong>Delete your user account:</strong> Use the Delete account control in your account settings and confirm with your password. The application processes deletion of your user account and its associated resources. Information may be retained where required by law.</p>
<p><strong>Email request:</strong> If you cannot use the in-app controls or need help with a data request, email <a href="mailto:support@tech2globe.tech">support@tech2globe.tech</a> from the address associated with your account and describe your request. We may ask you to verify your identity before acting.</p>
</div>
<a class="btn" href="mailto:support@tech2globe.tech?subject=Data%20Deletion%20Request">Request Data Deletion</a>
<p class="note">Deleting a channel in Tech2Globe Tech does not delete the underlying third-party account. Account deletion is separate from channel deletion. Your request may be subject to legal retention requirements, and the Platform's deletion controls do not guarantee deletion of information held by third-party providers.</p>
</div>
<footer>
<a href="/privacy-policy">Privacy Policy</a> &nbsp;·&nbsp;
<a href="/terms-of-use">Terms of Use</a><br>
© 2026 Tech2Globe Tech
</footer>
HTML,
        ];

        $now = now();
        $rows = [];

        foreach ($defaults as $name => $value) {
            $rows[] = [
                'name' => $name,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('options')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        // Keep legal page settings on rollback to avoid deleting admin-edited content.
    }
};
