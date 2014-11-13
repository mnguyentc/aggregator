<?php

/**
 * Scraping agent for blocket.se.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentBlocketSe extends Agent {

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
				if ($dom->find('div[id=view-about-job]')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '/'), 1);
						$job->setForeignId('blocket_se_' . trim($str));
					}

					//Get job title
					foreach($dom->find('h1') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[id=view-about-job]') as $DESCRIPTION) {
						$job->setDescription($DESCRIPTION->innertext);	
					}

					//Get job location
					$location = "Sweden";
					$regionId = 273803;
					
					if (count($dom->find('dl')) >= 1) {
						foreach ($dom->find('dl') as $LOCATION) {
							if (trim($LOCATION->children(0)->plaintext) == "Ort") {
								if (strlen(trim($LOCATION->children(1)->plaintext)) !== 0) {
									$location = current(explode(" ", trim($LOCATION->children(1)->plaintext)));
								}

								if ($location !== "Sweden") {
									$key = ScraperLib::searchForId($location, 'Sweden', $citylist);
									if (strlen($key) === 0) {
										$regionId = 273803;
									} else {
										$regionId = $key;
									}
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