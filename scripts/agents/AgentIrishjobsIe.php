<?php
/**
 * Scraping agent for irishjobs.ie.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentIrishjobsIe extends Agent {

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

		if ($dom->find('li.save-job a')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.job-details')){

					//Get foreign id
					foreach($dom->find('li.save-job a') as $id) {
						$job->setForeignId('irishjobs_ie_' . trim($id->getAttribute('jobid')));
					}

					//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}
/*
					//Get company name
					foreach($dom->find('div.border-wrap h2') as $Company) {						
						$job->setCompanyId(trim($Company->plaintext));
					}*/

					//Get job description
					foreach($dom->find('div.job-details') as $DESCRIPTION) {
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
					
					if (count($dom->find('li.location')) >= 1) {
						foreach ($dom->find('li.location') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$location = trim($LOCATION->plaintext);
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