<?php

/**
 * Scraping agent for guichetemplois.gc.ca.
 */
class AgentGuichetemploisGcCa extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('h2[itemprop=jobLocation]')) {
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div#job_listing')){

					//Get foreign id
					foreach($dom->find('div.float-right h4') as $Link) {
						if (trim($Link->plaintext) == "Numéro de l'offre :") {
							$job->setForeignId('guichetemplois_gc_ca_' . trim($Link->next_sibling()->plaintext));
						}	
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div#job_listing') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('h1') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('h2') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						$DESCRIPTION->lastChild()->innertext = '';
						$DESCRIPTION->lastChild()->outertext = '';

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

					
					if (count($dom->find('span[itemprop=address]')) < 1) {
						$job->addJobLocation("Canada", false, false, 273645);
					} else {
						foreach ($dom->find('span[itemprop=address]') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Canada", false, false, 273645);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273645);
							}		
						}
					}

					foreach($dom->find('li#gcwu-gcnb-lang a') as $Link) {
						$job->setJobRouting(false, $Link->href, 2);
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