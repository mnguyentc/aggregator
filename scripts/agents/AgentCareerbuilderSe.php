<?php
/**
 * Scraping agent for careerbuilder.se.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentCareerbuilderSe extends Agent {

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

		if ($dom->find('link[rel=alternate]')) {
			if ($dom->find('h1[id=h1JobTitle]')) {
				if ($dom->find('div[id=jdpDescrption]')){

					//Get foreign id
					foreach($dom->find('link[rel=alternate]') as $id) {
						$str = explode("/", $id->href);
						$job->setForeignId('careerbuilder_se_' . trim($str[4]));
					}

					//Get job title
					foreach($dom->find('h1[id=h1JobTitle]') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[id=jdpDescrption]') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('div[id=jdpSnapShot]') as $ret) {
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
					$location = "Sweden";
					$regionId = 273803;
					
					if (count($dom->find('div[id=lieuValue]')) >= 1) {
						foreach ($dom->find('div[id=lieuValue]') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$location = current(explode(",", trim($LOCATION->plaintext)));
							}

							if ($location !== "Sweden") {
								$key = ScraperLib::searchForId($location, 'Sweden', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273803;
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
						$job->setJobRouting(false, "http://".implode($output[1]), 2);
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