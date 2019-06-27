<?php

namespace Fromholdio\Heroic\Extensions;

use RyanPotter\SilverStripeColorField\Forms\ColorField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataExtension;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class HeroSiteConfigExtension extends DataExtension
{
    const MODE_SELF = 'self';
    const MODE_FEATURE = 'feature';
    const MODE_IMAGE = 'image';
    const MODE_COLOR = 'color';

    private static $hero_tab_path = 'Root.Hero';

    private static $db = [
        'IsHeroEnabled' => 'Boolean',
        'HeroMode' => 'Varchar',
        'HeroImageMode' => 'Varchar',
        'HeroColorMode' => 'Varchar',
        'HeroColor' => 'Varchar(7)',
        'HeroHeightMode' => 'Varchar',
        'HeroHorizontalAlignMode' => 'Varchar',
        'HeroVerticalAlignMode' => 'Varchar'
    ];

    private static $has_one = [
        'HeroImage' => Image::class
    ];

    private static $owns = [
        'HeroImage'
    ];

    private static $hero_mode_labels = [
        self::MODE_IMAGE => 'Background Image',
        self::MODE_COLOR => 'Background Colour'
    ];

    private static $hero_image_mode_labels = [
        self::MODE_FEATURE => 'Use feature image',
        self::MODE_SELF => 'Upload/Select custom image'
    ];

    private static $hero_color_mode_labels = [
        self::MODE_SELF => 'Select custom colour'
    ];

    private static $hero_height_mode_labels = [
        'sm' => 'Small',
        'md' => 'Medium',
        'lg' => 'Large',
        'xl' => 'X-Large'
    ];

    private static $hero_halign_mode_labels = [
        'left' => 'Left',
        'center' => 'Centre',
        'right' => 'Right'
    ];

    private static $hero_valign_mode_labels = [
        'top' => 'Top',
        'middle' => 'Middle',
        'bottom' => 'Bottom'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $tabPath = $this->getOwner()->getHeroTabPath();
        if (!$tabPath) {
            return;
        }

        $isEnabledField = FieldGroup::create(
            'Hero',
            CheckboxField::create(
                'IsHeroEnabled',
                $this->getOwner()->fieldLabel('IsHeroEnabled')
            )
        );

        $heroFieldsWrapper = Wrapper::create();

        $fields->addFieldsToTab($tabPath, [$isEnabledField, $heroFieldsWrapper]);

        $heightModeOptions = $this->getOwner()->getHeroHeightModeOptions();
        $hasHeightModeOptions = count($heightModeOptions) > 1;

        if ($hasHeightModeOptions) {

            $heightModeField = DropdownField::create(
                'HeroHeightMode',
                $this->getOwner()->fieldLabel('HeroHeightMode'),
                $heightModeOptions,
                $this->getOwner()->getDefaultHeroHeightMode()
            );
            $heightModeField->setHasEmptyDefault(false);
            $heroFieldsWrapper->push($heightModeField);
        }

        $modeOptions = $this->getOwner()->getHeroModeOptions();
        $hasModeOptions = count($modeOptions) > 1;

        if ($hasModeOptions) {

            if (!$this->getOwner()->HeroMode) {
                $this->getOwner()->HeroMode = $this->getOwner()->getDefaultHeroMode();
            }

            $modeField = DropdownField::create(
                'HeroMode',
                $this->getOwner()->fieldLabel('HeroMode'),
                $modeOptions
            );
            $modeField->setHasEmptyDefault(false);
            $heroFieldsWrapper->push($modeField);

            if (array_key_exists(self::MODE_IMAGE, $modeOptions)) {

                $imageModeOptions = $this->getOwner()->getHeroImageModeOptions();
                $hasImageModeOptions = count($imageModeOptions) > 1;
                $imageFieldsWrapper = Wrapper::create();

                if ($hasImageModeOptions) {

                    if (!$this->getOwner()->HeroImageMode) {
                        $this->getOwner()->HeroImageMode = $this->getOwner()->getDefaultHeroImageMode();
                    }

                    $imageModeField = OptionsetField::create(
                        'HeroImageMode',
                        $this->getOwner()->fieldLabel('HeroImageMode'),
                        $imageModeOptions
                    );
                    $imageModeField->setHasEmptyDefault(false);
                    $imageFieldsWrapper->push($imageModeField);
                }

                if (array_key_exists(self::MODE_SELF, $imageModeOptions)) {

                    $imageField = UploadField::create(
                        'HeroImage',
                        $this->getOwner()->fieldLabel('HeroImage')
                    );

                    $imageFieldWrapper = Wrapper::create($imageField);
                    $imageFieldsWrapper->push($imageFieldWrapper);

                    if ($hasImageModeOptions) {
                        $imageFieldWrapper
                            ->displayIf('HeroImageMode')
                            ->isEqualTo(self::MODE_SELF);
                    }
                    else {
                        $imageFieldWrapper
                            ->displayIf('HeroMode')
                            ->isEqualTo(self::MODE_IMAGE);
                    }
                }

                if ($imageFieldsWrapper->getChildren()->count()) {
                    $heroFieldsWrapper->push($imageFieldsWrapper);
                    $imageFieldsWrapper->displayIf('HeroMode')->isEqualTo(self::MODE_IMAGE);
                }
            }

            if (array_key_exists(self::MODE_COLOR, $modeOptions)) {

                $colorModeOptions = $this->getOwner()->getHeroColorModeOptions();
                $hasColorModeOptions = count($colorModeOptions) > 1;
                $colorFieldsWrapper = Wrapper::create();

                if ($hasColorModeOptions) {

                    if (!$this->getOwner()->HeroColorMode) {
                        $this->getOwner()->HeroColorMode = $this->getOwner()->getDefaultHeroColorMode();
                    }

                    $colorModeField = OptionsetField::create(
                        'HeroColorMode',
                        $this->getOwner()->fieldLabel('HeroColorMode'),
                        $colorModeOptions
                    );
                    $colorModeField->setHasEmptyDefault(false);
                    $colorFieldsWrapper->push($colorModeField);
                }

                if (array_key_exists(self::MODE_SELF, $colorModeOptions)) {

                    $colorField = ColorField::create(
                        'HeroColor',
                        $this->getOwner()->fieldLabel('HeroColor')
                    );

                    $colorFieldWrapper = Wrapper::create($colorField);
                    $colorFieldsWrapper->push($colorFieldWrapper);

                    if ($hasColorModeOptions) {
                        $colorFieldWrapper
                            ->displayIf('HeroColorMode')
                            ->isEqualTo(self::MODE_SELF);
                    }
                    else {
                        $colorFieldWrapper
                            ->displayIf('HeroMode')
                            ->isEqualTo(self::MODE_COLOR);
                    }
                }

                if ($colorFieldsWrapper->getChildren()->count()) {
                    $heroFieldsWrapper->push($colorFieldsWrapper);
                    $colorFieldsWrapper->displayIf('HeroMode')->isEqualTo(self::MODE_COLOR);
                }
            }
        }

        $alignFieldGroup = FieldGroup::create(
            'Content Alignment'
        );

        $vAlignModeOptions = $this->getOwner()->getHeroVerticalAlignModeOptions();
        $hasVAlignModeOptions = count($vAlignModeOptions) > 1;

        if ($hasVAlignModeOptions) {

            if (!$this->getOwner()->HeroVerticalAlignMode) {
                $this->getOwner()->HeroVerticalAlignMode = $this->getOwner()->getDefaultHeroVerticalAlignMode();
            }

            $vAlignModeField = DropdownField::create(
                'HeroVerticalAlignMode',
                $this->getOwner()->fieldLabel('HeroVerticalAlignMode'),
                $vAlignModeOptions
            );
            $vAlignModeField->setHasEmptyDefault(false);
            $alignFieldGroup->push($vAlignModeField);
        }

        $hAlignModeOptions = $this->getOwner()->getHeroHorizontalAlignModeOptions();
        $hasHAlignModeOptions = count($hAlignModeOptions) > 1;

        if ($hasHAlignModeOptions) {

            if (!$this->getOwner()->HeroHorizontalAlignMode) {
                $this->getOwner()->HeroHorizontalAlignMode = $this->getOwner()->getDefaultHeroHorizontalAlignMode();
            }

            $hAlignModeField = DropdownField::create(
                'HeroHorizontalAlignMode',
                $this->getOwner()->fieldLabel('HeroHorizontalAlignMode'),
                $hAlignModeOptions
            );
            $hAlignModeField->setHasEmptyDefault(false);
            $alignFieldGroup->push($hAlignModeField);
        }

        if ($alignFieldGroup->getChildren()->count() > 0) {
            $alignFieldsWrapper = Wrapper::create($alignFieldGroup);
            $heroFieldsWrapper->push($alignFieldsWrapper);
        }

        $heroFieldsWrapper->displayIf('IsHeroEnabled')->isChecked();
    }

    public function getHeroTabPath()
    {
        $path = $this->getOwner()->config()->get('hero_tab_path');
        if ($this->getOwner()->hasMethod('updateHeroTabPath')) {
            $path = $this->getOwner()->updateHeroTabPath($path);
        }
        return $path;
    }

    public function getHeroFeatureImage()
    {
        $image = null;
        $featureImageModuleExists = ModuleLoader::inst()
            ->getManifest()
            ->moduleExists('fromholdio/silverstripe-featureimage');
        if ($featureImageModuleExists) {
            $image = $this->getOwner()->FeatureImage();
        }
        if ($this->getOwner()->hasMethod('updateHeroFeatureImage')) {
            $image = $this->getOwner()->updateHeroFeatureImage($image);
        }
        return $image;
    }

    public function getDefaultHeroMode()
    {
        $mode = null;
        $options = $this->getOwner()->getHeroModeOptions();
        if (!is_array($options)) {
            $options = [$options];
        }
        if (count($options) === 1) {
            reset($options);
            $mode = key($options);
        }
        else if (count($options) > 1) {
            if ($this->getOwner()->getIsHeroFeatureImageEnabled() && isset($options[self::MODE_IMAGE])) {
                $mode = self::MODE_IMAGE;
            }
            else if (isset($options[self::MODE_COLOR])) {
                $mode = self::MODE_COLOR;
            }
            else {
                reset($options);
                $mode = key($options);
            }
        }
        if (!$mode) {
            $mode = self::MODE_COLOR;
        }
        if ($this->getOwner()->hasMethod('updateDefaultHeroMode')) {
            $mode = $this->getOwner()->updateDefaultHeroMode($mode);
        }
        return $mode;
    }

    public function getHeroModeOptions()
    {
        $options = $this->getOwner()->config()->get('hero_mode_labels');
        foreach ($options as $key => $value) {
            if ($value === false) {
                unset($options[$key]);
            }
        }
        if ($this->getOwner()->hasMethod('updateHeroModeOptions')) {
            $options = $this->getOwner()->updateHeroModeOptions($options);
        }
        return $options;
    }

    public function getInheritedHeroImageMode()
    {
        $mode = $this->getOwner()->HeroImageMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultHeroImageMode();
        }
        if ($this->getOwner()->hasMethod('updateInheritedHeroImageMode')) {
            $mode = $this->getOwner()->updateInheritedHeroImageMode($mode);
        }
        return $mode;
    }

    public function getDefaultHeroImageMode()
    {
        $mode = null;
        $options = $this->getOwner()->getHeroImageModeOptions();
        if (!is_array($options)) {
            $options = [$options];
        }
        if (count($options) === 1) {
            reset($options);
            $mode = key($options);
        }
        else if (count($options) > 1) {
            if ($this->getOwner()->getIsHeroFeatureImageEnabled() && isset($options[self::MODE_FEATURE])) {
                $mode = self::MODE_FEATURE;
            }
            else {
                reset($options);
                $mode = key($options);
            }
        }
        if (!$mode) {
            $mode = self::MODE_SELF;
        }
        if ($this->getOwner()->hasMethod('updateDefaultHeroImageMode')) {
            $mode = $this->getOwner()->updateDefaultHeroImageMode($mode);
        }
        return $mode;
    }

    public function getHeroImageModeOptions()
    {
        $options = $this->getOwner()->config()->get('hero_image_mode_labels');
        if (!$this->getOwner()->getIsHeroFeatureImageEnabled()) {
            if (isset($options[self::MODE_FEATURE])) {
                unset($options[self::MODE_FEATURE]);
            }
        }
        foreach ($options as $key => $value) {
            if ($value === false) {
                unset($options[$key]);
            }
        }
        if ($this->getOwner()->hasMethod('updateHeroImageModeOptions')) {
            $options = $this->getOwner()->updateHeroImageModeOptions($options);
        }
        return $options;
    }

    public function getInheritedHeroColorMode()
    {
        $mode = $this->getOwner()->HeroColorMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultHeroColorMode();
        }
        if ($this->getOwner()->hasMethod('updateInheritedHeroColorMode')) {
            $mode = $this->getOwner()->updateInheritedHeroColorMode($mode);
        }
        return $mode;
    }

    public function getDefaultHeroColorMode()
    {
        $mode = null;
        $options = $this->getOwner()->getHeroColorModeOptions();
        if (!is_array($options)) {
            $options = [$options];
        }
        if (count($options) > 0) {
            reset($options);
            $mode = key($options);
        }
        if (!$mode) {
            $mode = self::MODE_SELF;
        }
        if ($this->getOwner()->hasMethod('updateDefaultHeroColorMode')) {
            $mode = $this->getOwner()->updateDefaultHeroColorMode($mode);
        }
        return $mode;
    }

    public function getHeroColorModeOptions()
    {
        $options = $this->getOwner()->config()->get('hero_color_mode_labels');
        foreach ($options as $key => $value) {
            if ($value === false) {
                unset($options[$key]);
            }
        }
        if ($this->getOwner()->hasMethod('updateHeroImageModeOptions')) {
            $options = $this->getOwner()->updateHeroImageModeOptions($options);
        }
        return $options;
    }

    public function getInheritedHeroHeightMode()
    {
        $mode = $this->getOwner()->HeroHeightMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultHeroHeightMode();
        }
        if ($this->getOwner()->hasMethod('updateInheritedHeroHeightMode')) {
            $mode = $this->getOwner()->updateInheritedHeroHeightMode($mode);
        }
        return $mode;
    }

    public function getDefaultHeroHeightMode()
    {
        $mode = null;
        $options = $this->getOwner()->getHeroHeightModeOptions();
        if (!is_array($options)) {
            $options = [$options];
        }
        if (count($options) > 0) {
            reset($options);
            $mode = key($options);
        }
        if (!$mode) {
            $mode = self::MODE_SELF;
        }
        if ($this->getOwner()->hasMethod('updateDefaultHeroHeightMode')) {
            $mode = $this->getOwner()->updateDefaultHeroHeightMode($mode);
        }
        return $mode;
    }

    public function getHeroHeightModeOptions()
    {
        $options = $this->getOwner()->config()->get('hero_height_mode_labels');
        foreach ($options as $key => $value) {
            if ($value === false) {
                unset($options[$key]);
            }
        }
        if ($this->getOwner()->hasMethod('updateHeroHeightModeOptions')) {
            $options = $this->getOwner()->updateHeroHeightModeOptions($options);
        }
        return $options;
    }

    public function getInheritedHeroHorizontalAlignMode()
    {
        $mode = $this->getOwner()->HeroHorizontalAlignMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultHeroHorizontalAlignMode();
        }
        if ($this->getOwner()->hasMethod('updateInheritedHeroHorizontalAlignMode')) {
            $mode = $this->getOwner()->updateInheritedHeroHorizontalAlignMode($mode);
        }
        return $mode;
    }

    public function getDefaultHeroHorizontalAlignMode()
    {
        $mode = null;
        $options = $this->getOwner()->getHeroHorizontalAlignModeOptions();
        if (!is_array($options)) {
            $options = [$options];
        }
        if (count($options) > 0) {
            reset($options);
            $mode = key($options);
        }
        if (!$mode) {
            $mode = self::MODE_SELF;
        }
        if ($this->getOwner()->hasMethod('updateDefaultHeroHorizontalAlignMode')) {
            $mode = $this->getOwner()->updateDefaultHeroHorizontalAlignMode($mode);
        }
        return $mode;
    }

    public function getHeroHorizontalAlignModeOptions()
    {
        $options = $this->getOwner()->config()->get('hero_halign_mode_labels');
        foreach ($options as $key => $value) {
            if ($value === false) {
                unset($options[$key]);
            }
        }
        if ($this->getOwner()->hasMethod('updateHeroHorizontalAlignModeOptions')) {
            $options = $this->getOwner()->updateHeroHorizontalAlignModeOptions($options);
        }
        return $options;
    }

    public function getInheritedHeroVerticalAlignMode()
    {
        $mode = $this->getOwner()->HeroVerticalAlignMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultHeroVerticalAlignMode();
        }
        if ($this->getOwner()->hasMethod('updateInheritedHeroVerticalAlignMode')) {
            $mode = $this->getOwner()->updateInheritedHeroVerticalAlignMode($mode);
        }
        return $mode;
    }

    public function getDefaultHeroVerticalAlignMode()
    {
        $mode = null;
        $options = $this->getOwner()->getHeroVerticalAlignModeOptions();
        if (!is_array($options)) {
            $options = [$options];
        }
        if (count($options) > 0) {
            reset($options);
            $mode = key($options);
        }
        if (!$mode) {
            $mode = self::MODE_SELF;
        }
        if ($this->getOwner()->hasMethod('updateDefaultHeroVerticalAlignMode')) {
            $mode = $this->getOwner()->updateDefaultHeroVerticalAlignMode($mode);
        }
        return $mode;
    }

    public function getHeroVerticalAlignModeOptions()
    {
        $options = $this->getOwner()->config()->get('hero_valign_mode_labels');
        foreach ($options as $key => $value) {
            if ($value === false) {
                unset($options[$key]);
            }
        }
        if ($this->getOwner()->hasMethod('updateHeroVerticalAlignModeOptions')) {
            $options = $this->getOwner()->updateHeroVerticalAlignModeOptions($options);
        }
        return $options;
    }

    public function getIsHeroMultisitesEnabled()
    {
        $enabled = ModuleLoader::inst()
            ->getManifest()
            ->moduleExists('symbiote/silverstripe-multisites');
        if ($this->getOwner()->hasMethod('updateIsHeroMultisitesEnabled')) {
            $enabled = $this->getOwner()->updateIsHeroMultisitesEnabled();
        }
        return $enabled;
    }

    public function getIsHeroFeatureImageEnabled()
    {
        $enabled = ModuleLoader::inst()
            ->getManifest()
            ->moduleExists('fromholdio/silverstripe-featureimage');
        if ($this->getOwner()->hasMethod('updateIsHeroFeatureImageEnabled')) {
            $enabled = $this->getOwner()->updateIsHeroFeatureImageEnabled($enabled);
        }
        return $enabled;
    }
}