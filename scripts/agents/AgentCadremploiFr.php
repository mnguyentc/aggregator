<?php

/**
 * Scraping agent for cadremploi.fr.
 */
class AgentCadremploiFr extends Agent {

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
			if ($dom->find('h1.job-offer__title')) {
				if ($dom->find('main.job-offer')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $id) {					
						$str = substr(strrchr($id->content, '='), 1);
						$job->setForeignId('cadremploi_fr_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1.job-offer__title span.job-offer__position') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description

					foreach($dom->find('div[itemprop=description]') as $DESCRIPTION){
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.job-offer__desc--submit') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}
						
						$job->setDescription($DESCRIPTION->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('a#js-offres-localisation')) < 1) {
						$job->addJobLocation("France", false, false, 273682);
					} else {
						foreach($dom->find('a#js-offres-localisation') as $LOCATION) {						 
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("France", false, false, 273682);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273682);	
							}									
						}
					}

					foreach($dom->find('meta[property=og:url]') as $Link) {
						$job->setJobRouting(false, $Link->content, 2);
					}
					
					}

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
