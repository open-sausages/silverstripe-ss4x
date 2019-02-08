<?php

use SilverStripe\Assets\File;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\Tasks\MigrateFileTask;

class MigrateFileTaskMonitor extends BuildTask
{
    private static $segment = 'MigrateFileTaskMonitor';

    protected $title = 'Migrate File Task Monitor';

    protected $description = "
        Executes migrate file task with arguments and emails the result.
    ";

    /**
     * Removes existing files and creates new ones ready to be migrated.
     *
     * @param \SilverStripe\Control\HTTPRequest $request
     */
    public function run($request)
    {
        set_time_limit(120);
        ini_set('memory_limit','256M');

        $start = microtime(true);

        $migrateFileTask = new MigrateFileTask();
        $migrateFileTask->run($request);

        $end = microtime(true);
        Debug::message(
            sprintf(
                "Execution time: %s, Peak memory usage: %s\n",
                $this->formatExecutionTime($start, $end),
                $this->formatPeakMemoryUsage()
            ),
            false
        );
    }

    /**
     * Convert the provided start and end time to a interval in secs.
     * @param float $start
     * @param float $end
     * @return string
     */
    private function formatExecutionTime($start, $end)
    {
        $diff = round($end - $start, 4);
        return $diff . ' seconds';
    }

    /**
     * Get the peak memory usage formatted has a string and a meaningful unit.
     * @return string
     */
    private function formatPeakMemoryUsage()
    {
        $bytes = memory_get_peak_usage(true);
        return File::format_size($bytes);
    }
}
