<?php

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

class DummyFileTask extends BuildTask
{
    const DEFAULT_NUM_FILES = 1000;

    private static $segment = 'DummyFileTask';

    protected $title = 'Bulk create files';

    protected $description = "
        Creates files to be migrated using the MigrateFileTask.
        Use the numfiles parameter to override the number of files created. 
        Defaults to " . self::DEFAULT_NUM_FILES . " files.
    ";

    /**
     * Removes existing files and creates new ones ready to be migrated.
     *
     * @param \SilverStripe\Control\HTTPRequest $request
     */
    public function run($request)
    {
        static::clearFolder(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets');

        $num = array_key_exists('numfiles', $request->getVars()) ?
            intval($request->getVar('numfiles')) :
            self::DEFAULT_NUM_FILES;
        $values = [];
        $parameters = [];
        for ($i = 1; $i <= $num; $i++) {
            copy(BASE_PATH . DIRECTORY_SEPARATOR . "original.jpg", PUBLIC_PATH . DIRECTORY_SEPARATOR . "assets/hello{$i}.jpg");

            $values []= <<<SQL
(NULL,'SilverStripe\\Assets\\Image','2019-01-17 14:24:25','2019-01-17 14:24:25',
CONCAT('hello',?,'.jpg'),CONCAT('hello',?,'.jpg'),CONCAT('assets/hello',?,'.jpg'),
NULL,1,0,0,0,'Inherit','Inherit',NULL,NULL,NULL,0,0,0)
SQL;
            $parameters []= $i;
            $parameters []= $i;
            $parameters []= $i;
        }

        DB::Query("DELETE IGNORE FROM `File`");
        if (!empty($values)) {
            DB::prepared_query('INSERT INTO `File` VALUES ' . implode(',', $values), $parameters);
        }
    }

    private static function clearFolder($folder)
    {
        $files = glob($folder . "/*");
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
