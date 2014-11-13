<?php

/**
 * Scraping agent for cvlibrary.co.uk.
 */
class AgentCvlibraryCoUk extends Agent {

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
			if ($dom->find('div#job-id')) {
				if ($dom->find('div.jobDescriptionMain')) {

						//Get foreign id
						foreach($dom->find('div#job-id') as $id) {
							$job->setForeignId('cvlibrary_co_uk_' . trim($id->plaintext));
						}

	    				//Get job title
						foreach($dom->find('title') as $Jobtitle) {
							$str = strtok($Jobtitle->plaintext, '-');
							$job->setJobTitle(trim(strip_tags($str)));
						}

						//Get job description
						foreach($dom->find('div.jobDescriptionMain') as $DESCRIPTION) {
							foreach ($DESCRIPTION->find('script') as $ret) {
								$ret->innertext = '';
								$ret->outertext = '';
							}

							foreach ($DESCRIPTION->find('a') as $ret2) {
								$ret2->innertext = '';
								$ret2->outertext = '';
							}

							foreach ($DESCRIPTION->find('div.jobDescriptionDetailsApply') as $ret3) {
								$ret3->innertext = '';
								$ret3->outertext = '';
							}

							foreach ($DESCRIPTION->find('div.jobDescriptionCriteriaShare') as $ret4) {
								$ret4->innertext = '';
								$ret4->outertext = '';
							}

							foreach ($DESCRIPTION->find('div.jobDescriptionDetailsShare') as $ret5) {
								$ret5->innertext = '';
								$ret5->outertext = '';
							}

							foreach ($DESCRIPTION->find('div.jobDescriptionLinks') as $ret6) {
								$ret6->innertext = '';
								$ret6->outertext = '';
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

					$job->addJobLocation("UK", false, false, 273684);

					foreach($dom->find('comment') as $Link) {
					$startString="from ";
					$endString=" by HTTrack";
					preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
					$job->setJobRouting(false, 'http://'.implode($output[1]), 2);
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