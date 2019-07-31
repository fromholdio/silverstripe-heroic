<?php

namespace Fromholdio\Heroic;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Manifest\ModuleLoader;

class Heroic
{
    use Extensible;
    use Injectable;
    use Configurable;

    const MODE_SITE = 'site';
    const MODE_PARENT = 'parent';
    const MODE_CUSTOM = 'custom';
    const MODE_COLOR = 'color';
    const MODE_IMAGE = 'image';
    const MODE_VIDEO = 'video';
    const MODE_PALETTE = 'palette';

    private static $mode_options = [
        'height' => false,
        'align_vertical' => [
            self::MODE_PARENT => 'From parent',
            self::MODE_SITE => 'Site default',
            'middle' => 'Middle',
            'top' => 'Top',
            'bottom' => 'Bottom'
        ],
        'align_horizontal' => [
            self::MODE_PARENT => 'From parent',
            self::MODE_SITE => 'Site default',
            'center' => 'Centre',
            'left' => 'Left',
            'right' => 'Right'
        ],
        'text_color' => [
            self::MODE_PARENT => 'Parent text colour',
            self::MODE_SITE => 'Site default',
            self::MODE_PALETTE => 'Choose from site palette',
            self::MODE_CUSTOM => 'Select a custom colour'
        ],
        'cta_color' => [
            self::MODE_PARENT => 'Parent button colour',
            self::MODE_SITE => 'Site default',
            self::MODE_PALETTE => 'Choose from site button colours'
        ],
        'background' => [
            self::MODE_PARENT => 'Inherit from parent',
            self::MODE_SITE => 'Use site default',
            self::MODE_COLOR => 'Set a colour',
            self::MODE_IMAGE => 'Upload/select an image',
            self::MODE_VIDEO => 'Attach a vimeo.com video'
        ],
        'background_color' => [
            self::MODE_SITE => 'Use site default background colour',
            self::MODE_PALETTE => 'Choose from site palette',
            self::MODE_CUSTOM => 'Select a custom colour'
        ]
    ];

    private static $palettes = [];
    private static $enable_multisites = true;
    private static $enable_featureimage = true;
    private static $enable_slides = true;

    public static function get_mode_options($key)
    {
        $config = Config::inst()->get(self::class, 'mode_options');
        if ($config && is_array($config) && isset($config[$key])) {
            $options = $config[$key];
            if (is_array($options) && count($options) > 0) {
                return $options;
            }
        }
        return null;
    }

    public static function get_palette($key)
    {
        $config = Config::inst()->get(self::class, 'palettes');
        if ($config && is_array($config) && isset($config[$key])) {
            $palette = $config[$key];
            if (is_array($palette) && count($palette) > 0) {
                return $palette;
            }
        }
        return null;
    }

    public static function get_palette_for_dropdown($key)
    {
        $palette = self::get_palette($key);
        if ($palette) {
            $dropdownSource = [];
            foreach ($palette as $name => $data) {
                if (isset($data['hex'])) {
                    $dropdownSource[$name] = '#' . $data['hex'];
                }
            }
            if (count($dropdownSource) > 0) {
                return $dropdownSource;
            }
        }
        return null;
    }

    public static function get_is_slides_enabled()
    {
        return (bool) Config::inst()->get(self::class, 'enable_slides');
    }

    public static function get_is_multisites_enabled()
    {
        $exists = ModuleLoader::inst()->getManifest()
            ->moduleExists('symbiote/silverstripe-multisites');

        if (!$exists) {
            return false;
        }

        return (bool) Config::inst()->get(self::class, 'enable_multisites');
    }

    public static function get_is_featureimage_enabled()
    {
        $exists = ModuleLoader::inst()->getManifest()
            ->moduleExists('fromholdio/silverstripe-featureimage');

        if (!$exists) {
            return false;
        }

        return (bool) Config::inst()->get(self::class, 'enable_featureimage');
    }
}