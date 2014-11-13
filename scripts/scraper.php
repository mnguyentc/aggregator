#!/usr/bin/env php
<?php
define('APP_PATH', dirname(__FILE__));

/**
 * scraper.php - scrapes html files for job data using site specific
 * scraping agents.
 *
 * Example usage: php scrape.php -i index.html -s mol.fi
 */
require_once APP_PATH . '/common/set_error_handler.php';
require_once APP_PATH . '/vendor/autoload.php';
require_once APP_PATH . '/lib/Job.php';
require_once APP_PATH . '/lib/Agent.php';
require_once APP_PATH . '/lib/simple_html_dom.php';
require_once APP_PATH . '/lib/ScraperLib.php';

// Load configuration.

$configPath = APP_PATH . '/config/scraper';

Logger::configure("{$configPath}/logging.php");
$logger = Logger::getLogger('scraper');
$logger->info('Launching scraper');
$startTs = time();

$encodings = require "{$configPath}/encodings.php";

// Handle command line arguments.

$cmd = new Commando\Command();

$cmd->option('i')
        ->aka('input')
        ->require()
        ->describedAs('Input folder');

$cmd->option('o')
        ->aka('output')
        ->require()
        ->describedAs('Output folder');

$cmd->option('s')
        ->aka('site')
        ->describedAs('Site name');

$cmd->option('subdirs-are-sites')
        ->boolean()
        ->describedAs('Each sub directory in the input path represent a site.');

$input = trim($cmd['i']);
$output = trim($cmd['o']);
$siteName = trim($cmd['s']);
$subdirsAreSites = $cmd['subdirs-are-sites'];

// Validate the options.

if (strlen($siteName) > 0 && $subdirsAreSites) {
	$logger->warn("WARNING: no need to specify site name when using option subdirs-are-sites. Site names will be derived from directory names.");
	die();
}

if (strlen($siteName) === 0 && $subdirsAreSites === false) {
    $logger->warn("WARNING: no site name specified.");
    die();
}

if ( ! file_exists($input)) {
    $logger->warn("WARNING: input path '{$input}' doesn't exist.");
    die();
}

if (is_file($input)) {
    $logger->warn("WARNING: {$input} is file. Expected folder.");
    die();
}

// Create output directory if it doesn't exist.

if ( ! file_exists($output)) {
    mkdir($output, 0755, true);
}

// Determine which directories to scrape.

$sites = array();

if ($subdirsAreSites) {
    $paths = glob("{$input}/*", GLOB_ONLYDIR);

    foreach ($paths as $path) {
        $dir = basename($path);
        $sites[$dir] = $path;
    }
}
else {
    $sites[$siteName] = $input;
}

// Scrape the directories.

foreach ($sites as $site => $inputPath) {

    $className = ScraperLib::siteNameToClassName($site);
    $agentPath = APP_PATH . "/agents/{$className}.php";

    if ( ! file_exists($agentPath)) {
    	$logger->warn("WARNING: failed to load agent for site '{$site}'. '{$agentPath}' doesn't exist.");
    	die();
    }

    // Load the scraping agent.

    $logger->info("Scraping {$site}");

    $encoding = false;
    if (isset($encodings[$site])) {
        $encoding = $encodings[$site];
    }

    require_once $agentPath;
    $agent = new $className();
    ScraperLib::scrapeDirectory($inputPath, $output, $agent, $encoding);
}

$logger->info('Completed in ' . (time() - $startTs) . ' seconds.');
