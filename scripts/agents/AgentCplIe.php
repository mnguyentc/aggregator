<?php
/**
 * Scraping agent for cpl.ie.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentCplIe extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return array Job data in the form of an associative array.
	 */
	public function scrape($dom) {

		$job = new Job();
		$citylist = $GLOBALS['citylist'];

		if ($dom->find('input[name=jobreference]')) {
			if ($dom->find('title')) {
				if ($dom->find('div#job_description')){

					//Get foreign id
					foreach($dom->find('input[name=jobreference]') as $id) {
						$job->setForeignId('cpl_ie_' . trim($id->value));
					}

					//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get company name
					//$job->setCompanyId("Cpl Recruitment");

					//Get job description
					foreach($dom->find('div#job_description') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('img') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('script') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Ireland";
					$regionId = 273709;
					
					if (count($dom->find('th')) >= 1) {
						foreach ($dom->find('th') as $LOCATION) {	
							if (trim($LOCATION->plaintext) === "Locations") {					
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) !== 0) {
									$str = current(explode(" ", trim($LOCATION->next_sibling()->plaintext)));
									$location = rtrim($str,',');
								}

								if ($location !== "Ireland") {
									$key = ScraperLib::searchForId($location, 'Ireland', $citylist);
									if (strlen($key) === 0) {
										$regionId = 273709;
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
						$endString="by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, 'http://'.implode($output[1]), 2);
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