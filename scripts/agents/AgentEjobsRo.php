<?php

/**
 * Scraping agent for ejobs.ro.
 */
class AgentEjobsRo extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('div.job-wrapper')) {
			if ($dom->find('h1[itemprop=title]')) {
				if ($dom->find('div.job-content')){

					//Get foreign id
					foreach($dom->find('div.job-wrapper') as $Link) {
						$job->setForeignId('ejobs_ro_' . $Link->job);
					}

	    			//Get job title
					foreach($dom->find('h1[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div.job-content') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret1) {
							$ret1->innertext = '';
							$ret1->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('ul[itemprop=addressLocality] li')) < 1) {
						$job->addJobLocation("Romania", false, false, 273795);
					} else {
						foreach ($dom->find('ul[itemprop=addressLocality] li') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Romania", false, false, 273795);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273795);
							}	
						}
					}

					foreach($dom->find('link[rel=canonical]') as $Link) {
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