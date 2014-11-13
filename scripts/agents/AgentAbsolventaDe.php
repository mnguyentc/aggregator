<?php

/**
 * Scraping agent for absolventa.de.
 */
class AgentAbsolventaDe extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('meta[property=og:url]')) {
			if ($dom->find('div#job_offer-header')) {
				if ($dom->find('div#iframe')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $id) {
						$str = substr(strrchr($id->content, '/'), 1);
						$str2 = explode('-', $str);
						$job->setForeignId('absolventa_de_' . trim($str2[0]));
					}

	    			//Get job title
					foreach($dom->find('div#job_offer-header') as $this->Jobtitle) {
						foreach ($this->Jobtitle->find('div.text-center') as $TITLE) {
							
							$job->setJobTitle($TITLE->plaintext);
						}
					}

					//Get job description

					foreach($dom->find('div#job_offer-text') as $DESCRIPTION){
						foreach ($DESCRIPTION->find('h2') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}
						$job->setDescription($DESCRIPTION->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('h4')) < 1) {
						$job->addJobLocation("Germany", false, false, 273663);
					} else {
						foreach($dom->find('h4') as $LOCATION){
							if (strpos($LOCATION->plaintext,'Standort') !== false) {
								if (strlen(trim($LOCATION->parent()->plaintext)) === 0) {
									$job->addJobLocation("Germany", false, false, 273663);
								} else {
									$job->addJobLocation(trim($LOCATION->parent()->plaintext), false, false, 273663);
								}	
							}	
						}
					}
					
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