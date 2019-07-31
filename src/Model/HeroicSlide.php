<?php

namespace Fromholdio\Heroic\Model;

use Fromholdio\Heroic\Extensions\HeroicContentExtension;
use Fromholdio\Sortable\Extensions\Sortable;
use SilverStripe\ORM\DataObject;
use \Page;
use SilverStripe\Versioned\Versioned;

class HeroicSlide extends DataObject
{
    private static $table_name = 'HeroicSlide';
    private static $singular_name = 'Slide';
    private static $plural_name = 'Slides';

    private static $extensions = [
        HeroicContentExtension::class,
        Sortable::class,
        Versioned::class
    ];

    private static $has_one = [
        'Page' => Page::class
    ];

    private static $defaults = [
        'IsHeroicEnabled' => true,
        'UsePageTitleForHeroicHeadline' => false,
        'IsHeroicBackgroundVideoAutoplay' => false,
        'UsePageFeatureImageForHeroicBackgroundImage' => false
    ];

    private static $heroic_mode_options = [
        'height' => false
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName([
            'PageID',
            'Main'
        ]);
        return $fields;
    }

    public function getHeroicPage()
    {
        return $this->Page();
    }

    public function getHeroicParent()
    {
        return $this->Page();
    }

    public function getHeroicSite()
    {
        $parent = $this->getHeroicParent();
        if ($parent && $parent->exists()) {
            return $parent->getHeroicSite();
        }
        return null;
    }

    public function getSortableScope()
    {
        return self::get()
            ->filter('PageID', $this->PageID)
            ->exclude('ID', $this->ID);
    }
}