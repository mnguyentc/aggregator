<?php

/**
 * Scraping agent for monster.com.hk.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMonsterComHk extends Agent {

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

		if ($dom->find('meta[property=og:url]')) {
			if ($dom->find('title')) {
				if ($dom->find('div.ns_fulljd_wrap')){

					//Get foreign id
					foreach($dom->find('meta[property=og:url]') as $id) {
						$str2 = substr($id->content, 0, -5);
						$job->setForeignId('monster_com_hk_' . trim($str2));
					}

	    			//Get job title
					foreach($dom->find('title') as $this->Jobtitle) {
						$str = explode('-', $this->Jobtitle->plaintext);
						$job->setJobTitle($str[1]);
					}

					//Get job description

					foreach($dom->find('div.ns_fulljd_wrap') as $DESCRIPTION){
						foreach ($DESCRIPTION->find('script') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('style[media=screen]') as $ret1) {
							$ret1->innertext = '';
							$ret1->outertext = '';
						}

						$desc1 = str_replace("<span","<p",$DESCRIPTION->innertext);
						$desc2 = str_replace("span>","p>",$desc1);

						$job->setDescription($desc2);	
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);
					
					$job->addJobLocation("Hong Kong", false, false, 273702);
				
					$job->setJobRouting(false, $id->content, 2);
					

					return $job;
			}
			else {
				return false;
			}
		}
	}
}
}