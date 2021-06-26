<?php

namespace Fromholdio\Heroic\Extensions;

use Fromholdio\Heroic\Model\HeroicSlide;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\ArrayList;
use SilverStripe\SiteConfig\SiteConfig;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\Multisites\Model\Site;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class HeroicPageExtension extends HeroicContentExtension
{
    private static $has_many = [
        'HeroicSlides' => HeroicSlide::class
    ];

    private static $owns = [
        'HeroicSlides'
    ];

    private static $cascade_deletes = [
        'HeroicSlides'
    ];

    private static $cascade_duplicates = [
        'HeroicSlides'
    ];

    public function getHasMultipleHeroSlides()
    {
        $hasMultiple = false;
        if ($this->getOwner()->getIsHeroicSlidesEnabled()) {
            if ($this->getOwner()->HeroicSlides()->count() > 0) {
                $hasMultiple = true;
            }
        }
        return $hasMultiple;
    }

    public function getHeroSlides()
    {
        $firstSlide = $this->getOwner()->getFirstHeroSlide();

        $slides = ArrayList::create();
        $slides->push($firstSlide);

        if ($this->getOwner()->getHasMultipleHeroSlides()) {
            foreach ($this->getOwner()->HeroicSlides() as $slide) {
                $slides->push($slide);
            }
        }

        return $slides;
    }

    public function getFirstHeroSlide()
    {
        $firstSlide = HeroicSlide::create();
        $firstSlide->PageID = $this->getOwner()->ID;
        $firstSlide->UsePageTitleForHeroicHeadline = $this->getOwner()->UsePageTitleForHeroicHeadline;
        $firstSlide->HeroicHeadline = $this->getOwner()->HeroicHeadline;
        $firstSlide->HeroicCTAID = $this->getOwner()->HeroicCTAID;
        $this->getOwner()->invokeWithExtensions('updateFirstHeroSlide', $firstSlide);
        return $firstSlide;
    }

    public function getHeroicPage()
    {
        $page = $this->getOwner();
        $this->getOwner()->invokeWithExtensions('updateHeroicPage', $page);
        return $page;
    }

    public function getHeroicParent()
    {
        $parent = $this->getOwner()->Parent();
        if ($parent && $parent->exists()) {
            if ($this->getOwner()->getIsHeroicMultisitesEnabled()) {
                if ((int) $parent->ID === (int) $this->getOwner()->SiteID) {
                    $parent = null;
                }
            }
        }
        else {
            $parent = null;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroicParent', $parent);
        return $parent;
    }

    public function getHeroicSite()
    {
        $site = null;
        if ($this->getOwner()->getIsHeroicMultisitesEnabled()) {
            if (!is_a($this->getOwner(), Site::class)) {
                $site = $this->getOwner()->Site();
            }
        }
        else {
            $site = SiteConfig::current_site_config();
        }
        if (!is_null($site)) {
            if (
                !$site->hasExtension(HeroicConfigExtension::class)
                && !$site->hasExtension(HeroicPageExtension::class)
            )
                $site = null;
        }
        $this->getOwner()->invokeWithExtensions('updateHeroicSite', $site);
        return $site;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'HeroicSlides'
        ]);

        parent::updateCMSFields($fields);

        if (!$this->getOwner()->getIsHeroicEnabled()) {
            return;
        }

        $tabSetPath = $this->getOwner()->config()->get('heroic_tabset_path');
        if (!$tabSetPath) {
            return;
        }

        $heroicTabSet = $fields->findOrMakeTab($tabSetPath);

        if ($this->getOwner()->getIsHeroicSlidesEnabled()) {

            $slidesTab = Tab::create('HeroicSlidesTab', 'Carousel');

            $slidesField = GridField::create(
                'HeroicSlides',
                $this->getOwner()->fieldLabel('HeroicSlides'),
                $this->getOwner()->HeroicSlides(),
                $slidesConfig = GridFieldConfig_RecordEditor::create()
            );

            $slidesConfig
                ->removeComponentsByType([
                    GridFieldPageCount::class,
                    GridFieldPaginator::class,
                    GridFieldFilterHeader::class,
                    GridFieldAddExistingAutocompleter::class
                ])
                ->addComponents([
                    new GridFieldOrderableRows()
                ]);

            $slidesWrapper = Wrapper::create($slidesField);
            $slidesWrapper->displayIf('DoShowHeroic')->isChecked();

            $slidesTab->push($slidesWrapper);

            $heroicTabSet->push($slidesTab);
        }
    }
}
