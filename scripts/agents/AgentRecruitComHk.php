<?php

/**
 * Scraping agent for recruit.com.hk.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentRecruitComHk extends Agent {

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

		if ($dom->find('span[id=jobDetail_jobOrderLabel]')) {
			if ($dom->find('title')) {
				if ($dom->find('div[id=jobDetail_job_detail_div]')){

					//Get foreign id
					foreach($dom->find('span[id=jobDetail_jobOrderLabel]') as $id) {
						$str = substr(strrchr($id->plaintext, ':'), 1);
						$job->setForeignId('recruit_com_hk_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$str = explode('|', $Jobtitle->innertext);
						$job->setJobTitle(trim($str[0]));
					}

					//Get job description
					foreach($dom->find('div[id=jobDetail_job_detail_div]') as $DESCRIPTION) 

					foreach($dom->find('div[id=job_summary]') as $SUMMARY){
						$job->setDescription($DESCRIPTION->innertext."\n".$SUMMARY->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Hong Kong";
					$regionId = 273702;
					
					if (count($dom->find('span#jobDetail_locationLabel')) >= 1) {
						foreach ($dom->find('span#jobDetail_locationLabel') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode("/", trim($LOCATION->plaintext)));
								if (strpos($str,'District') !== false) {
								    $location = trim(substr($str, 0, -9));
								} else {
									$location = trim($str);
								}
							}

							if ($location !== "Hong Kong") {
								$key = ScraperLib::searchForId($location, 'Hong Kong', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273702;
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