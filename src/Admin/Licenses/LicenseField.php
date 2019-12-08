<?php

namespace Alpipego\Resizefly\Admin\Licenses;

use Alpipego\Resizefly\Addon\EddAddonUpdater;
use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

class LicenseField extends AbstractOption implements OptionInterface
{
    const STORE_URL        = 'https://www.resizefly.com';
    private $errorMessages = [];
    private $addon;
    private $license;
    private $statusKey;
    private $statusKeyVerbose;

    public function __construct(PageInterface $page, OptionsSectionInterface $section, $pluginPath, array $addonData)
    {
        $this->optionsField     = [
            'id'    => 'resizefly_license_key_'.$addonData['name'],
            'title' => $addonData['nicename'],
        ];
        $this->addon            = $addonData;
        $this->license          = get_option($this->optionsField['id']);
        $this->statusKey        = 'resizefly_license_status_'.$this->addon['name'];
        $this->statusKeyVerbose = 'resizefly_license_status_verbose_'.$this->addon['name'];
        $this->errorMessages    = [
            'expired'             => __('Your license key expired on %s.', 'resizefly'),
            'revoked'             => __('Your license key has been disabled.', 'resizefly'),
            'missing'             => __('Invalid license.', 'resizefly'),
            'invalid'             => __('Your license is not active for this URL.', 'resizefly'),
            'site_inactive'       => __('Your license is not active for this URL.', 'resizefly'),
            'item_name_mismatch'  => __('This appears to be an invalid license key for %s.', 'resizefly'),
            'no_activations_left' => __('Your license key has reached its activation limit.', 'resizefly'),
            'default'             => __('An error occurred, please try again.', 'resizefly'),
            'unknown_addon'       => __('Unknown addon', 'resizefly'),
        ];

        add_option($this->optionsField['id']);
        add_option($this->statusKey);
        add_option($this->statusKeyVerbose);

        parent::__construct($page, $section, $pluginPath);
    }

    public function run()
    {
        add_action('admin_init', [$this, 'checkUpdates']);
        parent::run();
    }

    public function checkUpdates()
    {
        new EddAddonUpdater(self::STORE_URL, $this->addon['file'], [
            'version' => $this->addon['version'],
            'license' => $this->license,
            'item_id' => ! empty($this->addon['id']) ? (int) $this->addon['id'] : 0,
            'author'  => ! empty($this->addon['author']) ? $this->addon['author'] : 'Alexander Goller',
            'beta'    => ! empty($this->addon['beta']),
        ]
        );
    }

    /**
     * Add a callback to settings field.
     */
    public function callback()
    {
        $this->includeView('resizefly-addon-license', [
            'id'             => $this->optionsField['id'],
            'addon'          => $this->addon,
            'license'        => $this->license,
            'status'         => get_option($this->statusKey),
            'status_verbose' => get_option($this->statusKeyVerbose),
        ]);
    }

    /**
     * Sanitize values added to this settings field.
     *
     * @param mixed $value value to sanititze
     *
     * @return mixed
     */
    public function sanitize($value)
    {
        //		if ( $value !== $this->license ) {
        $this->activateLicense($value);
        //		}

        if (empty(trim($value)) && $value !== $this->license) {
            $value = $this->deactivateLicense($this->license);
        }

        return sanitize_text_field($value);
    }

    public function validateLicense($license)
    {
        $response = $this->remotePost('check_license', $license);

        if (is_wp_error($response) || 200 === wp_remote_retrieve_response_code($response)) {
            $message = is_wp_error($response) ? $response->get_error_message() : $this->errorMessages['default'];
            update_option($this->statusKeyVerbose, $message);

            return false;
        }

        $licenseData = json_decode(wp_remote_retrieve_body($response));

        return 'valid' === $licenseData->license;
    }

    private function activateLicense($license)
    {
        $response = $this->remotePost('activate_license', $license);

        $message = __('valid', 'resizefly');

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $message = is_wp_error($response) ? $response->get_error_message() : $this->errorMessages['default'];
        } else {
            $licenseData = json_decode(wp_remote_retrieve_body($response));
            if (false === $licenseData->success) {
                $message = $this->handleError($licenseData);
            }

            update_option($this->statusKey, $licenseData->license);
        }

        update_option($this->statusKeyVerbose, $message);
    }

    private function remotePost($action, $license)
    {
        if (empty((int) $this->addon['id'])) {
            return new \WP_Error('empty_addon_id', $this->errorMessages['unknown_addon']);
        }

        return wp_remote_post(
            self::STORE_URL,
            [
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => [
                    'edd_action' => $action,
                    'license'    => $license,
                    'item_id'    => ! empty((int) $this->addon['id']) ? (int) $this->addon['id'] : 0,
                    'url'        => home_url(),
                ],
            ]
        );
    }

    private function handleError($licenseData)
    {
        $placeholder = '';
        if ('expired' === $licenseData->error) {
            $placeholder = date_i18n(get_option('date_format'), strtotime($licenseData->expires, current_time('timestamp')));
        }
        if ('item_name_mismatch' === $licenseData->error) {
            $placeholder = $this->addon['nicename'];
        }

        return sprintf($this->errorMessages[$licenseData->error], $placeholder);
    }

    private function deactivateLicense($license)
    {
        $response = $this->remotePost('deactivate_license', $license);
        if (is_wp_error($response) || 200 === wp_remote_retrieve_response_code($response)) {
            $message = is_wp_error($response) ? $response->get_error_message() : $this->errorMessages['default'];
            update_option($this->statusKeyVerbose, $message);

            return $license;
        }

        $licenseData = json_decode(wp_remote_retrieve_body($response));
        // $license_data->license will be either "deactivated" or "failed"
        if ('deactivated' === $licenseData->license) {
            update_option($this->statusKey, $licenseData->license);
            update_option($this->statusKeyVerbose, $this->errorMessages['revoked']);
            $license = '';
        }

        return $license;
    }
}
