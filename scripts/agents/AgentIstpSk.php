<?php

/**
 * Scraping agent for istp.sk.
 */
class AgentIstpSk extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input[name=poslat_link_cislo]')) {
			if ($dom->find('h1.mainTitle')) {
				if ($dom->find('div.titleWithIntro')){

					//Get foreign id
					foreach($dom->find('input[name=poslat_link_cislo]') as $id) {
						$job->setForeignId('istp_sk_' . trim($id->value));
					}

	    			//Get job title
					foreach($dom->find('h1.mainTitle') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div.col2 div.padding') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('ul.menuButton') as $ret2) {
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

					
					if (count($dom->find('span.title')) < 1) {
						$job->addJobLocation("Slovakia", false, false, 273808);
					} else {
						foreach ($dom->find('span.title') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Miesto výkonu práce:") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("Slovakia", false, false, 273808);
								} else {
									$job->addJobLocation($LOCATION->next_sibling()->plaintext, false, false, 273808);
								}
							}
						}
					}

					foreach($dom->find('link[rel=canonical]') as $Link) {
						$job->setJobRouting(false, $Link->href, false);
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