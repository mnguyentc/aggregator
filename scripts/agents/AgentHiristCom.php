<?php

/**
 * Scraping agent for hirist.com.
 */
class AgentHiristCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input#jobid')) {
			if ($dom->find('h3[itemprop=title]')) {
				if ($dom->find('div[itemprop=description]')){

					//Get foreign id

					foreach($dom->find('input#jobid') as $id) {
						$job->setForeignId('hirist_com_' . trim($id->value));
					}

	    			//Get job title
					foreach($dom->find('h3[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[itemprop=description]') as $DESCRIPTION) {
						
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('style') as $ret3) {
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

					
					if (count($dom->find('div#jobrecinfo small.gry_txt')) < 1) {
						$job->addJobLocation("India", false, false, 273712);
					} else {
						foreach($dom->find('div#jobrecinfo small.gry_txt') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Location") {
								if (strlen(trim($LOCATION->next_sibling()->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("India", false, false, 273712);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->next_sibling()->plaintext), false, false, 273712);
								}
							}
						}
					}
					
					foreach($dom->find('link[rel=canonical]') as $Link) {
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