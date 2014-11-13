<?php

/**
 * Scraping agent for cvkeskus.ee.
 */
class AgentCvkeskusEe extends Agent {

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
			if ($dom->find('div#online_job_edit_10')) {
				if ($dom->find('table#jobad-content-table')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $Link) {
						$str = substr(strrchr($Link->content, '/'), 1);
						$job->setForeignId('cvkeskus_ee_' . $str);
					}

	    			//Get job title
					foreach($dom->find('div#online_job_edit_10') as $Jobtitle) {
						$job->setJobTitle($Jobtitle->plaintext);
					}

					//Get job description
					foreach($dom->find('table#jobad-content-table') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('td.light-red-bottom') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('td#job-title') as $ret6) {
							$ret6->innertext = '';
							$ret6->outertext = '';
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

					$job->addJobLocation("Estonia", false, false, 273670);

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