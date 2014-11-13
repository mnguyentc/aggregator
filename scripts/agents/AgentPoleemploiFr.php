<?php

/**
 * Scraping agent for poleemploi.fr.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentPoleemploiFr extends Agent {

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

		if ($dom->find('comment')) {
			if ($dom->find('h4[itemprop=title]')) {
				if ($dom->find('div#offre-body')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$str = substr(strrchr(implode($output[1]), '/'), 1);
						$job->setForeignId('poleemploi_fr_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h4[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->next_sibling()->plaintext);
					}

					//Get job description
					foreach($dom->find('div#offre-body') as $DESCRIPTION) {
						
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					if (count($dom->find('li[itemprop=addressRegion]')) < 1) {
						$job->addJobLocation("France", false, false, 273682);
					} else {
						foreach ($dom->find('li[itemprop=addressRegion]') as $LOCATION) {
							if (strlen(trim($LOCATION)) === 0) {
								$job->addJobLocation("France", false, false, 273682);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273682);
							}
						}
					}
				
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, "https://".implode($output[1]), 2);
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
