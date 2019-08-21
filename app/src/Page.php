<?php

namespace {

    use DNADesign\Elemental\Extensions\ElementalPageExtension;
    use SilverStripe\CMS\Model\SiteTree;

    class Page extends SiteTree
    {
        private static $db = [];

        private static $extensions = [ElementalPageExtension::class];

        private static $has_one = [];
    }
}
