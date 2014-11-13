<?php

/**
 * Scraping agent for superjob.ru.
 */
class AgentSuperjobRu extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('div.VacancyView_number')) {
			if ($dom->find('h1.VacancyView_title')) {
				if ($dom->find('div.VacancyView_main')){

					//Get foreign id
					foreach($dom->find('div.VacancyView_number span.h_nowrap') as $id) {
						$job->setForeignId('superjob_ru_' . trim($id->plaintext, "№ "));
					}

	    			//Get job title
					foreach($dom->find('h1.VacancyView_title') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div.VacancyView_main div.VacancyView_details') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						$desc1 = str_replace("<div","<p",$DESCRIPTION->innertext);
 						$desc2 = str_replace("div>","p>",$desc1);
						$job->setDescription($desc2);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);
					
					if (count($dom->find('span.VacancyView_town')) < 1) {
						$job->addJobLocation("Russia", false, false, 273797);
					} else {
						foreach ($dom->find('span.VacancyView_town') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Russia", false, false, 273797);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273797);
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