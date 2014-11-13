<?php

/**
 * Scraping agent for snagajob.com.
 */
class AgentSnagajobCom extends Agent {

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
			if ($dom->find('h1[itemprop=description]')) {
				if ($dom->find('section.jobDescription')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '='), 1);
						$job->setForeignId('snagajob_com_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=description]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('section.jobDescription') as $DESCRIPTION) {
						
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

					if (count($dom->find('span[itemprop=address]')) < 1) {
						$job->addJobLocation("USA", false, false, 273837);
					} else {
						foreach ($dom->find('span[itemprop=address]') as $LOCATION) {
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