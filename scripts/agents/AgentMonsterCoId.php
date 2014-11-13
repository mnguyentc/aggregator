<?php

/**
 * Scraping agent for monster.co.id.
 */
class AgentMonsterCoId extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('meta[property=og:url]')) {
			if ($dom->find('div[class=ns_jd_headingbig]')) {
				if ($dom->find('div.ns_fulljd_wrap')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $Link) {
						$str = substr(strrchr($Link->content, '/'), 1);
						$str2 = explode('.', $str);
						$job->setForeignId('monster_co_id_' . trim($str2[0]));
					}

	    			//Get job title
					foreach($dom->find('div[class=ns_jd_headingbig hl]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div.ns_fulljd_wrap') as $DESCRIPTION) {
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

						$desc1 = str_replace("<div","<p",$DESCRIPTION->innertext);
   						$desc2 = str_replace("div>","p>",$desc1);

						$job->setDescription($desc2);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					
					if (count($dom->find('div[class=ns_jobsum_small_heading]')) < 1) {
						$job->addJobLocation("Indonesia", false, false, 273708);
					} else {
						foreach ($dom->find('div[class=ns_jobsum_small_heading]') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Locations") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("Indonesia", false, false, 273708);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273708);
								}						
							}
						}
					}
					
					foreach($dom->find('meta[property=og:url]') as $Link) {
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