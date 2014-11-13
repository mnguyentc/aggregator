<?php

/**
 * Scraping agent for maoripacificjobs.co.nz.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMaoripacificjobsCoNz extends Agent {

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

		if ($dom->find('form.main_form')) {
			if ($dom->find('h1.title')) {
				if ($dom->find('div.section_content')){

					//Get foreign id
					foreach($dom->find('title') as $id) {
						$str = substr(strrchr(trim($id->plaintext), ':'), 1);
						$id = rtrim(trim($str),".");
						$job->setForeignId('maoripacificjobs_co_nz_' . $id);
					}

	    			//Get job title
					foreach($dom->find('h1.title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					$DESCRIPTION = $dom->find('div.section_content', 0);

					$job->setDescription($DESCRIPTION->innertext);
										

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "New Zealand";
					$regionId = 273777;
					
					if (count($dom->find('p.meta strong')) >= 1) {
						foreach ($dom->find('p.meta strong') as $LOCATION) {												
							if (strlen(trim($LOCATION->plaintext)) !== 0) {							
								$location = trim($LOCATION->plaintext);
							}

							if ($location !== "New Zealand") {
								$key = ScraperLib::searchForId($location, 'New Zealand', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273777;
								} else {
									$regionId = $key;
								}
							}

							break;																																							
						}
					}

					$job->addJobLocation($location, false, false, $regionId);
					
					foreach($dom->find('form.main_form') as $Link) {
						$job->setJobRouting(false, $Link->action, 2);
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