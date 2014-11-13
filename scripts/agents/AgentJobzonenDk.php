<?php

/**
 * Scraping agent for jobzonen.dk.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentJobzonenDk extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();
		$citylist = $GLOBALS['citylist'];

		if ($dom->find('link[rel=canonical]')) {
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div[itemprop=description]')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="job/";
						$endString="/";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setForeignId('jobzonen_dk_' . implode($output[1]));
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[itemprop=description]') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Denmark";
					$regionId = 273665;
					
					if (count($dom->find('span.label')) >= 1) {
						foreach ($dom->find('span.label') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Arbejdssted") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) !== 0) {
									$str = current(explode(" ", trim($LOCATION->next_sibling()->plaintext)));
									$location = rtrim($str, ",");

									if (is_numeric($location)) {
										$location = substr(strrchr(trim($LOCATION->next_sibling()->plaintext), ' '), 1);
									}
								}

								if ($location !== "Denmark") {
									$key = ScraperLib::searchForId($location, 'Denmark', $citylist);
									if (strlen($key) === 0) {
										$regionId = 273665;
									} else {
										$regionId = $key;
									}
								}
							}																					
						}
					}
				
					$job->addJobLocation($location, false, false, $regionId);

					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, "http://".implode($output[1]), 2);
					}

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
}