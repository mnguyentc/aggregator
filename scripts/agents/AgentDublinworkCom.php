<?php
/**
 * Scraping agent for dublinwork.com.
 */
class AgentDublinworkCom extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return array Job data in the form of an associative array.
	 */
	public function scrape($dom) {

		$job = new Job();

		if ($dom->find('input[name=idJobOffer]')) {
			if ($dom->find('div.title')) {
				if ($dom->find('div.content')){

					//Get foreign id
					foreach($dom->find('input[name=idJobOffer]') as $id) {
						$job->setForeignId('dublinwork_com_' . trim($id->value));
					}

					//Get job title
					foreach($dom->find('div.title') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}
/*
					//Get company name
					foreach($dom->find('h1.titleB') as $Company) {						
						$job->setCompanyId(trim($Company->next_sibling()->next_sibling()->plaintext));
					}*/

					//Get job description
					foreach($dom->find('div.content') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('img') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('script') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						foreach ($DESCRIPTION->find('form') as $ret3) {
							$ret3->innertext = '';
							$ret3->outertext = '';
						}

						foreach ($DESCRIPTION->find('ins') as $ret4) {
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

					//Get job location
					$job->addJobLocation("Dublin", false, false, 20346);
						
					foreach($dom->find('comment') as $Link) {
						$startString="from ";
						$endString="by HTTrack";
						preg_match_all ("|$startString(.*)$endString|U", $Link, $output, PREG_PATTERN_ORDER);
						$job->setJobRouting(false, 'http://'.implode($output[1]), 2);
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