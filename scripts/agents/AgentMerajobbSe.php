<?php

/**
 * Scraping agent for merajobb.se.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMerajobbSe extends Agent {

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

		if ($dom->find('input[id=MainContent_JobAdDetails_hfAdID]')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.jobAdDescription')){

					//Get foreign id
					foreach($dom->find('input[id=MainContent_JobAdDetails_hfAdID]') as $id) {
						$str = substr(strrchr($id->href, '='), 1);
						$job->setForeignId('merajobb_se_' . $id->value);
					}

	    			//Get job title
					foreach($dom->find('h1') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.jobAdDescription') as $DESCRIPTION) {
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
					
					if (count($dom->find('ul.jobInfoTags')) >= 1) {
						foreach ($dom->find('ul.jobInfoTags') as $LOCATION) {
							if (strlen(trim($LOCATION->children(2)->plaintext)) !== 0) {
								$location = mb_convert_case(trim($LOCATION->children(2)->plaintext), MB_CASE_TITLE, "UTF-8");
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
				
					$job->addJobLocation($location, false, false, $regionId);

					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
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