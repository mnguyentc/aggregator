<?php

/**
 * Scraping agent for nationalevacaturebank.nl.
 */
class AgentNationalevacaturebankNl extends Agent {

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
				if ($dom->find('div#vacature-details')){

					//Get foreign id
					foreach($dom->find('var#vacature-id') as $Link) {
						$job->setForeignId('nationalevacaturebank_nl_' . trim($Link->plaintext));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div#vacature-details') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.header') as $ret7) {
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

					if (count($dom->find('title')) < 1) {
						$job->addJobLocation("Netherlands", false, false, 273772);
					} else {
						foreach ($dom->find('title') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Netherlands", false, false, 273772);
							} else {
								$locate = explode ('|', $LOCATION->plaintext);
								$job->addJobLocation(trim($locate[1]), false, false, 273772);
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