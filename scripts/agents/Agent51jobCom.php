<?php

/**
 * Scraping agent for 51job.com.
 */
class Agent51jobCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input#search_job_redirect_url')) {
			if ($dom->find('title')) {
				if ($dom->find('td.job_detail')){

					//Get foreign id
					foreach($dom->find('input#search_job_redirect_url') as $id) {
						$startString="job/";
						$endString=",";
						preg_match_all ("|$startString(.*)$endString|U", $id->value, $output, PREG_PATTERN_ORDER);
						$job->setForeignId('51job_com_' . trim(implode($output[1])));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('td[class=txt_4 wordBreakNormal job_detail]') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
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

					$job->addJobLocation("China", false, false, 273655);

					foreach($dom->find('input#search_job_redirect_url') as $Link) {
						$job->setJobRouting(false, $Link->value, 2);
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