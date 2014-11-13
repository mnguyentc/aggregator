<?php

/**
 * Scraping agent for vdab.be.
 */
class AgentVdabBe extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('a#apply_onder')) {
			if ($dom->find('h1')) {
				if ($dom->find('div#vacatureContent')){

					//Get foreign id
					foreach($dom->find('a#apply_onder') as $Link) {
						$startString="ID=";
						$endString="&amp";
						preg_match_all ("|$startString(.*)$endString|U", $Link->href, $output, PREG_PATTERN_ORDER);

						$startString1="sess=";
						$endString1="&amp";
						preg_match_all ("|$startString1(.*)$endString1|U", $Link->href, $output1, PREG_PATTERN_ORDER);

						$job->setForeignId('vdab_be_' . implode($output[1]). implode($output1[1]));
					}

	    			//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div#vacatureContent') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('style') as $ret4) {
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

					if (count($dom->find('title')) < 1) {
						$job->addJobLocation("Belgium", false, false, 273629);
					} else {
						foreach($dom->find('title') as $LOCATION) {	
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Belgium", false, false, 273629);
							} else {
								$str = substr(strrchr(trim($LOCATION->plaintext), ' '), 1);					 
								$job->addJobLocation(trim($str, '()'), false, false, 273629);
							}									
						}
					}	

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