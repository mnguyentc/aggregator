<?php

/**
 * Scraping agent for staffpoint.fi.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentStaffpointFi extends Agent {

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

		if ($dom->find('link[rel=canonical]')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.main_column')) {

					//Get foreign ID
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '-'), 1);
						$job->setForeignId('staffpoint_fi_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.main_column') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('div.interested_buttons') as $ret) {
							$ret->innertext = '';
						}
						$job->setDescription($DESCRIPTION->innertext);
					}

					//Get job location
					$location = "Finland";
					$regionId = 273677;
					
					if (count($dom->find('div[class=job_blobs]')) >= 1) {
						foreach ($dom->find('div[class=job_blobs]') as $LOCATION) {							
							if (strlen(trim($LOCATION->children(2)->plaintext)) !== 0) {
								$location = trim($LOCATION->children(2)->plaintext);
							}

							if ($location !== "Finland") {
								$key = ScraperLib::searchForId($location, 'Finland', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273677;
								} else {
									$regionId = $key;
								}
							}							
						}
					}
				
					$job->addJobLocation($location, false, false, $regionId);

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);
					$outputDate = $newdate."T".date("h:i").":00Z";

					$job->setExpireDate($outputDate);

					$job->setJobRouting(false, $id->href, 2);			

			return $job;
			}
			else {
				return false;
			}
		}
	}
}
}