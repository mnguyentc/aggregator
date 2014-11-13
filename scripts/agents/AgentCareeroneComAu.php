<?php

/**
 * Scraping agent for careerone.com.au.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentCareeroneComAu extends Agent {

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
			if ($dom->find('title')) {
				if ($dom->find('div#monsterAppliesContentHolder')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$str = substr(strrchr(implode($output[1]), '-'), 1);
						$str2 = explode('.', $str);
						$job->setForeignId('careerone_com_au_' . trim($str2[0]));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div#monsterAppliesContentHolder') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#jobsummary') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#sidecol') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#jobheader') as $ret5) {
							$ret5->innertext = '';
							$ret5->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#ejb_sendJob') as $ret6) {
							$ret6->innertext = '';
							$ret6->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						foreach ($DESCRIPTION->find('style') as $ret8) {
							$ret8->innertext = '';
							$ret8->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Australia";
					$regionId = 273622;
					
					if (count($dom->find('input#jobLocation')) >= 1) {
						foreach ($dom->find('input#jobLocation') as $LOCATION) {							
							if (strlen(trim($LOCATION->value)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->value)));
								$location = rtrim($str, ",");
							}

							if ($location !== "Australia") {
								$key = ScraperLib::searchForId($location, 'Australia', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273773;
								} else {
									$regionId = $key;
								}
							}																																
						}
					}

					$job->addJobLocation($location, false, false, $regionId);

					foreach($dom->find('link[rel=canonical]') as $Link) {
						$job->setJobRouting(false, $Link->href, 2);
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