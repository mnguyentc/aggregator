<?php

/**
 * Scraping agent for fish4.co.uk.
 */
class AgentFish4CoUk extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return array Job data in the form of an associative array.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('meta[property=og:url]')) {
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div[itemprop=description]')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $id) {
						$str = explode('/', $id->content);
						$job->setForeignId('fish4_co_uk_' . trim($str[4]));
					}

					//Get job title
					foreach($dom->find('h1[itemprop=title]') as $this->Jobtitle) {
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[itemprop=description]') as $DESCRIPTION) {
						$job->setDescription($DESCRIPTION->innertext);	
					}

					if (count($dom->find('span[itemprop=hiringOrganization]')) < 1) {
						$job->addJobLocation("UK", false, false, 273684);
					} else {
						foreach($dom->find('span[itemprop=hiringOrganization]') as $LOCATION) {
							if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0){
								$job->addJobLocation("UK", false, false, 273684);
							} else {
								$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273684);
							}	
						}
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					$job->setJobRouting(false, $id->content, 2);

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
}