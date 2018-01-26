<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:24
 */

namespace Alpipego\Resizefly\Admin\Sizes;

use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

class SizesField extends AbstractOption implements OptionInterface
{

    /**
     * @var string option name for out of sync image sizes
     */
    const OUTOFSYNC = 'resizefly_sizes_outofsync';
    /**
     * @var array
     */
    private $registeredSizes = [];
    /**
     * @var array
     */
    private $savedSizes = [];
    /**
     * @var PageInterface
     */
    private $page;

    /**
     * RestrictSizesField constructor.
     *
     * @param PageInterface $page
     * @param OptionsSectionInterface|string $section
     * @param string $pluginPath
     */
    public function __construct(PageInterface $page, OptionsSectionInterface $section, $pluginPath)
    {
        $this->page         = $page;
        $this->optionsField = [
            'id'    => 'resizefly_sizes',
            'title' => esc_attr__('Image Sizes', 'resizefly'),
            'args'  => ['class' => 'hide-if-no-js', 'label_for' => 'resizefly_sizes_section'],
        ];
        parent::__construct($page, $section, $pluginPath);
    }

    public function run()
    {
        add_action('after_setup_theme', function () {
            // get registered and saved image sizes
            $this->savedSizes      = (array)get_option('resizefly_sizes', []);
            $this->registeredSizes = $this->getRegisteredImageSizes();

            // set defaults
            add_option('resizefly_sizes', $this->registeredSizes);
            add_option(self::OUTOFSYNC, []);
        }, 11);

        add_action('current_screen', function (\WP_Screen $screen) {
            // check if saved and registered image sizes are in sync
            if ($screen->id === $this->page->getId()) {
                $this->imageSizesSynced();
            }

            // add inline styles
            add_action('admin_enqueue_scripts', function () {
                wp_add_inline_style('wp-admin', '.rzf-image-sizes th {padding-left:10px;}');
            });
        });

        // see if there are out-of-sync image sizes
        add_action('admin_notices', [$this, 'adminSyncNotice']);

        // check for out of sync image sizes after theme switch, theme or plugin updates, (de)activation of plugins
        add_action('after_switch_theme', [$this, 'imageSizesSynced']);
        add_action('upgrader_process_complete', [$this, 'imageSizesSynced']);
        add_action('activated_plugin', [$this, 'imageSizesSynced']);
        add_action('deactivate_plugin', [$this, 'imageSizesSynced']);

        parent::run();
    }

    /**
     * Gets and normalizes built in and registered image sizes
     *
     * @return array
     */
    protected function getRegisteredImageSizes()
    {
        $intermediate = get_intermediate_image_sizes();
        $additional   = wp_get_additional_image_sizes();
        $sizes        = [];

        foreach ($intermediate as $size) {
            if (! array_key_exists($size, $additional)) {
                $sizes[$size]['width']  = (int)get_option("{$size}_size_w");
                $sizes[$size]['height'] = (int)get_option("{$size}_size_h");
                $sizes[$size]['crop']   = get_option("{$size}_crop");
            } elseif (isset($additional[$size])) {
                $sizes[$size] = [
                    'width'  => (int)$additional[$size]['width'],
                    'height' => (int)$additional[$size]['height'],
                    'crop'   => $additional[$size]['crop'],
                ];
            }

            if (array_key_exists($size, $sizes)) {
                $sizes[$size]['active'] = true;
            }
        }

        return $sizes;
    }

    /**
     * Check if the saved an (externally) registered image sizes are in sync
     */
    public function imageSizesSynced()
    {
        $savedSizes      = array_map([$this, 'normalizeSizes'], $this->savedSizes);
        $registeredSizes = array_map([$this, 'normalizeSizes'], $this->registeredSizes);

        $new = array_udiff_assoc(
            $registeredSizes,
            $savedSizes,
            [$this, 'compareSizes']
        );

        $missing = array_udiff_assoc(
            $savedSizes,
            $registeredSizes,
            [$this, 'compareSizes']
        );

        $updated = array_intersect_key($new, $missing);
        $new     = array_diff_key($new, $updated);
        $missing = array_diff_key($missing, $updated);

        update_option(self::OUTOFSYNC, ['new' => $new, 'updated' => $updated, 'missing' => $missing]);
    }

    /**
     * Add a callback to settings field
     *
     * @return void
     */
    public function callback()
    {
        $args                = $this->optionsField;
        $args['image_sizes'] = $this->getImageSizes();
        $args['out_of_sync'] = get_option(self::OUTOFSYNC, []);

        $this->includeView($this->optionsField['id'], $args);
    }

    /**
     * @return array
     */
    protected function getImageSizes()
    {
        array_walk($this->registeredSizes, function (&$size, $name) {
            if (array_key_exists($name, $this->savedSizes)) {
                $size['active'] = $this->savedSizes[$name]['active'];
            }
        });

        return $this->sortImageSizes($this->registeredSizes);
    }

    /**
     * @param array $sizes
     *
     * @return mixed
     */
    protected function sortImageSizes($sizes)
    {
        // order results
        uasort($sizes, function ($a, $b) {
            if ($a['width'] === $b['width']) {
                return $a['height'] - $b['height'];
            }

            return $a['width'] - $b['width'];
        });

        return $sizes;
    }

    /**
     * Sanitize values added to this settings field
     *
     * @param array $sizes
     *
     * @return array
     * @internal param mixed $value value to sanititze
     *
     */
    public function sanitize($sizes)
    {
        // cast types
        foreach ($sizes as &$size) {
            $size['width']  = (int)$size['width'];
            $size['height'] = (int)$size['height'];
            $size['active'] = isset($size['active']);
            $crop           = explode(', ', $size['crop']);
            $size['crop']   = (bool)$crop[0];
            if (is_array($crop) && count($crop) === 2) {
                $size['crop'] = array_values($crop);
            }
        }

        return $sizes;
    }

    /**
     * Outputs the admin notice
     */
    public function adminSyncNotice()
    {
        if (empty(array_filter(get_option(self::OUTOFSYNC, []))) || ! (bool)get_option('resizefly_restrict_sizes', false)) {
            return;
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <?=
                sprintf(
                    wp_kses(
                        __(
                            'The registered and saved image sizes for ResizeFly are out of sync. <a href="%s"><button type="button">Please review them here.</button></a>',
                            'resizefly'
                        ),
                        ['a' => ['href' => []], 'button' => ['type' => ['button']]]
                    ),
                    esc_url(menu_page_url($this->page->getSlug(), false) . '#rzf-image-sizes')
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function compareSizeNames($a, $b)
    {
        return $a === $b ? 0 : 1;
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function compareSizes($a, $b)
    {
        ksort($a);
        ksort($b);

        return $a === $b ? 0 : 1;
    }

    /**
     * @param array $value normalize input array
     *
     * @return array
     */
    private function normalizeSizes($value)
    {
        unset($value['active']);
        if (! is_array($value['crop'])) {
            $value['crop'] = (bool)$value['crop'];
        }

        return $value;
    }
}
