<?php

/**
 * Scraping agent for candle.co.nz.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentCandleCoNz extends Agent {

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

		if ($dom->find('input#incProductDetail_hiddenMobiProductID')) {
			if ($dom->find('td.heading')) {
				if ($dom->find('td.jobdetail')){

					//Get foreign id
					foreach($dom->find('input#incProductDetail_hiddenMobiProductID') as $id) {
						$job->setForeignId('candle_co_nz_' . trim($id->value));
					}

	    			//Get job title
					foreach($dom->find('td.heading') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description

					foreach($dom->find('td.jobdetail') as $DESCRIPTION) {
					
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('style') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('span.location')) < 1) {
						$job->addJobLocation("New Zealand", false, false, 273777);
					} else {
						foreach($dom->find('span.location') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("New Zealand", false, false, 273777);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273777);
							}
						}
					}

					//Get job location
					$location = "New Zealand";
					$regionId = 273777;
					
					if (count($dom->find('span.location')) >= 1) {
						foreach ($dom->find('span.location') as $LOCATION) {												
							if (strlen(trim($LOCATION->plaintext)) !== 0) {							
								$location = trim($LOCATION->plaintext);
								print($location."\n");
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
					
					foreach($dom->find('input#incProductDetail_hiddenMobiJobURL') as $Link) {
						$job->setJobRouting(false, "http://www.candle.co.nz".$Link->value, 2);
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