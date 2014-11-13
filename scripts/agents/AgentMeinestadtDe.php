<?php

/**
 * Scraping agent for meinestadt.de.
 */
class AgentMeinestadtDe extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return array Job data in the form of an associative array.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input[name=params]')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.ms-tab-container-inner')){

					//Get foreign id
					foreach($dom->find('input[name=params]') as $id) {
						$str = explode('=', $id->value);
						$job->setForeignId('meinstadt_de_' . trim($str[1]));
					}

					//Get job title
					foreach($dom->find('h1') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.ms-tab-container-inner div.tab-content') as $DESCRIPTION) {
						$job->setDescription($DESCRIPTION->innertext);	
					}

					
					if (count($dom->find('div.ms-breadcrumb')) < 1) {
						$job->addJobLocation("Germany", false, false, 273663);
					} else {
						foreach($dom->find('div.ms-breadcrumb') as $LOCATION) {	
							if (strlen(trim($LOCATION->children(2)->plaintext)) === 0) {
								$job->addJobLocation("Germany", false, false, 273663);
							} else {
								$job->addJobLocation(trim($LOCATION->children(2)->plaintext), false, false, 273663);
							}					
						}
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

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