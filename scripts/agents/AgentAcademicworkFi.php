<?php

/**
 * Scraping agent for academicwork.fi.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentAcademicworkFi extends Agent {

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

		if ($dom->find('a[data-share=addthis]')) {
			if ($dom->find('h1')) {
				if ($dom->find('article.job')){
					
					//Get foreign ID
					foreach($dom->find('a[data-share=addthis]') as $FOREIGN_ID) {
						$str = substr(strrchr($FOREIGN_ID->href, '='), 1);
						$str2 = substr(strrchr($str, '/'), 1);
						$job->setForeignId('academicwork_fi_' . trim($str2));
					}	

					//Get job title
					foreach($dom->find('h1') as $this->jobtitle) {
						$job->setJobTitle($this->jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('article.job') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('section.job-introduction') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.row') as $ret1) {
							$ret1->innertext = '';
							$ret1->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('div[id=jobad-contact-container]') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}
						
						$desc1 = str_replace("<pre","<p",$DESCRIPTION->innertext);
						$desc2 = str_replace("pre>","p>",$desc1);
						//$this->job['DESCRIPTION'] = $DESCRIPTION;
						$job->setDescription($desc2);
					}

					//Get expire date
					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Finland";
					$regionId = 273677;
					
					if (count($dom->find('i.icon-aw-pin')) >= 1) {
						foreach ($dom->find('i.icon-aw-pin') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$location = trim($LOCATION->plaintext);
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

					foreach($dom->find('a[data-share=addthis]') as $ROUTING_URL) {
						$str = substr(strrchr($ROUTING_URL->href, '='), 1);
						$job->setJobRouting(false, trim($str), 2);
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