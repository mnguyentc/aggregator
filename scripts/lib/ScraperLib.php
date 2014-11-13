<?php

require_once APP_PATH . '/lib/Stomp.php';

//Load city list into an array
$rows = array_map('str_getcsv', file(dirname(__FILE__) . "/../../doc/citylist.csv"));
$header = array_shift($rows);
$csv = array();
foreach ($rows as $row) {
  $csv[] = array_combine($header, $row);
}

$GLOBALS['citylist'] = $csv;

/**
 * ScraperLib - a utility class to shared scraper related methods among scripts.
 */

class ScraperLib {

    /**
     * Detects the encoding of the specified file. Assumes that application 'uchardet' is installed on the system.
     *
     * @param $filename Filename
     */
    
    //Search value in city list and return correspondent ID
    public static function searchForId($city, $country = '', $array) {
       foreach ($array as $key => $val) {
           if ($val['City'] === $city && $val['Country'] === $country) {
               return $val['ID'];
           }
       }
       return false;
    }

    public static function detectEncoding($filename) {
        $encoding = trim(shell_exec("uchardet {$filename}"));

        return $encoding;
    }

    /**
     * Returns installation path of specified application.
     *
     * @param $applicationName Application name
     *
     * @return string/bool
     */
    public static function getInstallationPath($applicationName) {
        $path = trim(shell_exec("which {$applicationName}"));

        if (strlen($path) > 0) {
            return $path;
        }

        return false;
    }

    /**
     * Validates that the required configuration exists.
     *
     * @param $scriptName The script the config belongs to.
     *
     * @return bool
     */
    public static function validateConfiguration($scriptName) {
        $validScriptNames = array('crawler', 'scraper', 'pusher');
        $expectedFiles = array(
            'crawler' => array(
                'crawler/crawler.php',
                'crawler/logging.php'
            ),
            'scraper' => array(
                'scraper/scraper.php',
                'scraper/logging.php'
            ),
            'pusher' => array(
                'pusher/pusher.php',
                'pusher/logging.php'
            )
        );

        if ( ! in_array($scriptName, $validScriptNames)) {
            throw new Exception("Invalid script name {$scriptName}. Expected on of following: " . implode(', ', $validScriptNames) . '.');
        }

        foreach ($expectedFiles[$scriptName] as $file) {
            $path = APP_PATH . "/config/{$file}";
            if ( ! is_file($path)) {
                throw new Exception("Required configuration file {$path} is missing. Create one based on example configuration and verify it's contents.");
            }
        }

        return true;
    }

    /**
     * Converts site name to class name.
     *
     * @param string $siteName Site name
     *
     * @return string Class name
     */
    public static function siteNameToClassName($siteName) {
        $parts = explode('.', $siteName);
        $uppercaseParts = array_map(function($part) {
            return ucfirst($part);
        }, $parts);
        $className = 'Agent' . implode('', $uppercaseParts);

        return $className;
    }

    /**
     * Checks if the specified path points to an empty directory.
     *
     * @param string $path Path to check
     *
     * @return bool 	   True if empty, false otherwise
     */
    public static function isEmptyDir($path) {
        if ( ! is_readable($path)) {
            throw Exception('Failed to read directory');
        }

        $handle = opendir($path);
        while (false !== ($entry = readdir($handle))) {
            if ($entry !== '.' && $entry !== '..') {
                return false;
            }
        }

        return true;
    }

