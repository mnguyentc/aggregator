#!/usr/bin/env php
<?php
define('APP_PATH', dirname(__FILE__));

require_once APP_PATH . '/vendor/autoload.php';

// Handle command line arguments.

$cmd = new Commando\Command();

$cmd->option('c')
        ->aka('crawler-path')
        ->describedAs('Path to crawler output');

$cmd->option('s')
        ->aka('scraper-path')
        ->describedAs('Path to scraper output');

$crawlerPath = trim($cmd['c']);
$scraperPath = trim($cmd['s']);

echo "The time of this report: ".date("l d-m-y H:i:s")."\n";

if (strlen($crawlerPath) === 0 > 0 && strlen($scraperPath) === 0) {
    die("WARNING: need path to either crawler or scraper output (or both).\n");
}

if (strlen($crawlerPath) > 0 && file_exists($crawlerPath) === false) {
    die("WARNING: couldn't find specified directory for crawler output ({$crawlerPath}).\n");
}

if (strlen($scraperPath) > 0 && file_exists($scraperPath) === false) {
    die("WARNING: couldn't find specified directory for scraper output ({$scraperPath}).\n");
}

// Scan directories and show results.

if (strlen($crawlerPath) > 0) {
    echo "Crawler: Total number of files\n";
    echo shell_exec("find {$crawlerPath} . -type f | wc -l") . "\n";

    echo "Crawler: Total number of files in each site\n";
    echo shell_exec('cd ' . $crawlerPath . '; for D in *; do echo $D; find $D -type f| wc -l; done;') . "\n";

    echo "Crawler: Total size of folder\n";
    echo shell_exec("du {$crawlerPath} -sh") . "\n";

    echo "Crawler: Total size of each folder\n";
    echo shell_exec("du {$crawlerPath}* -sh |sort -r") . "\n";
}

if (strlen($scraperPath) > 0) {
    echo "Scraper: Total number of jobs\n";
    echo shell_exec("find {$scraperPath} . -type f | wc -l") . "\n";

    echo "Scraper: Total size of jobs\n";
    echo shell_exec("du {$scraperPath} -sh") . "\n";
}
