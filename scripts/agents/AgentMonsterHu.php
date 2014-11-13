<?php

/**
 * Scraping agent for monster.hu.
 */
class AgentMonsterHu extends Agent {

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
			if ($dom->find('meta[itemprop=title]')) {
				if ($dom->find('div#monsterAppliesContentHolder')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '-'), 1);
						$str2 = explode('.', $str);
						$job->setForeignId('monster_hu_' . trim($str2[0]));
					}

	    			//Get job title
					foreach($dom->find('meta[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->content);
					}

					//Get job description
					foreach($dom->find('div#monsterAppliesContentHolder') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#ejb_sendJob') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#jobheader') as $ret5) {
							$ret5->innertext = '';
							$ret5->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#job_summary') as $ret6) {
							$ret6->innertext = '';
							$ret6->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret7) {
							$ret7->innertext = '';
							$ret7->outertext = '';
						}

						foreach ($DESCRIPTION->find('div#sidecol') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
						
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);


					if (count($dom->find('input#jobLocation')) < 1) {
						$job->addJobLocation("Hungary", false, false, 273707);
					} else {
						foreach ($dom->find('input#jobLocation') as $LOCATION) {
							if (strlen(trim($LOCATION->value)) === 0) {
								$job->addJobLocation("Hungary", false, false, 273707);
							} else {
								$job->addJobLocation($LOCATION->value, false, false, 273707);
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