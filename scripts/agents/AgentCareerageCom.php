<?php

/**
 * Scraping agent for careerage.com.
 */
class AgentCareerageCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return Job Instance of Job class containing scraped job data.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input[name=ad]')) {
			if ($dom->find('meta[name=title]')) {
				if ($dom->find('input[value=Apply to this Job]')){

					//Get foreign id

					foreach($dom->find('input[name=ad]') as $id) {
						$str = substr(strrchr($id->value, '/'), 1);
						$str2 = substr($str,0,strrpos($str,'.'));
						$job->setForeignId('careerage_com_' . trim($str2));
					}

	    			//Get job title
					foreach($dom->find('title') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}

					//Get job description
					foreach($dom->find('div.yui-g') as $DESCRIPTION) {	
						$desc = $DESCRIPTION->children(2)->children(1)->innertext;
						$desc1 = str_replace("<div","<p",$desc);
   						$desc2 = str_replace("div>","p>",$desc1);
   						$filtered_data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $desc2);
						$job->setDescription($filtered_data);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					$job->addJobLocation("India", false, false, 273712);

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