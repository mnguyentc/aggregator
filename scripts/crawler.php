#!/usr/bin/env php
<?php
define('APP_PATH', dirname(__FILE__));

/**
 * crawler.php - crawls job boards and saves web pages to disk.
 *
 * Example usage: php crawl.php -s mol.fi
 */
require_once APP_PATH . '/common/set_error_handler.php';
require_once APP_PATH . '/vendor/autoload.php';
require_once APP_PATH . '/lib/ScraperLib.php';

$configPath = APP_PATH . '/config/crawler';
$siteConfigPath = "{$configPath}/sites-enabled";
$pids = array();

try {

    // Validate configuration.

    try {
        ScraperLib::validateConfiguration('crawler');
    }
    catch (Exception $e) {
        echo 'WARNING: ' . $e->getMessage();
        die();
    }

    // Load configuration.

    $config = require "{$configPath}/crawler.php";

    Logger::configure("{$configPath}/logging.php");
    $logger = Logger::getLogger('crawler');
    $logger->info('Launching crawler');

    // Check operating system.

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $logger->warn("WARNING: crawler.php is not Windows compatible. Run on *nix.");
        die();
    }

    // Check dependencies.

    if (!ScraperLib::getInstallationPath('httrack')) {
        $logger->warn('WARNING: Failed to detect application "httrack". Install it and try again.');
        die();
    }

    // Process command line arguments.

    $cmd = new Commando\Command();

    $cmd->option()
            ->describedAs("One of the following actions:\n\trun: Launches httrack instance(s)\n\tlist: Lists running httrack instance(s)\n\tkill: Kills the specified httrack instance.")
            ->required()
            ->must(function($action) {
                $validActions = array('run', 'list', 'kill');
                return in_array($action, $validActions);
            });

    $cmd->option('site')
            ->aka('s')
            ->describedAs('Site name belonging to the httrack process to kill');

    $cmd->option('pid')
            ->aka('p')
            ->describedAs('PID of the httrack process to kill');

    $cmd->option('delete-all-sites')
            ->describedAs('Remove all output before crawling')
            ->boolean();

    $cmd->option('delete-other-sites')
            ->describedAs('Remove output for all sites except the ones being crawled')
            ->boolean();

    $cmd->option('o')
            ->aka('output')
            ->describedAs('Output folder');

    $cmd->option('y')
            ->aka('yes')
            ->describedAs('Assume Yes to all queries and do not prompt')
            ->boolean();

    // Output path is derived from one of the following (in order of priority):
    // 		1. Command line parameter.
    // 		2. Configuration file.
    // 		3. Current working directory.

    $outputPath = getcwd();
    if (strlen(trim($cmd['o'])) > 0) {
        $outputPath = trim($cmd['o']);
    }
    else if (isset($config['outputPath'])) {
        $outputPath = $config['outputPath'];
    }

    // Execute the specified action.

    $action = $cmd[0];

    if ($action === 'run') {

        $logger->info("Writing output to {$outputPath}");

        $siteName = false;
        if (strlen(trim($cmd['s'])) > 0) {
            $siteName = $cmd['s'];
        }

        // Delete existing output before crawling.

        if ($cmd['delete-all-sites']) {
            $logger->info('Deleting output for all sites');
            ScraperLib::deleteOutput($outputPath);
        }
        else if ($cmd['delete-other-sites']) {
            $logger->info('Deleting output for sites not included in this crawl');
            ScraperLib::deleteOutput($outputPath, array($siteName));
        }

        // Run httrack instances.
        // ----------------------
        // Load site configuration(s).

        $configs = ScraperLib::loadSiteConfig($siteConfigPath, $siteName);

        if (count($configs) == 0) {
            $logger->warn("WARNING: No site configurations loaded. There's nothing to do.");
        }
        else {
            $logger->info('Loaded ' . count($configs) . " site configurations. Launching httrack instances.");

            ScraperLib::deleteOrphanPidFiles();
            $storedPids = ScraperLib::getStoredPids();

            // Enumerate through the site configurations.

            foreach ($configs as $siteName => $config) {

                // Only launch httrack instances for sites that aren't already being crawled.

                if (in_array($siteName, array_keys($storedPids))) {
                    $logger->warn("WARNING: there's already a httrack instance running for site '{$siteName}' (PID: " . $storedPids[$siteName] . ")");
                }
                else {
                    // Launch httrack.

                    $cmd = "{$config['cmd']} -O {$outputPath}/{$siteName} -f2";

                    if (is_dir("{$outputPath}/{$siteName}")) {
                        $cmd .= " -iC2";
                    }

                    $fullCmd = "nohup {$cmd} > /dev/null 2>&1 & echo $!";

                    $pids[$siteName] = exec($fullCmd);
                    file_put_contents(APP_PATH . "/run/{$siteName}.pid", $pids[$siteName]);

                    $logger->info("\t{$siteName} - PID: {$pids[$siteName]}");
                }
            }
        }

        if (count($pids) === 0) {
            $logger->info("No httrack instances launched.");
        }
        else if (count($pids) === 1) {
            $logger->info("Launched one new instance of httrack.");
        }
        else {
            $logger->info('Launched ' . count($pids) . " new instances of httrack.");
        }
    }
    else if ($action === 'list') {

        // List httrack instances.
        // -----------------------
        // Delete orphan PID files.

        ScraperLib::deleteOrphanPidFiles();

        $pids = ScraperLib::getStoredPids();

        if (count($pids) === 0) {
            $logger->info("There are no running instances of httrack.");
        }
        else {
            foreach ($pids as $siteName => $pid) {
                $logger->info("{$siteName}:\t{$pid}");
            }
        }
    }
    else if ($action === 'kill') {

        // Kill httrack instance.
        // ----------------------

        $storedPids = ScraperLib::getStoredPids();
        $siteNames = array_flip($storedPids);
        $pid = false;
        $siteName = false;
        $killAll = false;
        $numKilled = 0;

        if (strlen(trim($cmd['p'])) > 0 && strlen(trim($cmd['s'])) > 0) {
            $logger->warn("WARNING: don't supply both PID (p) and site name (s) when killing a process. Choose one.");
        }
        else {
            if (strlen(trim($cmd['p'])) > 0) {
                $pid = (int) $cmd['p'];
                if (in_array($pid, $storedPids)) {
                    $siteName = $siteNames[$pid];
                }
                else {
                    $logger->warn("WARNING: PID {$pid} does not belong to any htttrack instance started by this application.");
                }
            }
            else if (strlen(trim($cmd['s'])) > 0) {
                $siteName = trim($cmd['s']);

                if (in_array($siteName, $siteNames)) {
                    $pid = $storedPids[$siteName];
                }
                else {
                    $logger->warn("WARNING: there is no httrack instance running for site {$siteName}.");
                }
            }
            else {
                $killAll = true;
            }

            if ($killAll) {
                if ($cmd['y']) {
                    $response = 'y';
                }
                else {
                    $response = strtolower(readline("Kill all instances of httrack that have been started by this application? (y/N) "));
                }

                if ($response === 'y' || $response === 'yes') {
                    foreach ($storedPids as $siteName => $pid) {
                        $logger->info("Killing {$siteName} httrack instance (PID: {$pid}).");
                        posix_kill($pid, 2);
                        $numKilled++;
                    }
                }
            }
            else if ($pid && $siteName) {
                $response = strtolower(readline("Kill httrack instance with PID {$pid} running for site {$siteName}? (y/N) "));

                if ($response === 'y' || $response === 'yes') {
                    posix_kill($pid, 2);
                    $numKilled++;
                }
            }
        }

        ScraperLib::deleteOrphanPidFiles();

        if ($numKilled === 0) {
            $logger->info("Killed no processes");
        }
        else if ($numKilled === 1) {
            $logger->info("Killed on process");
        }
        else {
            $logger->info("KIlled {$numKilled} processes");
        }
    }
}
catch (Exception $e) {
    $logger->error($e);
}
