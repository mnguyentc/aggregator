<?php

/**
 * Scraping agent for linkedin.com.
 */
class AgentLinkedinCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('link[rel=canonical]')) {
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div.description-module')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '/'), 1);
						$job->setForeignId('linkedin_com_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.description-module div.content') as $DESCRIPTION) {
						
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret3) {
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


					if (count($dom->find('span[itemprop=jobLocation]')) < 1) {
						$job->addJobLocation("USA", false, false, 273837);
					} else {
						foreach ($dom->find('span[itemprop=jobLocation]') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("USA", false, false, 273837);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273837);
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