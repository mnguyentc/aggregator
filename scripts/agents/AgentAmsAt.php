<?php

/**
 * Scraping agent for ams.at.
 */
class AgentAmsAt extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('td.fld')) {
			if ($dom->find('td.tdsubhead')) {
				if ($dom->find('td.tdcontent')){

					//Get foreign id
					foreach($dom->find('td.tdcontent') as $Link) {						
						$str = substr(strrchr($Link->plaintext, ':'), 1);
						$job->setForeignId('ams_at_'.trim($str));
					}

	    			//Get job title
					foreach($dom->find('td.tdsubhead') as $Jobtitle) {
						if ($Jobtitle->plaintext == "Berufsgruppe:") {
							$job->setJobTitle($Jobtitle->next_sibling()->plaintext);
						}
					}

					//Get job description
					foreach($dom->find('td.tdcontent') as $DESCRIPTION) {
						
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						$job->setDescription(utf8_encode($DESCRIPTION->innertext));
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);


					if (count($dom->find('td.tdsubhead')) < 1) {
						$job->addJobLocation("Austria", false, false, 273621);
					} else {
						foreach ($dom->find('td.tdsubhead') as $LOCATION) {
							if ($LOCATION->plaintext == "Arbeitsort:") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("Austria", false, false, 273621);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273621);
								}
							}
						}
					}

					$job->setJobRouting(false, "https://jobroom.ams.or.at/jobsuche/FreieSuche.jsp", 2);

					return $job;
				}
				else {
					return false;
				}
			}
		}
	}
}