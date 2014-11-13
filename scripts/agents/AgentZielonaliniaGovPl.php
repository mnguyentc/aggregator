<?php

/**
 * Scraping agent for zielonalinia.gov.pl.
 */
class AgentZielonaliniaGovPl extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('dl.props')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.offer-full')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$str = substr(strrchr(implode($output[1]), '='), 1);
						$job->setForeignId('zielonalinia_gov_pl_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.subcol-1') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
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

					if (count($dom->find('dt')) < 1) {
						$job->addJobLocation("Poland", false, false, 273785);
					} else {
						foreach ($dom->find('dt') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Lokalizacja:") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("Poland", false, false, 273785);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273785);
								}
							}
						}
					}

					foreach($dom->find('meta[property=og:url]') as $Link) {
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