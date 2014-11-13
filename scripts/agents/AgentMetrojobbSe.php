<?php
/**
 * Scraping agent for metrojobb.se.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMetrojobbSe extends Agent {

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
		
		if ($dom->find('a.addthis_button_email')) {
			if ($dom->find('h1')) {
				if ($dom->find('section.job-ad__main')){

					//Get foreign id
					foreach($dom->find('meta[id=criteoData]') as $id) {
						$job->setForeignId('metrojobb_se_' . $id->attr['data-jobad-id']);
					}
					
	    			//Get job title
					foreach($dom->find('section.job-ad__main h1') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('section.job-ad__main') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('h1') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}
						
						$job->setDescription($DESCRIPTION->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Sweden";
					$regionId = 273803;
					
					if (count($dom->find('li.job-ad__sidebar__location')) >= 1) {
						foreach ($dom->find('li.job-ad__sidebar__location') as $LOCATION) {
							foreach ($LOCATION->find('span') as $ret) {
								$ret->innertext = '';
								$ret->outertext = '';
							}

							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->plaintext)));
								$location = rtrim($str, ",");
							}																					
						}
					}

					if (count($dom->find('li.job-ad__sidebar__location')) < 1 && count($dom->find('li.job-ad__sidebar__region')) >= 1) {
						foreach ($dom->find('li.job-ad__sidebar__region') as $LOCATION) {
							foreach ($LOCATION->find('span') as $ret) {
								$ret->innertext = '';
								$ret->outertext = '';
							}

							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->plaintext)));
								$location = rtrim($str, ",");
							}																					
						}
					}

					if ($location !== "Sweden") {
						$key = ScraperLib::searchForId($location, 'Sweden', $citylist);
						if (strlen($key) === 0) {
							$regionId = 273803;
						} else {
							$regionId = $key;
						}
					}	
				
					$job->addJobLocation($location, false, false, $regionId);

					foreach($dom->find('a.addthis_button_email') as $id) {
						$job->setJobRouting(false, $id->attr['addthis:url'], 2);
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