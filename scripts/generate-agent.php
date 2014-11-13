#!/usr/bin/env php
<?php
define('APP_PATH', dirname(__FILE__));

/**
 * generate-agent.php - Generates a skeleton for a scraping agent.
 *
 * Examle usage: php generate-agent -s mol.fi
 * Result: a new scraping agent with filename AgentMolFi.php is placed
 * in the agents folder.
 */
require_once APP_PATH . '/common/set_error_handler.php';
require_once APP_PATH . '/vendor/autoload.php';
require_once APP_PATH . '/lib/ScraperLib.php';

$cmd = new Commando\Command();

$cmd->option('s')
        ->aka('site')
        ->require()
        ->describedAs('Site name');

$siteName = $cmd['s'];
$className = ScraperLib::siteNameToClassName($siteName);

$path = "agents/{$className}.php";

if (file_exists($path)) {
    die("ERROR: can't generate agent. File already exists: {$path}\n");
}

// Replace the template placeholders with real values.

$templateContents = file_get_contents('lib/AgentTemplate.php');
$classContents = str_replace('{site-name}', $siteName, $templateContents);
$classContents = str_replace('{class-name}', $className, $classContents);

file_put_contents($path, $classContents);

echo "Generated new agent at {$path}\n";
