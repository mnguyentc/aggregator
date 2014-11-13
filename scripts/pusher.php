#!/usr/bin/env php
<?php
define('APP_PATH', dirname(__FILE__));

/**
 * pusher.php - pushes job files to Active MQ.
 *
 * Example usage: php push.php -i job_folder
 */
require_once APP_PATH . '/common/set_error_handler.php';
require_once APP_PATH . '/vendor/autoload.php';
require_once APP_PATH . '/lib/ScraperLib.php';

// Load configuration.

$configPath = APP_PATH . '/config/pusher';
$config = include "{$configPath}/pusher.php";
Logger::configure("{$configPath}/logging.php");
$logger = Logger::getLogger('pusher');
$logger->info('Launching pusher');
$startTs = time();

// Handle command line arguments.

$cmd = new Commando\Command();

$cmd->option('i')
        ->aka('input')
        ->require()
        ->describedAs('Input file or folder');

$cmd->option('l')
        ->aka('limit')
        ->describedAs('Limit the number of pushed jobs to this');

$cmd->option('delete-after-push')
        ->boolean()
        ->describedAs('Files are deleted after being pushed');

$input = $cmd['i'];
$limit = (strlen(trim(($cmd['l']))) > 0 ? $cmd['l'] : false);
$deleteAfterPush = $cmd['delete-after-push'];

if ($limit !== false) {
    $limit = (int) $limit;

    if ($limit <= 0) {
        $logger->error("ERROR: invalid limit. Must be a positive integer.\n");
        die();
    }
}

// Push the jobs.

$numPushed = ScraperLib::pushJobs($input, $config['url'], $config['queue'], $limit, $deleteAfterPush);

$logger->info("Pushed {$numPushed} jobs to {$config['queue']} at {$config['url']}");
$logger->info('Completed in ' . (time() - $startTs) . ' seconds.');
