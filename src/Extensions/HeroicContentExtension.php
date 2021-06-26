<?php

namespace Fromholdio\Heroic\Extensions;

use Fromholdio\MiniGridField\Forms\HasOneMiniGridField;
use Fromholdio\SuperLinkerCTAs\Model\CTA;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class HeroicContentExtension extends HeroicExtension
{
    private static $db = [
        'HeroicHeadline' => 'Varchar'
    ];

    private static $has_one = [
        'HeroicCTA' => CTA::class
    ];

    private static $owns = [
        'HeroicCTA'
    ];

    private static $cascade_deletes = [
        'HeroicCTA'
    ];

    private static $cascade_duplicates = [
        'HeroicCTA'
    ];

    private static $field_labels = [
        'HeroicHeadline' => 'Custom Headline',
        'HeroicCTA' => 'Button'
    ];

    private static $heroic_tabset_path = 'Root.Hero';

    public function getHeroHeadline()
    {
        $headline = $this->getOwner()->HeroicHeadline;
        if ($this->getOwner()->UsePageTitleForHeroicHeadline) {
            $page = $this->getOwner()->getHeroicPage();
            if ($page && $page->exists()) {
                $headline = $page->getTitle();
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroHeadline', $headline);
        return $headline;
    }

    public function getHeroCTA()
    {
        $cta = null;
        if ($this->getOwner()->HeroicCTAID) {
            $cta = $this->getOwner()->HeroicCTA();
            if (!$cta || !$cta->exists() || !$cta->HasTarget()) {
                $cta = null;
            }
        }
        $this->getOwner()->invokeWithExtensions('updateHeroCTA', $cta);
        return $cta;
    }

    public function getHeroicPage()
    {
        return null;
    }

    public function updateSiteCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'HeroicHeadline',
            'HeroicCTAID'
        ]);

        parent::updateCMSFields($fields);

        if (!$this->getOwner()->getIsHeroicEnabled()) {
            return;
        }

        $tabSetPath = $this->getOwner()->config()->get('heroic_tabset_path');
        if (!$tabSetPath) {
            return;
        }

        $contentTabPath = $tabSetPath . '.HeroicContentTab';
        $contentTab = $fields->findOrMakeTab($contentTabPath);

        $headlineField = TextField::create(
            'HeroicHeadline',
            $this->getOwner()->fieldLabel('HeroicHeadline')
        );
        $headlineField
            ->displayIf('UsePageTitleForHeroicHeadline')
            ->isNotChecked();
        $contentTab->insertAfter(
            'UsePageTitleForHeroicHeadlineFieldGroup',
            $headlineField
        );

        $ctaField = HasOneMiniGridField::create(
            'HeroicCTA',
            $this->getOwner()->fieldLabel('HeroicCTA'),
            $this->getOwner()
        );
        $contentTab->insertAfter('HeroicHeadline', $ctaField);
    }
}