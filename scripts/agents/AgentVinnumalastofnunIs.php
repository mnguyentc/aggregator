<?php

/**
 * Scraping agent for vinnumalastofnun.is.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentVinnumalastofnunIs extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();
		$citylist = $GLOBALS['citylist'];

		if ($dom->find('comment')) {
			if ($dom->find('h2.nafn')) {
				if ($dom->find('div.box-left')){

					//Get foreign id
					foreach($dom->find('comment') as $Link) {
						$startString="nr/";
						$endString="/";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setForeignId('vinnumalastofnun_is_' . trim(implode($output[1])));
					}

	    			//Get job title
					foreach($dom->find('h2.nafn') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.box-left') as $DESCRIPTION) {
						$str = htmlentities($DESCRIPTION->innertext,ENT_NOQUOTES,'UTF-8',false);
						$str = str_replace(array('&lt;','&gt;'),array('<','>'), $str);
						$str = str_replace(array('&amp;lt;','&amp;gt'),array('&lt;','&gt;'), $str);	
						$job->setDescription($str);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					$job->addJobLocation("Iceland", false, false, 273716);

					foreach($dom->find('link[rel=canonical]') as $Link) {
						$job->setJobRouting(false, $Link->href, false);
					}

					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString=" by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, "http://".trim(implode($output[1])), 2);
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