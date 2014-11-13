<?php

/**
 * Scraping agent for hh.ru.
 */
class AgentHhRu extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('meta[itemprop=url]')) {
			if ($dom->find('h1.b-vacancy-title')) {
				if ($dom->find('div#hypercontext')){

					//Get foreign id
					foreach($dom->find('meta[itemprop=url]') as $id) {
						$str = substr(strrchr($id->content, '/'), 1);
						$job->setForeignId('hh_ru_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1.b-vacancy-title') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div#hypercontext') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						foreach ($DESCRIPTION->find('span.g-switcher HHMaps-ShowAddress-ShowOnMap') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('div.HHMaps-ShowAddress-Address')) < 1) {
						$job->addJobLocation("Russia", false, false, 273797);
					} else {
						foreach ($dom->find('div.HHMaps-ShowAddress-Address') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Russia", false, false, 273797);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273797);
							}
						}
					)

					foreach($dom->find('meta[itemprop=url]') as $Link) {
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
}