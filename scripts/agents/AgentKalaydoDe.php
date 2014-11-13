<?php

/**
 * Scraping agent for kalaydo.de.
 */
class AgentKalaydoDe extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('span.nmb')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.description')){

					//Get foreign id
					foreach($dom->find('span.nmb') as $id) {
						
						$str = explode(':', $id->plaintext);
						$job->setForeignId('kalaydo_de_' . trim($str[1]));
					}

	    			//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description

					foreach($dom->find('div.description') as $DESCRIPTION){
						$job->setDescription($DESCRIPTION->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);


					if (count($dom->find('span[itemprop=joblocation]')) < 1) {
						$job->addJobLocation("Germany", false, false, 273803);
					} else {
						foreach($dom->find('span[itemprop=joblocation]') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Germany", false, false, 273803);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273803);	
							}
						}
					}
					
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString="/?search";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, 'http://'.implode($output[1]), 2);
					}
					
					}

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
