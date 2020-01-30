<?php

use SilverStripe\ORM\DataExtension;

class PageImageExtension extends DataExtension
{
    private static $has_one = [
        'Page' => 'Page'
    ];
}
