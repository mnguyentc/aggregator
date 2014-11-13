<?php

/**
 * Scraping agent for job.ru.
 */
class AgentJobRu extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('h1[data-id]')) {
			if ($dom->find('title')) {
				if ($dom->find('div.vacancy-description')){

					//Get foreign id
					foreach($dom->find('h1[data-id]') as $id) {
						$job->setForeignId('job_ru_' . trim($id->getAttribute('data-id')));
					}

	    			//Get job title
					foreach($dom->find('h1[data-id]') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div.vacancy-description') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
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

					
					if (count($dom->find('div.address')) < 1) {
						$job->addJobLocation("Russia", false, false, 273797);
					} else {
						foreach ($dom->find('div.address') as $LOCATION) {
							foreach ($LOCATION->find('a') as $ret) {
								$ret->innertext = '';
								$ret->outertext = '';
							}

							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Russia", false, false, 273797);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273797);
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
}