    /**
     * Recursively searches input path for files, scrapes them using
     * the provided agent and writes scraped data to output path.
     *
     * @param string      $inputPath  Location of files to be scraped
     * @param string      $outputPath Location of output files
     * @param object      $agent      Scraping agent instance
     * @param string/bool $encoding   Encoding of input files (optional)
     *
     * @return void 			 No return data. All output is written to file.
     */
    public static function scrapeDirectory($inputPath, $outputPath, $agent, $encoding = false) {
        $logger = Logger::getLogger('ScraperLib');
        $entries = scandir($inputPath);

        foreach ($entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $childPath = "{$inputPath}/{$entry}";
                if (is_dir($childPath)) {
                    self::scrapeDirectory($childPath, $outputPath, $agent, $encoding);
                }
                else {
                	try {
                        if ($encoding === false) {
                            $encoding = self::detectEncoding($childPath);
                        }

                        $logger->debug($childPath . ' => ' . $encoding);
                        if ($encoding !== false && $encoding !== 'ascii/unknown' && $encoding !== 'UTF-8') {
                            rename($childPath, "{$childPath}.orig");
                            shell_exec("iconv -f '{$encoding}' -t UTF-8 '{$childPath}.orig' -o '{$childPath}'");
                            unlink("{$childPath}.orig");                         
                        }

                        $dom = file_get_html($childPath);
    	                    
                        if ($dom) {
                            $job = $agent->scrape($dom);

                            if ($job) {

                                if (is_object($job) === false || get_class($job) !== 'Job') {
                                    
                                    try {
                                        throw new Exception("Agent returned invalid data for file '{$childPath}'. Expected instance of class 'Job'");
                                    } catch (Exception $e) {
                                      // do nothing... php will ignore and continue    
                                    }                                 
                                }

                                $validationResult = $job->validate();

	                            if ($validationResult === true) {
	                                $filename = $job->getForeignId() . '.json';
	                                file_put_contents("{$outputPath}/{$filename}", $job->toJson());
	                            }
                                else {
                                    $logger->warn("WARNING: {$childPath} is invalid: {$validationResult}");
                                }
	                        }
	                    }
	                }
	                catch (Exception $e) {
	                	$logger->error($e);
	                }
                }
            }
        }
    }

    /**
     * Pushes all jobs located at the specified path to the specified Active MQ queue.
     *
     * @param string $inputPath       Input path
     * @param string $url             Active MQ url
     * @param string $queueName       Queue name
     * @param bool   $limit           Limit the number of pushed jobs to this (optional)
     * @param bool   $deleteAfterPush Files are deleted after being pushed
     *
     * @return integer          Number of pushed jobs
     */
    public static function pushJobs($inputPath, $url, $queueName, $limit = false, $deleteAfterPush = false) {
        $files = array();
        if (is_file($inputPath)) {
            $files[] = $inputPath;
        }
        else {
            $files = glob("{$inputPath}/*.json");
        }

        // New connection
        $queue = new Stomp($url);

        // Connect
        $queue->connect();

        $numPushed = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $jobData = json_decode($content, true);

            $message = array(
                'method' => 'replaceJob',
                'params' => array(
                    'foreign_id' => $jobData['FOREIGN_ID'],
                    'data' => $jobData
                )
            );

            $jsonMessage = json_encode($message, JSON_UNESCAPED_UNICODE);

            $queue->send($queueName, $jsonMessage);
            $numPushed++;

            if ($deleteAfterPush) {
                unlink($file);
            }

            if ($limit && $numPushed === $limit) {
                break;
            }
        }

        // All good clients unsub when done
        $queue->unsubscribe($queueName);

        // All good clients disconnect whne done
        $queue->disconnect();

        return $numPushed;
    }

    /**
     * Loads site configurations from the specified path.
     *
     * @param string $path The path of the configuration files.
     * @param bool $siteName Site name (optional).
     *
     * @return array Site configurations in the form of associative arrays.
     */
    public static function loadSiteConfig($path, $siteName = false) {

        // Keys that must be present in a config file for it to be considered valid.

        $expectedKeys = array('name', 'interval', 'cmd');

        if ( ! file_exists($path)) {
            throw new Exception("Configuration directory '{$path}' does not exist.");
        }

        // Load site configuration(s).

        $configFiles = scandir($path);
        $configs = array();

        foreach ($configFiles as $configFile) {

            if (is_file("{$path}/{$configFile}") && strtolower(pathinfo($configFile, PATHINFO_EXTENSION)) === 'json') {

                if ($siteName === false || $configFile === "{$siteName}.json") {
                    $contents = file_get_contents("{$path}/{$configFile}");
                    $config = json_decode($contents, true);

                    // Validate the configuration.

                    foreach ($expectedKeys as $expectedKey) {
                        if ( ! isset($config[$expectedKey])) {
                            throw new Exception("Invalid site configuration. Value '{$expectedKey}' missing from {$path}/{$configFile}.");
                        }
                    }

                    $filename = pathinfo($configFile, PATHINFO_FILENAME);
                    $configs[$filename] = $config;
                }
            }
        }

        return $configs;
    }

    /**
     * Returns PIDs of running processes.
     *
     * @return array PIDs
     */
    public static function getRunningPids() {
        exec("ps aux", $psOutput);

        $pids = array();
        foreach ($psOutput as $line) {
            preg_match('/^\S+\s+(\d+)/', $line, $matches);

            if (count($matches) == 2) {
                $pids[] = (int) $matches[1];
            }
        }

        return $pids;
    }

    /**
     * Returns stored PIDs of httrack instances launched by the crawler.
     * 
     * @return array Associative array of the form $siteName => $pid.
     */
    public static function getStoredPids() {
        $files = glob(APP_PATH . '/run/*.pid');

        $pids = array();
        foreach ($files as $file) {
            $pid = file_get_contents($file);
            $siteName = basename($file, '.pid');
            $pids[$siteName] = (int) $pid;
        }

        return $pids;
    }

    /**
     * Deletes PID files that can't be tied to any running httrack instance.
     */
    public static function deleteOrphanPidFiles() {
        $storedPids = self::getStoredPids();
        $runningPids = self::getRunningPids();

        foreach ($storedPids as $siteName => $storedPid) {
            if ( ! in_array($storedPid, $runningPids)) {
                unlink(APP_PATH . "/run/{$siteName}.pid");
            }
        }
    }

    /**
     * Deletes output files.
     * 
     * @param  string     $outputPath    Output path.
     * @param  array/bool $excludedSites Name(s) of site(s) that should be
     *                                   excluded from the purge.
     * @throws InvalidArgumentException
     */
    public static function deleteOutput($outputPath, $excludedSites = false) {
        if ( ! is_dir($outputPath)) {
            throw new InvalidArgumentException("{$outputPath} is not a directory");
        }

        if ($excludedSites && is_array($excludedSites) === false) {
            throw new InvalidArgumentException("$excludedSites should be an array of sitenames");
        }

        $paths = glob("{$outputPath}/*", GLOB_ONLYDIR);

        foreach ($paths as $path) {
            $parts = pathinfo($path);
            $dir = $parts['basename'];

            if ($excludedSites === false || in_array($dir, $excludedSites) === false) {
                self::deleteDir($path);
            }
        }
    }

    /**
     * Recursively deletes the specified directory.
     * 
     * @param string $dirPath           Directory path.
     * @throws InvalidArgumentException
     */
	public static function deleteDir($dir) {
		if ( ! is_dir($dir)) {
			throw new InvalidArgumentException("{$dirPath} must be a directory");
		}
		else {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object !== '.' && $object !== '..') {
					if (filetype("{$dir}/{$object}") === 'dir') {
						self::deleteDir("{$dir}/{$object}");
					}
					else { 
						unlink("{$dir}/{$object}");
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
}
