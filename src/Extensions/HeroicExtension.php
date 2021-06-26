<?php

namespace Fromholdio\Heroic\Extensions;

use Fromholdio\SimpleVideo\Model\SimpleVideo;
use Fromholdio\Heroic\Heroic;
use Fromholdio\Heroic\Model\HeroicSlide;
use Fromholdio\MiniGridField\Forms\HasOneMiniGridField;
use Heyday\ColorPalette\Fields\ColorPaletteField;
use RyanPotter\SilverStripeColorField\Forms\ColorField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Security;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class HeroicExtension extends DataExtension
{
    private static $db = [
        'DoShowHeroic' => 'Boolean',
        'UsePageTitleForHeroicHeadline' => 'Boolean',
        'HeroicHeightMode' => 'Varchar(20)',
        'HeroicAlignVertical' => 'Varchar(20)',
        'HeroicAlignHorizontal' => 'Varchar(20)',
        'HeroicTextColorMode' => 'Varchar(20)',
        'HeroicTextColorKey' => 'Varchar',
        'HeroicTextColorCustom' => 'Varchar(7)',
        'HeroicCTAColorMode' => 'Varchar(20)',
        'HeroicCTAColorKey' => 'Varchar(7)',
        'HeroicBackgroundMode' => 'Varchar(20)',
        'HeroicBackgroundColorMode' => 'Varchar(20)',
        'HeroicBackgroundColorKey' => 'Varchar',
        'HeroicBackgroundColorCustom' => 'Varchar(7)',
        'HeroicBackgroundOverlayOpacity' => 'Int',
        'HeroicBackgroundOverlayColor' => 'Varchar(7)',
        'IsHeroicBackgroundVideoAutoplay' => 'Boolean',
        'UsePageFeatureImageForHeroicBackgroundImage' => 'Boolean'
    ];

    private static $has_one = [
        'HeroicBackgroundImage' => Image::class,
        'HeroicBackgroundVideo' => SimpleVideo::class
    ];

    private static $owns = [
        'HeroicBackgroundImage',
        'HeroicBackgroundVideo'
    ];

    private static $cascade_deletes = [
        'HeroicBackgroundVideo'
    ];

    private static $cascade_duplicates = [
        'HeroicBackgroundVideo'
    ];

    private static $defaults = [
        'DoShowHeroic' => true,
        'UsePageTitleForHeroicHeadline' => true,
        'IsHeroicBackgroundVideoAutoplay' => true,
        'UsePageFeatureImageForHeroicBackgroundImage' => true
    ];

    private static $field_labels = [
        'DoShowHeroic' => 'Enable Hero',
        'UsePageTitleForHeroicHeadline' => 'Use Page Title for Headline',
        'HeroicHeightMode' => 'Height',
        'HeroicAlignVertical' => 'Vertical Content Align',
        'HeroicAlignHorizontal' => 'Horizontal Content Align',
        'HeroicTextColorMode' => 'Text Colour',
        'HeroicTextColorKey' => 'Choose from Palette',
        'HeroicTextColorCustom' => 'Pick a Colour',
        'HeroicCTAColorMode' => 'Button Colour',
        'HeroicCTAColorKey' => 'Choose from Palette',
        'HeroicBackgroundMode' => 'Type',
        'HeroicBackgroundColorMode' => 'Colour',
        'HeroicBackgroundColorKey' => 'Choose from Palette',
        'HeroicBackgroundColorCustom' => 'Pick a Colour',
        'HeroicBackgroundImage' => 'Upload/select Image',
        'HeroicBackgroundVideo' => 'Attach Video',
        'HeroicBackgroundOverlayOpacity' => 'Opacity',
        'HeroicBackgroundOverlayColor' => 'Colour',
        'IsHeroicBackgroundVideoAutoplay' => 'Autoplay as silent background-style video',
        'UsePageFeatureImageForHeroicBackgroundImage' => 'Use Page Feature Image'
    ];

    private static $enable_heroic = true;

    public function getDoShowHero()
    {
        $doShow = $this->getOwner()->getIsHeroicEnabled();
        if ($doShow) {
            $doShow = (bool) $this->getOwner()->DoShowHeroic;
        }
        $this->getOwner()->invokeWithExtensions('updateDoShowHero', $doShow);
        return $doShow;
    }

    public function getIsHeroicEnabled()
    {
        $isEnabled = false;
        $site = $this->getOwner()->getHeroicSite();
        if ($site) {
            $isEnabled = (bool) $site->getDoShowHero();
        }
        if (!$site || $isEnabled) {
            $isEnabled = (bool) $this->getOwner()->config()->get('enable_heroic');
        }
        $currController = Controller::curr();
        if (is_a($currController, Security::class)) {
            $isEnabled = false;
        }
        $this->getOwner()->invokeWithExtensions('updateIsHeroicEnabled', $isEnabled);
        return $isEnabled;
    }

    public function getHeroHeight()
    {
        $height = null;
        if ($this->getOwner()->getHeroicModeOptions('height')) {
            $mode = $this->getOwner()->HeroicHeightMode;
            if (!$mode) {
                $mode = $this->getOwner()->getHeroicModeDefault('height');
            }

            if ($mode === Heroic::MODE_PARENT) {
                $parent = $this->getOwner()->getHeroicParent();
                if ($parent) {
                    $height = $parent->getHeroHeight();
                }
            }
            else if ($mode === Heroic::MODE_SITE) {
                $site = $this->getOwner()->getHeroicSite();
                if ($site) {
                    $height = $site->getHeroHeight();
                }
            }
            else {
                $height = $mode;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroHeight', $height);
        return $height;
    }

    public function getHeroAlignVertical()
    {
        $alignVertical = null;
        if ($this->getOwner()->getHeroicModeOptions('align_vertical')) {
            $mode = $this->getOwner()->HeroicAlignVertical;
            if (!$mode) {
                $mode = $this->getOwner()->getHeroicModeDefault('align_vertical');
            }

            if ($mode === Heroic::MODE_PARENT) {
                $parent = $this->getOwner()->getHeroicParent();
                if ($parent) {
                    $alignVertical = $parent->getHeroAlignVertical();
                }
            }
            else if ($mode === Heroic::MODE_SITE) {
                $site = $this->getOwner()->getHeroicSite();
                if ($site) {
                    $alignVertical = $site->getHeroAlignVertical();
                }
            }
            else {
                $alignVertical = $mode;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroAlignVertical', $alignVertical);
        return $alignVertical;
    }

    public function getHeroAlignHorizontal()
    {
        $alignHorizontal = null;
        if ($this->getOwner()->getHeroicModeOptions('align_horizontal')) {
            $mode = $this->getOwner()->HeroicAlignHorizontal;
            if (!$mode) {
                $mode = $this->getOwner()->getHeroicModeDefault('align_horizontal');
            }

            if ($mode === Heroic::MODE_PARENT) {
                $parent = $this->getOwner()->getHeroicParent();
                if ($parent) {
                    $alignHorizontal = $parent->getHeroAlignHorizontal();
                }
            }
            else if ($mode === Heroic::MODE_SITE) {
                $site = $this->getOwner()->getHeroicSite();
                if ($site) {
                    $alignHorizontal = $site->getHeroAlignHorizontal();
                }
            }
            else {
                $alignHorizontal = $mode;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroAlignHorizontal', $alignHorizontal);
        return $alignHorizontal;
    }

    public function getHeroTextColor()
    {
        $color = null;
        if ($this->getOwner()->getHeroicModeOptions('text_color')) {
            $mode = $this->getOwner()->HeroicTextColorMode;
            if (!$mode) {
                $mode = $this->getOwner()->getHeroicModeDefault('text_color');
            }

            if ($mode === Heroic::MODE_PARENT) {
                $parent = $this->getOwner()->getHeroicParent();
                if ($parent) {
                    $color = $parent->getHeroTextColor();
                }
            }
            else if ($mode === Heroic::MODE_SITE) {
                $site = $this->getOwner()->getHeroicSite();
                if ($site) {
                    $color = $site->getHeroTextColor();
                }
            }
            else if ($mode === Heroic::MODE_PALETTE) {
                $color = $this->getOwner()->HeroicTextColorKey;
            }
            else if ($mode === Heroic::MODE_CUSTOM) {
                $color = $this->getOwner()->HeroicTextColorCustom;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroTextColorMode', $color);
        return $color;
    }

    public function getHeroTextColorKey()
    {
        $color = $this->getOwner()->getHeroTextColor();
        if ($color && strpos($color, '#') !== 0) {
            return $color;
        }
        return null;
    }

    public function getHeroTextColorHex()
    {
        $color = $this->getOwner()->getHeroTextColor();
        if ($color && strpos($color, '#') === 0) {
            return $color;
        }
        return null;
    }

    public function getHeroCTAColor()
    {
        $color = null;
        if ($this->getOwner()->getHeroicModeOptions('cta_color')) {
            $mode = $this->getOwner()->HeroicCTAColorMode;
            if (!$mode) {
                $mode = $this->getOwner()->getHeroicModeDefault('cta_color');
            }

            if ($mode === Heroic::MODE_PARENT) {
                $parent = $this->getOwner()->getHeroicParent();
                if ($parent) {
                    $color = $parent->getHeroCTAColor();
                }
            }
            else if ($mode === Heroic::MODE_SITE) {
                $site = $this->getOwner()->getHeroicSite();
                if ($site) {
                    $color = $site->getHeroCTAColor();
                }
            }
            else if ($mode === Heroic::MODE_PALETTE) {
                $color = $this->getOwner()->HeroicCTAColorKey;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroCTAColorMode', $color);
        return $color;
    }

    public function getHeroCTAColorKey()
    {
        $color = $this->getOwner()->getHeroCTAColor();
        if ($color && strpos($color, '#') !== 0) {
            return $color;
        }
        return null;
    }

    public function getHeroicBackgroundSource()
    {
        $source = null;

        $options = $this->getOwner()->getHeroicModeOptions('background');
        if ($options && count($options) > 0) {

            $mode = $this->getOwner()->HeroicBackgroundMode;
            if (!$mode) {
                $mode = $this->getOwner()->getHeroicModeDefault('background');
            }

            if ($mode === Heroic::MODE_PARENT) {
                $parent = $this->getOwner()->getHeroicParent();
                if ($parent) {
                    $source = $parent->getHeroicBackgroundSource();
                }
            }
            else if ($mode === Heroic::MODE_SITE) {
                $site = $this->getOwner()->getHeroicSite();
                if ($site) {
                    $source = $site->getHeroicBackgroundSource();
                }
            }
            else {
                $source = $this->getOwner();
            }
        }

        $this->getOwner()->invokeWithExtensions('updateHeroicBackgroundSource', $source);
        return $source;
    }

    public function getHeroBackgroundMode()
    {
        $mode = null;
        $source = $this->getOwner()->getHeroicBackgroundSource();
        if ($source) {
            $mode = $source->HeroicBackgroundMode;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundMode', $mode);
        return $mode;
    }

    public function getIsHeroBackgroundModeImage()
    {
        $mode = $this->getOwner()->getHeroBackgroundMode();
        $isImage = ($mode === Heroic::MODE_IMAGE);
        $this->getOwner()->invokeWithExtensions('updateIsHeroBackgroundImage', $isImage);
        return $isImage;
    }

    public function getIsHeroBackgroundModeVideo()
    {
        $mode = $this->getOwner()->getHeroBackgroundMode();
        $isVideo = ($mode === Heroic::MODE_VIDEO);
        $this->getOwner()->invokeWithExtensions('updateIsHeroBackgroundVideo', $isVideo);
        return $isVideo;
    }

    public function getIsHeroBackgroundModeColor()
    {
        $mode = $this->getOwner()->getHeroBackgroundMode();
        $isColor = ($mode === Heroic::MODE_COLOR);
        $this->getOwner()->invokeWithExtensions('updateIsHeroBackgroundColor', $isColor);
        return $isColor;
    }

    public function getIsHeroBackgroundVideoAutoplay()
    {
        $isAutoplay = null;
        if ($this->getOwner()->getIsHeroBackgroundModeVideo()) {

            $source = $this->getOwner()->getHeroicBackgroundSource();
            if ($source) {
                $isAutoplay = (bool) $source->IsHeroicBackgroundVideoAutoplay;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateIsHeroicBackgroundVideoAutoplay', $isAutoplay);
        return (bool) $isAutoplay;
    }

    public function getHeroBackgroundImage()
    {
        $image = null;
        if (
            $this->getOwner()->getIsHeroBackgroundModeImage()
            || $this->getOwner()->getIsHeroBackgroundVideoAutoplay()
        ) {
            $source = $this->getOwner()->getHeroicBackgroundSource();
            if ($source) {

                if (
                    $source->UsePageFeatureImageForHeroicBackgroundImage
                    && $source->getIsHeroicFeatureImageEnabled()
                ) {
                    $page = $this->getOwner()->getHeroicPage();
                    if ($page && $page->exists() && $page->hasMethod('getInheritedFeatureImage')) {
                        $image = $page->getInheritedFeatureImage();
                    }
                }
                else {
                    $image = $source->HeroicBackgroundImage();
                }
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundImage', $image);
        if (!$image || !$image->exists()) {
            $image = null;
        }
        return $image;
    }

    public function getHeroBackgroundVideo()
    {
        $video = null;
        if ($this->getOwner()->getIsHeroBackgroundModeVideo()) {
            $source = $this->getOwner()->getHeroicBackgroundSource();
            if ($source) {
                $video = $source->HeroicBackgroundVideo();
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundVideo', $video);
        if (!$video || !$video->exists()) {
            $video = null;
        }
        return $video;
    }

    public function getHeroBackgroundColor()
    {
        $color = null;
        if ($this->getOwner()->getIsHeroBackgroundModeColor()) {
            $source = $this->getOwner()->getHeroicBackgroundSource();
            if ($source) {

                $mode = $source->HeroicBackgroundColorMode;
                if (!$mode) {
                    $mode = $source->getHeroicModeDefault('background_color');
                }

                if ($mode === Heroic::MODE_PARENT) {
                    $parent = $source->getHeroicParent();
                    if ($parent) {
                        $color = $parent->getHeroicBackgroundColor();
                    }
                }
                else if ($mode === Heroic::MODE_SITE) {
                    $site = $source->getHeroicSite();
                    if ($site) {
                        $color = $site->getHeroBackgroundColor();
                    }
                }
                else if ($mode === Heroic::MODE_PALETTE) {
                    $color = $source->HeroicBackgroundColorKey;
                }
                else if ($mode === Heroic::MODE_CUSTOM) {
                    $color = $source->HeroicBackgroundColorCustom;
                }
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundColor', $color);
        if (!$color) {
            $color = null;
        }
        return $color;
    }

    public function getHeroBackgroundColorKey()
    {
        $color = $this->getOwner()->getHeroBackgroundColor();
        if ($color && strpos($color, '#') !== 0) {
            return $color;
        }
        return null;
    }

    public function getHeroBackgroundColorHex()
    {
        $color = $this->getOwner()->getHeroBackgroundColor();
        if ($color && strpos($color, '#') === 0) {
            return $color;
        }
        return null;
    }

    public function getHasHeroBackgroundOverlay($cssCheck = true)
    {
        $hasOverlay = false;
        if (
            $this->getOwner()->getIsHeroBackgroundModeImage()
            || $this->getOwner()->getIsHeroBackgroundModeVideo()
        ) {
            $source = $this->getOwner()->getHeroicBackgroundSource();
            if ($source) {

                if ($cssCheck) {
                    if ($this->getOwner()->getHeroBackgroundOverlayColorHex()) {
                        if ($this->getOwner()->getHeroBackgroundOverlayOpacityCSS() > 0) {
                            $hasOverlay = true;
                        }
                    }
                }
                else {
                    if ($this->getOwner()->getHeroBackgroundOverlayColor()) {
                        if ($this->getOwner()->getHeroBackgroundOverlayOpacity() > 0) {
                            $hasOverlay = true;
                        }
                    }
                }
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHasHeroBackgroundOverlay', $hasOverlay);
        return (bool) $hasOverlay;
    }

    public function getHeroBackgroundOverlayColor()
    {
        $color = null;
        $source = $this->getOwner()->getHeroicBackgroundSource();
        if ($source) {
            $color = $source->HeroicBackgroundOverlayColor;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundOverlayColor', $color);
        return $color;
    }

    public function getHeroBackgroundOverlayColorHex()
    {
        $hex = null;
        $color = $this->getOwner()->getHeroBackgroundOverlayColor();
        if (strpos($color, '#') === 0) {
            $hex = $color;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundOverlayColorHex', $hex);
        return $hex;
    }

    public function getHeroBackgroundOverlayOpacity()
    {
        $opacity = null;
        $source = $this->getOwner()->getHeroicBackgroundSource();
        if ($source) {
            $opacity = $source->HeroicBackgroundOverlayOpacity;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundOverlayOpacity', $opacity);
        return (int) $opacity;
    }

    public function getHeroBackgroundOverlayOpacityCSS()
    {
        $cssOpacity = 0;
        $opacity = $this->getOwner()->getHeroBackgroundOverlayOpacity();
        if ($opacity > 99) {
            $cssOpacity = 1;
        }
        else if ($opacity > 0) {
            $cssOpacity = $opacity / 100;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroBackgroundOverlayOpacityCSS', $cssOpacity);
        return $cssOpacity;
    }

    public function getHeroicPalette($key, $forDropdown = true)
    {
        $palette = null;

        $useGlobalPalette = true;
        $localPaletteConfig = $this->getOwner()->config()->get('heroic_palettes');
        if ($localPaletteConfig && is_array($localPaletteConfig) && isset($localPaletteConfig[$key])) {
            $useGlobalPalette = false;
            $localPalette = $localPaletteConfig[$key];
            if ($localPalette && is_array($localPalette) && count($localPalette) > 0) {

                foreach ($localPalette as $name => $data) {
                    if (!is_array($data) || !isset($data['title']) || !isset($data['hex'])) {
                        unset($localPalette[$name]);
                    }
                }
                $palette = $localPalette;
                if ($forDropdown) {
                    $dropdownPalette = [];
                    foreach ($palette as $name => $data) {
                        if (isset($data['hex'])) {
                            $dropdownPalette[$name] = '#' . $data['hex'];
                        }
                    }
                    $palette = $dropdownPalette;
                }
            }
        }

        if ($useGlobalPalette) {
            if ($forDropdown) {
                $palette = Heroic::get_palette_for_dropdown($key);
            }
            else {
                $palette = Heroic::get_palette($key);
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroicPalette', $key, $forDropdown, $palette);
        if (!is_array($palette) || count($palette) < 1) {
            $palette = null;
        }
        return $palette;
    }

    public function getHeroicModeOptions($key)
    {
        $options = null;

        $useGlobalOptions = true;
        $localOptionsConfig = $this->getOwner()->config()->get('heroic_mode_options');
        if ($localOptionsConfig && is_array($localOptionsConfig) && isset($localOptionsConfig[$key])) {
            $useGlobalOptions = false;
            $localOptions = $localOptionsConfig[$key];
            if ($localOptions && is_array($localOptions) && count($localOptions) > 0) {
                $options = $localOptions;
            }
        }

        if ($useGlobalOptions) {
            $options = Heroic::get_mode_options($key);
        }

        if (is_array($options)) {
            foreach ($options as $optKey => $option) {
                if ($option === false) {
                    unset($options[$optKey]);
                }
            }
            if (isset($options[Heroic::MODE_PARENT]) && !$this->getOwner()->getHeroicParent()) {
                unset($options[Heroic::MODE_PARENT]);
            }
            if (isset($options[Heroic::MODE_SITE]) && !$this->getOwner()->getHeroicSite()) {
                unset($options[Heroic::MODE_SITE]);
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroicModeOptions', $key, $options);
        if (!is_array($options) || count($options) < 1) {
            $options = null;
        }
        return $options;
    }

    public function getHeroicModeDefault($key)
    {
        $default = null;
        $options = $this->getOwner()->getHeroicModeOptions($key);
        if ($options) {
            if (isset($options[Heroic::MODE_PARENT])) {
                $default = Heroic::MODE_PARENT;
            }
            else if (isset($options[Heroic::MODE_SITE])) {
                $default = Heroic::MODE_SITE;
            }
            else {
                reset($options);
                $default = key($options);
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroicModeDefault', $key, $default);
        return $default;
    }

    public function getHeroicModesConfig($key)
    {
        $useGlobalConfig = true;
        $config = $this->getOwner()->config()->get('heroic_mode_options');
        if ($config && is_array($config) && isset($config[$key])) {
            $useGlobalConfig = false;
            $config = $config = $config[$key];
        }
        if ($useGlobalConfig) {

        }
        $this->getOwner()->invokeWithExtensions($config);
        return $config;
    }

    public function getHeroicOpacityOptions(int $multiples = null)
    {
        if (!$multiples || $multiples < 1) {
            $multiples = 5;
        }
        $options = [];
        $val = 0;
        while ($val <= 100) {
            if ($val === 0) {
                $label = '0% (no overlay)';
            }
            else if ($val === 100) {
                $label = '100% (no transparency)';
            }
            else {
                $label = $val . '%';
            }
            $options[$val] = $label;
            $val = $val + $multiples;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroicOpacityOptions', $multiples, $options);
        return $options;
    }

    public function getHeroicParent()
    {
        return null;
    }

    public function getHeroicSite()
    {
        return null;
    }

    public function getIsHeroicSlidesEnabled()
    {
        $isEnabled = Heroic::get_is_slides_enabled();
        $this->getOwner()->invokeWithExtensions('updateIsHeroicSlidesEnabled', $isEnabled);
        return $isEnabled;
    }

    public function getIsHeroicFeatureImageEnabled()
    {
        $isEnabled = Heroic::get_is_featureimage_enabled();
        $this->getOwner()->invokeWithExtensions('updateIsHeroicFeatureImageEnabled', $isEnabled);
        return $isEnabled;
    }

    public function getIsHeroicMultisitesEnabled()
    {
        $isEnabled = Heroic::get_is_multisites_enabled();
        $this->getOwner()->invokeWithExtensions('updateIsHeroicMultisitesEnabled', $isEnabled);
        return $isEnabled;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'UsePageTitleForHeroicHeadline',
            'HeroicHeightMode',
            'HeroicAlignVertical',
            'HeroicAlignHorizontal',
            'HeroicTextColorMode',
            'HeroicTextColorKey',
            'HeroicTextColorCustom',
            'HeroicCTAColorMode',
            'HeroicCTAColorKey',
            'HeroicBackgroundMode',
            'HeroicBackgroundColorMode',
            'HeroicBackgroundColorKey',
            'HeroicBackgroundColorCustom',
            'HeroicBackgroundImage',
            'HeroicBackgroundVideoID',
            'HeroicBackgroundOverlayOpacity',
            'HeroicBackgroundOverlayColor',
            'UsePageFeatureImageForHeroicBackgroundImage'
        ]);

        if (!$this->getOwner()->getIsHeroicEnabled()) {
            return;
        }

        $tabSetPath = $this->getOwner()->config()->get('heroic_tabset_path');
        if (!$tabSetPath) {
            return;
        }

        $contentTabPath = $tabSetPath . '.HeroicContentTab';
        $contentTab = $fields->findOrMakeTab($contentTabPath);
        $contentTab->setTitle('Content');

        $heroicTabSet = $fields->fieldByName($tabSetPath);
        $heroicTabSet->setTitle('Hero');

        $isHeroicSlide = is_a($this->getOwner(), HeroicSlide::class);

        if (!$isHeroicSlide) {
            $doShowHeroicFieldGroup = FieldGroup::create(
                CheckboxField::create(
                    'DoShowHeroic',
                    $this->getOwner()->fieldLabel('DoShowHeroic')
                )
            );
            $doShowHeroicFieldGroup->setName('DoShowHeroicGroup');
            $doShowHeroicFieldGroup->setTitle('Hero');
            $contentTab->push($doShowHeroicFieldGroup);
        }

        $contentTabFieldsWrapper = Wrapper::create();
        $contentTabFieldsWrapper->setName('HeroicContentTabWrapper');


        $pageTitleAsHeadlineFieldGroup = FieldGroup::create(
            CheckboxField::create(
                'UsePageTitleForHeroicHeadline',
                $this->getOwner()->fieldLabel('UsePageTitleForHeroicHeadline')
            )
        );
        $pageTitleAsHeadlineFieldGroup->setName('UsePageTitleForHeroicHeadlineFieldGroup');
        $pageTitleAsHeadlineFieldGroup->setTitle('Headline');
        $contentTabFieldsWrapper->push($pageTitleAsHeadlineFieldGroup);

        $heightOptions = $this->getOwner()->getHeroicModeOptions('height');
        if ($heightOptions) {
            $heightField = DropdownField::create(
                'HeroicHeightMode',
                $this->getOwner()->fieldLabel('HeroicHeightMode'),
                $heightOptions
            );
            $heightField->setHasEmptyDefault(false);
            $contentTabFieldsWrapper->push($heightField);
        }

        $alignVerticalOptions = $this->getOwner()->getHeroicModeOptions('align_vertical');
        if ($alignVerticalOptions) {
            $alignVerticalField = DropdownField::create(
                'HeroicAlignVertical',
                $this->getOwner()->fieldLabel('HeroicAlignVertical'),
                $alignVerticalOptions
            );
            $alignVerticalField->setHasEmptyDefault(false);
        }

        $alignHorizOptions = $this->getOwner()->getHeroicModeOptions('align_horizontal');
        if ($alignHorizOptions) {
            $alignHorizField = DropdownField::create(
                'HeroicAlignHorizontal',
                $this->getOwner()->fieldLabel('HeroicAlignHorizontal'),
                $alignHorizOptions
            );
            $alignHorizField->setHasEmptyDefault(false);
        }

        if ($alignVerticalOptions && $alignHorizOptions) {

            $alignVerticalField->setTitle('Vertical');
            $alignHorizField->setTitle('Horizontal');

            $alignFieldGroup = FieldGroup::create(
                $alignVerticalField,
                $alignHorizField
            );

            $alignFieldGroup->setName('ContentAlignGroup');
            $alignFieldGroup->setTitle('Content Alignment');

            $contentTabFieldsWrapper->push($alignFieldGroup);
        }
        else if ($alignVerticalOptions) {
            $contentTabFieldsWrapper->push($alignVerticalField);
        }
        else if ($alignHorizOptions) {
            $contentTabFieldsWrapper->push($alignHorizField);
        }

        $textColorModeOptions = $this->getOwner()->getHeroicModeOptions('text_color');
        if ($textColorModeOptions) {

            if (!$this->getOwner()->HeroicTextColorMode) {
                $this->getOwner()->HeroicTextColorMode = $this->getOwner()->getHeroicModeDefault('text_color');
            }
            $textColorModeField = DropdownField::create(
                'HeroicTextColorMode',
                $this->getOwner()->fieldLabel('HeroicTextColorMode'),
                $textColorModeOptions
            );
            $textColorModeField->setHasEmptyDefault(false);
            $contentTabFieldsWrapper->push($textColorModeField);

            if (isset($textColorModeOptions[Heroic::MODE_PALETTE])) {

                $textColorPaletteOptions = $this->getOwner()->getHeroicPalette('text_color');
                if ($textColorPaletteOptions) {
                    if (!$this->getOwner()->HeroicTextColorKey) {
                        reset($textColorPaletteOptions);
                        $this->getOwner()->HeroicTextColorKey = key($textColorPaletteOptions);
                    }
                    $textColorKeyField = ColorPaletteField::create(
                        'HeroicTextColorKey',
                        $this->getOwner()->fieldLabel('HeroicTextColorKey'),
                        $textColorPaletteOptions
                    );

                    $textColorKeyWrapper = Wrapper::create($textColorKeyField);
                    $textColorKeyWrapper->setName('HeroicTextColorKeyWrapper');
                    $contentTabFieldsWrapper->push($textColorKeyWrapper);
                    $textColorKeyWrapper
                        ->displayIf('HeroicTextColorMode')
                        ->isEqualTo(Heroic::MODE_PALETTE);
                }
            }

            if (isset($textColorModeOptions[Heroic::MODE_CUSTOM])) {

                $textColorField = ColorField::create(
                    'HeroicTextColorCustom',
                    $this->getOwner()->fieldLabel('HeroicTextColorCustom')
                );

                $textColorWrapper = Wrapper::create($textColorField);
                $textColorWrapper->setName('HeroicTextColorCustomWrapper');
                $contentTabFieldsWrapper->push($textColorWrapper);
                $textColorWrapper
                    ->displayIf('HeroicTextColorMode')
                    ->isEqualTo(Heroic::MODE_CUSTOM);
            }
        }

        $ctaColorModeOptions = $this->getOwner()->getHeroicModeOptions('cta_color');
        if ($ctaColorModeOptions) {

            if (!$this->getOwner()->HeroicCTAColorMode) {
                $this->getOwner()->HeroicCTAColorMode = $this->getOwner()->getHeroicModeDefault('cta_color');
            }
            $ctaColorModeField = DropdownField::create(
                'HeroicCTAColorMode',
                $this->getOwner()->fieldLabel('HeroicCTAColorMode'),
                $ctaColorModeOptions
            );
            $ctaColorModeField->setHasEmptyDefault(false);
            $contentTabFieldsWrapper->push($ctaColorModeField);

            if (isset($ctaColorModeOptions[Heroic::MODE_PALETTE])) {

                $ctaColorPaletteOptions = $this->getOwner()->getHeroicPalette('cta_color');
                if ($ctaColorPaletteOptions) {
                    if (!$this->getOwner()->HeroicCTAColorKey) {
                        reset($ctaColorPaletteOptions);
                        $this->getOwner()->HeroicCTAColorKey = key($ctaColorPaletteOptions);
                    }
                    $ctaColorKeyField = ColorPaletteField::create(
                        'HeroicCTAColorKey',
                        $this->getOwner()->fieldLabel('HeroicCTAColorKey'),
                        $ctaColorPaletteOptions
                    );

                    $ctaColorKeyWrapper = Wrapper::create($ctaColorKeyField);
                    $contentTabFieldsWrapper->push($ctaColorKeyWrapper);
                    $ctaColorKeyWrapper
                        ->displayIf('HeroicCTAColorMode')
                        ->isEqualTo(Heroic::MODE_PALETTE);
                }
            }
        }

        $contentTab->push($contentTabFieldsWrapper);

        if (!$isHeroicSlide) {
            $contentTabFieldsWrapper->displayIf('DoShowHeroic')->isChecked();
        }

        $backgroundModeOptions = $this->getOwner()->getHeroicModeOptions('background');
        if ($backgroundModeOptions) {

            $backgroundTab = Tab::create('HeroicBackgroundTab', 'Background');

            $backgroundTabFieldsWrapper = Wrapper::create();
            $backgroundTabFieldsWrapper->setName('HeroicBackgroundTabWrapper');

            if (!$this->getOwner()->HeroicBackgroundMode) {
                $this->getOwner()->HeroicBackgroundMode = $this->getOwner()->getHeroicModeDefault('background');
            }
            $backgroundModeField = OptionsetField::create(
                'HeroicBackgroundMode',
                $this->getOwner()->fieldLabel('HeroicBackgroundMode'),
                $backgroundModeOptions
            );
            $backgroundTabFieldsWrapper->push($backgroundModeField);

            if (isset($backgroundModeOptions[Heroic::MODE_COLOR])) {

                $bgColorModeOptions = $this->getOwner()->getHeroicModeOptions('background_color');
                if ($bgColorModeOptions) {

                    $bgColorFieldsWrapper = Wrapper::create();

                    if (!$this->getOwner()->HeroicBackgroundColorMode) {
                        $this->getOwner()->HeroicBackgroundColorMode = $this->getOwner()->getHeroicModeDefault('background_color');
                    }
                    $bgColorModeField = DropdownField::create(
                        'HeroicBackgroundColorMode',
                        $this->getOwner()->fieldLabel('HeroicBackgroundColorMode'),
                        $bgColorModeOptions
                    );
                    $bgColorModeField->setHasEmptyDefault(false);
                    $bgColorFieldsWrapper->push($bgColorModeField);

                    if (isset($bgColorModeOptions[Heroic::MODE_PALETTE])) {

                        $bgColorPaletteOptions = $this->getOwner()->getHeroicPalette('background_color');
                        if ($bgColorPaletteOptions) {
                            if (!$this->getOwner()->HeroicBackgroundColorKey) {
                                reset($bgColorPaletteOptions);
                                $this->getOwner()->HeroicBackgroundColorKey = key($bgColorPaletteOptions);
                            }
                            $bgColorKeyField = ColorPaletteField::create(
                                'HeroicBackgroundColorKey',
                                $this->getOwner()->fieldLabel('HeroicBackgroundColorKey'),
                                $bgColorPaletteOptions
                            );

                            $bgColorKeyWrapper = Wrapper::create($bgColorKeyField);
                            $bgColorKeyWrapper->setName('HeroicBackgroundColorKeyWrapper');
                            $bgColorFieldsWrapper->push($bgColorKeyWrapper);
                            $bgColorKeyWrapper
                                ->displayIf('HeroicBackgroundColorMode')
                                ->isEqualTo(Heroic::MODE_PALETTE);
                        }
                    }

                    if (isset($bgColorModeOptions[Heroic::MODE_CUSTOM])) {

                        $bgColorField = ColorField::create(
                            'HeroicBackgroundColorCustom',
                            $this->getOwner()->fieldLabel('HeroicBackgroundColorCustom')
                        );

                        $bgColorWrapper = Wrapper::create($bgColorField);
                        $bgColorWrapper->setName('HeroicBackgroundColorCustomoWrapper');
                        $bgColorFieldsWrapper->push($bgColorWrapper);
                        $bgColorWrapper
                            ->displayIf('HeroicBackgroundColorMode')
                            ->isEqualTo(Heroic::MODE_CUSTOM);
                    }

                    $backgroundTabFieldsWrapper->push($bgColorFieldsWrapper);
                    $bgColorFieldsWrapper
                        ->displayIf('HeroicBackgroundMode')
                        ->isEqualTo(Heroic::MODE_COLOR);
                }

                if (isset($backgroundModeOptions[Heroic::MODE_VIDEO])) {

                    $bgVideoFieldsWrapper = Wrapper::create(
                        HasOneMiniGridField::create(
                            'HeroicBackgroundVideo',
                            $this->getOwner()->fieldLabel('HeroicBackgroundVideo'),
                            $this->getOwner()
                        ),
                        $autoplayFieldGroup = FieldGroup::create(
                            CheckboxField::create(
                                'IsHeroicBackgroundVideoAutoplay',
                                $this->getOwner()->fieldLabel('IsHeroicBackgroundVideoAutoplay')
                            )
                        )
                    );

                    $autoplayFieldGroup->setName('AutoplayGroup');
                    $autoplayFieldGroup->setTitle('Autoplay');

                    $bgVideoFallbackImageHeaderField = HeaderField::create(
                        'HeroicVideoImageHeader',
                        'Select an image as a fallback on mobile',
                        3
                    );
                    $bgVideoFallbackImageHeaderField
                        ->displayIf('IsHeroicBackgroundVideoAutoplay')
                        ->isChecked();
                    $bgVideoFieldsWrapper->push($bgVideoFallbackImageHeaderField);

                    $backgroundTabFieldsWrapper->push($bgVideoFieldsWrapper);
                    $bgVideoFieldsWrapper
                        ->displayIf('HeroicBackgroundMode')
                        ->isEqualTo(Heroic::MODE_VIDEO);
                }

                if (isset($backgroundModeOptions[Heroic::MODE_VIDEO]) || isset($backgroundModeOptions[Heroic::MODE_IMAGE])) {

                    $bgImageFieldsWrapper = Wrapper::create();
                    $bgImageFieldsWrapper->setName('HeroicBackgroundImageFieldsWrapper');

                    if ($this->getOwner()->getIsHeroicFeatureImageEnabled()) {
                        $useFeatureImageFieldGroup = FieldGroup::create(
                            CheckboxField::create(
                                'UsePageFeatureImageForHeroicBackgroundImage',
                                $this->getOwner()->fieldLabel('UsePageFeatureImageForHeroicBackgroundImage')
                            )
                        );
                        $useFeatureImageFieldGroup->setName('UsePageFeatureImageForHeroicBackgroundImageGroup');
                        $useFeatureImageFieldGroup->setTitle('Feature Image');
                        $bgImageFieldsWrapper->push($useFeatureImageFieldGroup);
                    }

                    $bgImageFieldWrapper = Wrapper::create(
                        UploadField::create(
                            'HeroicBackgroundImage',
                            $this->getOwner()->fieldLabel('HeroicBackgroundImage')
                        )
                    );
                    if ($this->getOwner()->getIsHeroicFeatureImageEnabled()) {
                        $bgImageFieldWrapper
                            ->displayIf('UsePageFeatureImageForHeroicBackgroundImage')
                            ->isNotChecked();
                    }
                    $bgImageFieldsWrapper->push($bgImageFieldWrapper);
                    $bgImageFieldsWrapper
                        ->displayUnless('HeroicBackgroundMode')->isEqualTo(Heroic::MODE_VIDEO)
                        ->andIf('IsHeroicBackgroundVideoAutoplay')->isNotChecked();

                    $bgImageFieldsExtraWrapper = Wrapper::create($bgImageFieldsWrapper);

                    $backgroundTabFieldsWrapper->push($bgImageFieldsExtraWrapper);
                    $bgImageFieldsExtraWrapper
                        ->displayIf('HeroicBackgroundMode')->isEqualTo(Heroic::MODE_IMAGE)
                        ->orIf('HeroicBackgroundMode')->isEqualTo(Heroic::MODE_VIDEO);

                    $opacitySource = $this->getOwner()->getHeroicOpacityOptions();
                    if ($opacitySource) {

                        $bgOverlayFieldsWrapper = Wrapper::create();
                        $bgOverlayFieldsWrapper->push(
                            HeaderField::create(
                                'HeroicBackgroundOverlayHeader',
                                'Set a semi-transparent overlay to ensure text is readable',
                                3
                            )
                        );

                        $opacityField = DropdownField::create(
                            'HeroicBackgroundOverlayOpacity',
                            $this->getOwner()->fieldLabel('HeroicBackgroundOverlayOpacity'),
                            $opacitySource
                        );

                        if (!$this->getOwner()->HeroicBackgroundOverlayColor) {
                            $this->getOwner()->HeroicBackgroundOverlayColor = '#000000';
                        }
                        $overlayColorField = ColorField::create(
                            'HeroicBackgroundOverlayColor',
                            $this->getOwner()->fieldLabel('HeroicBackgroundOverlayColor')
                        );

                        $overlayFieldGroup = FieldGroup::create($opacityField, $overlayColorField);
                        $overlayFieldGroup->setName('HeroicBackgroundOverlayGroup');
                        $overlayFieldGroup->setTitle('Overlay');

                        $bgOverlayFieldsWrapper->push($overlayFieldGroup);
                        $bgOverlayFieldsWrapper->setName('HeroicBackgroundOverlayGroupWrapper');
                        $backgroundTabFieldsWrapper->push($bgOverlayFieldsWrapper);
                        $bgOverlayFieldsWrapper
                            ->displayIf('HeroicBackgroundMode')->isEqualTo(Heroic::MODE_IMAGE)
                            ->orIf('HeroicBackgroundMode')->isEqualTo(Heroic::MODE_VIDEO);
                    }
                }
            }

            $backgroundTab->push($backgroundTabFieldsWrapper);
            if (!$isHeroicSlide) {
                $backgroundTabFieldsWrapper->displayIf('DoShowHeroic')->isChecked();
            }
            $heroicTabSet->push($backgroundTab);
        }
    }
}
