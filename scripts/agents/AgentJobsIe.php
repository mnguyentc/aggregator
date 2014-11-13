<?php

/**
 * Scraping agent for jobs.ie.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentJobsIe extends Agent {

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

		if ($dom->find('form[name=aspnetForm]')) {
			if ($dom->find('span[itemprop=title]')) {
				if ($dom->find('div[itemtype=http://schema.org/JobPosting]')){

					//Get foreign id
					foreach($dom->find('form[name=aspnetForm]') as $id) {
						$str = substr(strrchr($id->action, '='), 1);
						$job->setForeignId('jobs_ie_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('span[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[itemtype=http://schema.org/JobPosting]') as $DESCRIPTION) {
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
					
					if (count($dom->find('span[itemprop=jobLocation]')) >= 1) {
						foreach ($dom->find('span[itemprop=jobLocation]') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->plaintext)));
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
				
					$job->addJobLocation($location, false, false, $regionId);

					$job->setJobRouting(false, $id->action, 2);
					

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
}