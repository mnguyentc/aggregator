<?php

/**
 * Scraping agent for jobbnorge.no.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentJobbnorgeNo extends Agent {

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

		if ($dom->find('div[itemprop=jobLocation]')) {
			if ($dom->find('title')) {
				if ($dom->find('div.stillingen')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $Link) {
						$id = explode('/', $Link->content);
						$job->setForeignId('jobbnorge_no_' . trim($id[5]));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					$DESCRIPTION = $dom->find('section', 0);

					foreach ($DESCRIPTION->find('script') as $ret) {
						$ret->innertext = '';
						$ret->outertext = '';
					}

					foreach ($DESCRIPTION->find('img') as $ret1) {
						$ret1->innertext = '';
						$ret1->outertext = '';
					}

					$job->setDescription($DESCRIPTION->innertext);

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Norway";
					$regionId = 273773;
					
					if (count($dom->find('div[itemprop=jobLocation]')) >= 1) {
						foreach ($dom->find('div[itemprop=jobLocation]') as $LOCATION) {				
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->plaintext)));
								$location = rtrim($str, ",");
							}

							if ($location !== "Norway") {
								$key = ScraperLib::searchForId($location, 'Norway', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273773;
								} else {
									$regionId = $key;
								}
							}

							break;																										
						}
					}
				
					$job->addJobLocation($location, false, false, $regionId);

					foreach($dom->find('meta[property=og:url]') as $Link) {
						$job->setJobRouting(false, $Link->content, 2);
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