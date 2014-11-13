<?php

/**
 * Scraping agent for gazetapraca.pl.
 */
class AgentGazetapracaPl extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input#clIdHid')) {
			if ($dom->find('title')) {
				if ($dom->find('div#ogl')){

					//Get foreign id
					foreach($dom->find('input#clIdHid') as $Link) {
						$job->setForeignId('gazetapraca_pl_' . $Link->value);
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div#ogl') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.gora') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.dol') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('div.c0')) < 1) {
						$job->addJobLocation("Poland", false, false, 273785);
					} else {
						foreach ($dom->find('div.c0') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Lokalizacja:")
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("Poland", false, false, 273785);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273785);
								}	
							}
						}
					}

					foreach($dom->find('meta[property=og:url]') as $Link) {
						$job->setJobRouting(false, $Link->content, 2);
					}

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
