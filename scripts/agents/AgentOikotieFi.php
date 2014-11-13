<?php
/**
 * Scraping agent for oikotie.fi.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentOikotieFi extends Agent {

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
			if ($dom->find('h1#jobTitle')) {
				if ($dom->find('div[id=jobDescription]')){

				    foreach($dom->find('link[rel=canonical]') as $FOREIGN_ID) {
						$str = substr(strrchr($FOREIGN_ID->href, '/'), 1);
						$job->setForeignId('oikotie_fi_' . trim($str));
					}

				    //Get job title
					foreach($dom->find('h1#jobTitle') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->children(0)->plaintext);
					}

					//Get job description
					foreach($dom->find('div[id=jobDescription]') as $DESCRIPTION) {
						$job->setDescription($DESCRIPTION->innertext);	
					}

					//Get job location
					$location = "Finland";
					$regionId = 273677;
					
					if (count($dom->find('span[property=schema:addressLocality]')) >= 1) {
						foreach ($dom->find('span[property=schema:addressLocality]') as $LOCATION) {							
							if (strlen(trim($LOCATION->content)) !== 0) {
								$location = current(explode(",", trim($LOCATION->content)));
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

					foreach($dom->find('link[rel=canonical]') as $ROUTING_URL) {
						$job->setJobRouting(false, $ROUTING_URL->href, 2);
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