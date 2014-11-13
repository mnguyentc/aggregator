<?php

/**
 * Scraping agent for jobs-bank.com.
 */
class AgentJobsbankCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('a[title=Twitter]')) {
			if ($dom->find('input[name=jobtitle]')) {
				if ($dom->find('table.boxborder')){

					//Get foreign id

					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$str = substr(strrchr(implode($output[1]), '='), 1);
						$job->setForeignId('jobsbank_com_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('tr') as $DESCRIPTION) {
						if (trim($DESCRIPTION->children(0)->plaintext) == "Job Location") {
							$desc = $DESCRIPTION->next_sibling()->next_sibling()->innertext;
							$job->setDescription($desc);
						}	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('td.txtsmallblack')) < 1) {
						$job->addJobLocation("India", false, false, 273712);
					} else {
						foreach($dom->find('td.txtsmallblack') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Job Location") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("India", false, false, 273712);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273712);
								}	
							}
						}
					}

					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, "http://".implode($output[1]), 2);
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