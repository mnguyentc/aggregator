<?php

/**
 * Scraping agent for jobsdb.com.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentJobsdbCom extends Agent {

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
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div[class=jobad-primary-details]') == TRUE || $dom->find('div[class=job-ad-job-desc jobDesc]') == TRUE){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '='), 1);
						$job->setForeignId('jobsdb_com_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					if (count($dom->find('div[class=job-ad-job-desc jobDesc]') > 1)) {
						foreach($dom->find('div[class=job-ad-job-desc jobDesc]') as $DESCRIPTION) {
							foreach ($DESCRIPTION->find('script') as $ret) {
								$ret->innertext = '';
								$ret->outertext = '';
							}

							$job->setDescription($DESCRIPTION->innertext);
						}
					} 

					if (count($dom->find('div[class=jobad-primary-details]') > 1)) {
						foreach($dom->find('div[class=jobad-primary-details]') as $DESCRIPTION) {
							foreach ($DESCRIPTION->find('script') as $ret) {
								$ret->innertext = '';
								$ret->outertext = '';
							}

							$job->setDescription($DESCRIPTION->innertext);
						}
					}
					

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Singapore";
					$regionId = 273804;
					
					if (count($dom->find('[itemprop=jobLocation]')) >= 1) {
						foreach ($dom->find('[itemprop=jobLocation]') as $LOCATION) {												
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(",", trim($LOCATION->plaintext)));									
								$location = trim($str);
							}

							if ($location !== "Singapore") {
								$key = ScraperLib::searchForId($location, 'Singapore', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273804;
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