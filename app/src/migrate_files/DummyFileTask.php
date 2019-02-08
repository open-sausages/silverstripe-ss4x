<?php

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

class DummyFileTask extends BuildTask
{
    const DEFAULT_NUM_FILES = 100;

    private static $segment = 'DummyFileTask';

    protected $title = 'Dummy File Task';

    protected $description = "
        Creates files to be migrated using the MigrateFileTask.
        Use the numfiles parameter to override the number of files created. 
        Defaults to " . self::DEFAULT_NUM_FILES . " files.
    ";

    const FILES = [
        'original.jpg',
        'picsum.jpg',
        'picsum-1.jpg',
        'picsum-2.jpg',
    ];

    private static function getNewFileName($original, $i)
    {
        return $i . '-' . $original;
    }

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
        for ($i = 1; $i <= $num; $i++) {
            $original = self::FILES[array_rand(self::FILES)];
            $file = self::getNewFileName($original, $i);

            copy(BASE_PATH . DIRECTORY_SEPARATOR . $original, PUBLIC_PATH . DIRECTORY_SEPARATOR . "assets/".$file);

            $values []= <<<SQL
(NULL,'SilverStripe\\Assets\\Image','2019-01-17 14:24:25','2019-01-17 14:24:25',
'{$file}','{$file}','assets/{$file}',
NULL,1,0,0,0,'Inherit','Inherit',NULL,NULL,NULL,0,0,0)
SQL;
        }

        DB::query("DROP TABLE IF EXISTS `File`");
        DB::query(<<<SQL
CREATE TABLE `File` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ClassName` enum('SilverStripe\\Assets\\File','SilverStripe\\Assets\\Folder','SilverStripe\\Assets\\Image') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'SilverStripe\\Assets\\File',
  `LastEdited` datetime DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Filename` mediumtext,
  `Content` mediumtext,
  `ShowInSearch` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ParentID` int(11) NOT NULL DEFAULT '0',
  `OwnerID` int(11) NOT NULL DEFAULT '0',
  `Version` int(11) NOT NULL DEFAULT '0',
  `CanViewType` enum('Anyone','LoggedInUsers','OnlyTheseUsers','Inherit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Inherit',
  `CanEditType` enum('LoggedInUsers','OnlyTheseUsers','Inherit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Inherit',
  `FileHash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FileFilename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FileVariant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `BasicFieldsTestPageID` int(11) NOT NULL DEFAULT '0',
  `TestPageID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `ParentID` (`ParentID`),
  KEY `OwnerID` (`OwnerID`),
  KEY `Name` (`Name`(191)),
  KEY `ClassName` (`ClassName`),
  KEY `CompanyID` (`CompanyID`),
  KEY `BasicFieldsTestPageID` (`BasicFieldsTestPageID`),
  KEY `TestPageID` (`TestPageID`)
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=utf8;
SQL
        );
        if (!empty($values)) {
            DB::query('INSERT INTO `File` VALUES ' . implode(',', $values));
        }
    }

    private static function clearFolder($folder)
    {
        $files = glob($folder . "/*");
        foreach ($files as $file) {
            if (!is_dir($file)) {
                unlink($file);
            }
        }
    }
}
