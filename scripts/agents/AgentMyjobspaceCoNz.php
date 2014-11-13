<?php

/**
 * Scraping agent for myjobspace.co.nz.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMyjobspaceCoNz extends Agent {

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
				if ($dom->find('div.job_details')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '/'), 1);
						$job->setForeignId('myjobspace_co_nz_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description

					foreach($dom->find('div.job_details') as $DESCRIPTION) {
					
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

						foreach ($DESCRIPTION->find('h1') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "New Zealand";
					$regionId = 273777;
					
					if (count($dom->find('meta[name=keywords]')) >= 1) {
						foreach ($dom->find('meta[name=keywords]') as $LOCATION) {												
							if (strlen(trim($LOCATION->content)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->content)));							
								$location = rtrim($str, ",");
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