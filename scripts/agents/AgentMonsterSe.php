<?php

/**
 * Scraping agent for monster.se.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMonsterSe extends Agent {

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

		if ($dom->find('link[rel=canonical]')) {
			if ($dom->find('title')) {
				if ($dom->find('a#ejb_SubHdTxt')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$str = substr(strrchr(implode($output[1]), '-'), 1);
						$str2 = explode('.', $str);
						$job->setForeignId('monster_se_' . trim($str2[0]));
					}

	    			//Get job title
					foreach($dom->find('title') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div#monsterAppliesContentHolder') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#iaactionfixed') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#ejb_sendJob') as $ret4) {
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
					$location = "Sweden";
					$regionId = 273803;
					
					if (count($dom->find('input#jobLocation')) >= 1) {
						foreach ($dom->find('input#jobLocation') as $LOCATION) {
							if (strlen(trim($LOCATION->value)) !== 0) {
								$location = current(explode(",", trim($LOCATION->value)));
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