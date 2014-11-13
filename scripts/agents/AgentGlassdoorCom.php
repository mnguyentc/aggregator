<?php

/**
 * Scraping agent for glassdoor.com.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentGlassdoorCom extends Agent {

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

		if ($dom->find('meta[property=og:url]')) {
			if ($dom->find('title')) {
				if ($dom->find('div[id=JobDescContainer]')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $id) {
						$startString="jl=";
						$endString="&paoIdKey";
						preg_match_all ("|$startString(.*)$endString|U", $id->content, $output, PREG_PATTERN_ORDER);
						$job->setForeignId('glassdoor_com_'.implode($output[1]));
					}
						
	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$str = explode('|', $Jobtitle->plaintext);
						$job->setJobTitle(trim($str[0]));
					}

					//Get job description

					foreach($dom->find('div[id=JobDescContainer]') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('div.margTop') as $ret) {
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
					$location = "Finland";
					$regionId = 273677;
					
					if (count($dom->find('tt.i-cit')) >= 1) {
						foreach ($dom->find('tt.i-cit') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$location = trim($LOCATION->plaintext);
								print($location);
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
					
					$job->setJobRouting(false, $id->content, 2);

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
}