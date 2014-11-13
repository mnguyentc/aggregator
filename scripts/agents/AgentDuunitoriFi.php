<?php

/**
 * Scraping agent for duunitori.fi.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentDuunitoriFi extends Agent {

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
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div[itemprop=description]')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $id) {
						$str = substr(strrchr($id->content, '-'), 1);
						$job->setForeignId('duunitori_fi_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=title]') as $Jobtitle) {	
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description

					foreach($dom->find('div[itemprop=description]') as $DESCRIPTION)
					
					foreach($dom->find('div.article-info') as $SUMMARY){
						$job->setDescription($DESCRIPTION->innertext . "\n" . $SUMMARY->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Finland";
					$regionId = 273677;
					
					if (count($dom->find('span[itemprop=streetAddress]')) >= 1) {
						foreach ($dom->find('span[itemprop=streetAddress]') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->plaintext)));
								$location = rtrim($str,',');
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