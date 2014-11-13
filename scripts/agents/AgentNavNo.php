<?php

/**
 * Scraping agent for nav.no.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentNavNo extends Agent {

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

		if ($dom->find('form#sokeboksForm')) {
			if ($dom->find('h2.tittel')) {
				if ($dom->find('div.beskrivelse')){

					//Get foreign id
					foreach($dom->find('form#sokeboksForm') as $Link) {
						$startString="ID=";
						$endString="&";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setForeignId('nav_no_' . trim(implode($output[1])));
					}

	    			//Get job title
					foreach($dom->find('h2.tittel') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.beskrivelse') as $DESCRIPTION) {
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
					$location = "Norway";
					$regionId = 273773;
					
					if (count($dom->find('span.navn')) >= 1) {
						foreach ($dom->find('span.navn') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Arbeidssted:") {				
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) !== 0) {
									$str = current(explode(" ", trim($LOCATION->next_sibling()->plaintext)));
									$location = rtrim($str, ",");
								}

								if ($location !== "Norway") {
									$key = ScraperLib::searchForId($location, 'Norway', $citylist);
									if (strlen($key) === 0) {
										$regionId = 273773;
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