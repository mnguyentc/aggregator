<?php
/**
 * Scraping agent for recruitireland.com.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentRecruitirelandCom extends Agent {

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

		if ($dom->find('form#aspnetForm')) {
			if ($dom->find('h1.job-header')) {
				if ($dom->find('div#ctl00_mainContent_jobInfo')){

					//Get foreign id
					foreach($dom->find('form#aspnetForm') as $id) {
						$str = substr(strrchr($id->action, '='), 1);
						$job->setForeignId('recruitireland_com_' . trim($str));
					}

					//Get job title
					foreach($dom->find('h1.job-header') as $Jobtitle) {
						foreach ($Jobtitle->find('span') as $ret1) {
							$ret1->innertext = '';
							$ret1->outertext = '';
						}

						$job->setJobTitle(trim($Jobtitle->plaintext));
					}
/*
					//Get company name
					foreach($dom->find('div.job-owner-description h3') as $Company) {
						$job->setCompanyId(trim($Company->plaintext));
					}*/

					//Get job description
					foreach($dom->find('div#ctl00_mainContent_jobInfo div.generic-text') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('img') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('script') as $ret2) {
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
					$location = "Ireland";
					$regionId = 273709;
					
					if (count($dom->find('h1.job-header span')) >= 1) {
						foreach ($dom->find('h1.job-header span') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$location = trim($LOCATION->plaintext);
							}

							if ($location !== "Ireland") {
								$key = ScraperLib::searchForId($location, 'Ireland', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273709;
								} else {
									$regionId = $key;
								}
							}							
						}
					}
				
					$job->addJobLocation($location, false, false, $regionId);
					
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString="by HTTrack";
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