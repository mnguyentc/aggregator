<?php

/**
 * Scraping agent for cv.lv.
 */
class AgentCvLv extends Agent {

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
			if ($dom->find('meta[property=og:title]')) {
				if ($dom->find('table#jobad_table')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$str = substr(strrchr(implode($output[1]), '-'), 1);
						$str2 = explode('.', $str);
						$job->setForeignId('cv_lv_' . trim($str2[0]));
					}

	    			//Get job title
					foreach($dom->find('meta[property=og:title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->content);
					}

					//Get job description
					foreach($dom->find('table#jobad_table') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
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


					if (count($dom->find('span[itemprop=jobLocation]')) < 1) {
						$job->addJobLocation("Latvia", false, false, 273742);
					} else {
						foreach ($dom->find('span[itemprop=jobLocation]') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Latvia", false, false, 273742);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273742);	
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