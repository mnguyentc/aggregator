<?php

/**
 * Scraping agent for stjobs.sg.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentStjobsSg extends Agent {

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

		if ($dom->find('div[class=col-sm-9]')) {
			if ($dom->find('h1.text-primary')) {
				if ($dom->find('div[class=job-page-description]')) {

					//Get foreign id
					foreach($dom->find('comment') as $id) {
						$startString="view-job/";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $id, $output, PREG_PATTERN_ORDER);					
						$job->setForeignId('stjobs_sg_' . implode($output[1]));
					}

	    			//Get job title
					foreach($dom->find('h1.text-primary') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div[class=job-page-description]') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.job-page-share') as $ret1) {
							$ret1->innertext = '';
							$ret1->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.job-page-share') as $ret2) {
							$ret2->prev_sibling ()->innertext = '';
							$ret2->prev_sibling ()->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
					}
					

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Singapore";
					$regionId = 273804;
					
					if (count($dom->find('i[class=fa fa-road]')) >= 1) {
						foreach ($dom->find('i[class=fa fa-road]') as $LOCATION) {												
							if (strlen(trim($LOCATION->next_sibling()->plaintext)) !== 0) {							
								$location = trim($LOCATION->next_sibling()->plaintext);
							}

							if ($location !== "Singapore") {
								$key = ScraperLib::searchForId($location, 'Singapore', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273804;
								} else {
									$regionId = $key;
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