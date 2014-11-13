<?php

/**
 * Scraping agent for uranus.fi.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentUranusFi extends Agent {

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

		if ($dom->find('div.path_item_content a')) {
			if ($dom->find('h1')) {
				if ($dom->find('div[id=main_column]')){

					//Get foreign ID
					foreach($dom->find('div.path_item_content a') as $id) {
						$str = substr(strrchr($id->href, '/'), 1);
						$job->setForeignId('uranus_fi_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[id=main_column]') as $DESCRIPTION) {
						$job->setDescription($DESCRIPTION->children(2)->innertext);
					}

					//Get job location
					$location = "Finland";
					$regionId = 273677;
					
					if (count($dom->find('h3')) >= 1) {
						foreach ($dom->find('h3') as $LOCATION) {
							if (trim($LOCATION->plaintext) == 'Työpaikan sijainti') {							
								if (strlen(trim($LOCATION->next_sibling()->children(0)->plaintext)) !== 0) {
									if (is_numeric(trim($LOCATION->next_sibling()->children(1)->plaintext))) {
										$location = trim($LOCATION->next_sibling()->children(0)->plaintext);
									} else {
										$location = trim($LOCATION->next_sibling()->children(1)->plaintext);
									}
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
					}
				
					$job->addJobLocation($location, false, false, $regionId);

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$this->job['EXPIRE_DATE'] = $newdate."T".date("h:i").":00Z";

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