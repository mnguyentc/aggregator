<?php

/**
 * Scraping agent for keljob.com.
 */
class AgentKeljobCom extends Agent {

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
			if ($dom->find('h1')) {
				if ($dom->find('div[id=jobs_detail]')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '-'), 1);
						$job->setForeignId('keljob_com_' . trim($str));
					}

					//Get job title
					foreach($dom->find('h1') as $this->Jobtitle) {
						
						$job->setJobTitle($this->Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('div[id=jobs_detail]') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('a') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('h2') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('p.postule') as $ret4) {
							$ret4->innertext = '';
							$ret4->outertext = '';
						}

						foreach ($DESCRIPTION->find('img') as $ret5) {
							$ret5->innertext = '';
							$ret5->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);	
					}

					//Get location
					if (count($dom->find('table.mad td.label')) < 1) {
						$job->addJobLocation("France", false, false, 273682);
					} else {
						foreach($dom->find('table.mad td.label') as $LOCATION) {
							if (trim($LOCATION->plaintext) == "Localisation :") {
								if (strlen(trim($LOCATION->next_sibling()->plaintext)) === 0) {
									$job->addJobLocation("France", false, false, 273682);
								} else {
									$job->addJobLocation(trim($LOCATION->next_sibling()->plaintext), false, false, 273682);
								}						
							}
						}
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					$job->setJobRouting(false, $id->href, 2);
				

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
}