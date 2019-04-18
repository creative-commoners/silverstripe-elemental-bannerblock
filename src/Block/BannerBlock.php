<?php

namespace SilverStripe\ElementalBannerBlock\Block;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ElementalFileBlock\Block\FileBlock;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\ArrayData;

class BannerBlock extends FileBlock
{
    private static $icon = 'font-icon-block-banner';

    private static $db = [
        'Content' => 'HTMLText',
        'CallToActionLink' => 'Link',
    ];

    private static $singular_name = 'banner';

    private static $plural_name = 'banners';

    private static $table_name = 'S_EB_BannerBlock';

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Banner');
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            // Remove default scaffolded relationship fields
            $fields->removeByName('CallToActionLinkID');

            // Move the file upload field to be before the content
            $upload = $fields->fieldByName('Root.Main.File');
            $fields->insertBefore('Content', $upload);

            // Set the height of the content fields
            $fields->fieldByName('Root.Main.Content')->setRows(5);

            $fields->fieldByName('Root.Main.CallToActionLink')
                ->setTitle(_t(__CLASS__ . '.CallToActionTitle', 'Call to action link'));
        });

        return parent::getCMSFields();
    }

    /**
     * For the frontend, return a parsed set of data for use in templates
     *
     * @return ArrayData|null
     */
    public function CallToActionLink()
    {
        return $this->decodeLinkData($this->getField('CallToActionLink'));
    }

    /**
     * Add the banner content instead of the image title
     *
     * {@inheritDoc}
     */
    public function getSummary()
    {
        if ($this->File() && $this->File()->exists()) {
            return $this->getSummaryThumbnail() . $this->dbObject('Content')->Summary(20);
        }
        return '';
    }

    /**
     * Return content summary for summary section of ElementEditor
     *
     * @return array
     */
    protected function provideBlockSchema()
    {
        $blockSchema = parent::provideBlockSchema();
        $blockSchema['content'] = $this->dbObject('Content')->Summary(20);
        return $blockSchema;
    }

    /**
     * Given a set of JSON data, decode it, attach the relevant Page object and return as ArrayData
     *
     * @param string $linkJson
     * @return ArrayData|null
     */
    protected function decodeLinkData($linkJson)
    {
        if (!$linkJson || $linkJson === 'null') {
            return;
        }

        $data = ArrayData::create(json_decode($linkJson));

        // Link page, if selected
        if ($data->PageID) {
            $data->setField('Page', self::get_by_id(SiteTree::class, $data->PageID));
        }

        return $data;
    }
}
