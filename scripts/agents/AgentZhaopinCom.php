<?php

/**
 * Scraping agent for zhaopin.com.
 */
class AgentZhaopinCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('comment')) {
			if ($dom->find('h1')) {
				if ($dom->find('div.terminalpage-main')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="com/";
						$endString=".htm";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setForeignId('zhaopin_com_' . trim(implode($output[1])));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.terminalpage-main div.tab-cont-box') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('button') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('ul.tab-ul') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						foreach ($DESCRIPTION->find('p.collect') as $ret5) {
							$ret5->innertext = '';
							$ret5->outertext = '';
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

					if (count($dom->find('span')) < 1) {
						$job->addJobLocation("China", false, false, 273655);
					} else {
						foreach ($dom->find('span') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "工作地点：") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("China", false, false, 273655);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273655);
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