<?php

/**
 * Scraping agent for vacature.com.
 */
class AgentVacatureCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('var#vacature-id')) {
			if ($dom->find('title')) {
				if ($dom->find('div#vacature-detail-view')){

					//Get foreign id
					foreach($dom->find('var#vacature-id') as $id) {
						$job->setForeignId('vacature_com_' . trim($id->plaintext));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$title = explode('|', $Jobtitle->plaintext);
						$job->setJobTitle(trim($title[0]));
					}

					//Get job description
					foreach($dom->find('div#vacature-detail-view') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('style') as $ret4) {
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

					
					if (count($dom->find('span.nobr')) < 1) {
						$job->addJobLocation("Belgium", false, false, 273629);
					} else {
						foreach($dom->find('span.nobr') as $LOCATION) {		
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Belgium", false, false, 273629);	
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273629);	
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