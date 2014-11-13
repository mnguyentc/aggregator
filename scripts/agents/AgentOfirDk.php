<?php

/**
 * Scraping agent for ofir.dk.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentOfirDk extends Agent {

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

		if ($dom->find('link[rel=canonical]')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.JobAd')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '='), 1);
						$job->setForeignId('ofir_dk_' . $str);
					}

	    			//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.JobAd') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						foreach ($DESCRIPTION->find('h1') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);					

					//Get job location
					$location = "Denmark";
					$regionId = 273665;
					
					if (count($dom->find('div.JobAddressBody')) >= 1) {
						foreach ($dom->find('div.JobAddressBody') as $LOCATION) {				
							if (strlen(trim($LOCATION->children(2)->plaintext)) !== 0) {
								$str = explode(" ", trim($LOCATION->children(2)->plaintext));
								$location = rtrim($str[1], ",");
							}

							if ($location !== "Denmark") {
								$key = ScraperLib::searchForId($location, 'Denmark', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273665;
								} else {
									$regionId = $key;
								}
							}																										
						}
					}
				
					$job->addJobLocation($location, false, false, $regionId);

					foreach($dom->find('link[rel=canonical]') as $Link) {
						$job->setJobRouting(false, $Link->href, 2);
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