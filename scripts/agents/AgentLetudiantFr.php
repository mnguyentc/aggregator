<?php

/**
 * Scraping agent for letudiant.fr.
 */
class AgentLetudiantFr extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('div.lieu_annonce')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.showjob')){

					//Get foreign id
					foreach($dom->find('h2') as $id) {
						$str = substr(strrchr($id->plaintext, 'f.'), 1);
						$str2 = preg_replace("/[^0-9]/", "", $str);
						$job->setForeignId('letudiant_fr_' . $str2);
					}

	    			//Get job title
					foreach($dom->find('h1') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div.showjob') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('ol') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.heading') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.social') as $ret5) {
							$ret5->innertext = '';
							$ret5->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.prevNextLinksTop') as $ret6) {
							$ret6->innertext = '';
							$ret6->outertext = '';
						}

							foreach ($DESCRIPTION->find('h1') as $ret7) {
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
					
					
					if (count($dom->find('div.lieu_annonce')) < 1) {
						$job->addJobLocation("France", false, false, 273682);
					} else {
						foreach ($dom->find('div.lieu_annonce') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
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
						$job->setJobRouting(false, "http://".trim(implode($output[1])), 2);
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