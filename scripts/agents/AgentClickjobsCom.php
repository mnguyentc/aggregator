<?php

/**
 * Scraping agent for clickjobs.com.
 */
class AgentClickjobsCom extends Agent {

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
			if ($dom->find('title')) {
				if ($dom->find('p.j-lhd')){

					//Get foreign id

					foreach($dom->find('td') as $id) {
						if (trim($id->plaintext) == "Job Code:") {
							$job->setForeignId('clickjobs_com_' . trim($id->next_sibling()->plaintext));
						}
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[style=padding-left:18px;]') as $DESCRIPTION) {
						
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('style') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.inr-curve-new') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						foreach ($DESCRIPTION->find('div.lt') as $ret5) {
							$ret5->innertext = '';
							$ret5->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					$job->addJobLocation("India", false, false, 273712);

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