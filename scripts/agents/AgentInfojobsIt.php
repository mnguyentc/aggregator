<?php

/**
 * Scraping agent for infojobs.it.
 */
class AgentInfojobsIt extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('meta[name=Canonical]')) {
			if ($dom->find('meta[name=og:title]')) {
				if ($dom->find('div.main-content')){

					//Get foreign id
					foreach($dom->find('meta[name=Canonical]') as $id) {
						$str = substr(strrchr($id->content, '-'), 1);
						$job->setForeignId('infojobs_it_' . trim($str));
					}

	    			//Get job title
					foreach($dom->find('meta[name=og:title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->content);
					}

					//Get job description
					foreach($dom->find('div.main-content') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a#link_mostrar_mas_info') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret1) {
							$ret1 = strip_tags($ret1);
						}

						foreach ($DESCRIPTION->find('table.info-proceso') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('h1') as $ret6) {
							$ret6->innertext = '';
							$ret6->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

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


					if (count($dom->find('td#prefijoPoblacion')) < 1) {
						$job->addJobLocation("Italia", false, false, 273717);
					} else {
						foreach ($dom->find('td#prefijoPoblacion') as $LOCATION) {
							if (strlen(trim($LOCATION->plaintext)) === 0) {
								$job->addJobLocation("Italia", false, false, 273717);
							} else {
								$job->addJobLocation(trim($LOCATION->plaintext), false, false, 273717);
							}
						}
					}

					foreach($dom->find('meta[name=Canonical]') as $Link) {
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