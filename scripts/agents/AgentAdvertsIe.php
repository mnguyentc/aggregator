<?php
/**
 * Scraping agent for adverts.ie.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentAdvertsIe extends Agent {

	/**
	 * Scrapes job data from the provided html dom object.
	 *
	 * @param simple_html_dom $dom Simple html dom object.
	 *
	 * @return array Job data in the form of an associative array.
	 */
	public function scrape($dom) {

		$job = new Job();
		$citylist = $GLOBALS['citylist'];

		if ($dom->find('link[rel=canonical]')) {
			if ($dom->find('span[itemprop=title]')) {
				if ($dom->find('div.description-box')){

					//Get foreign id
					foreach($dom->find('link[rel=canonical]') as $id) {
						$str = substr(strrchr($id->href, '/'), 1);
						$job->setForeignId('adverts_ie_' . trim($str));
					}

					//Get job title
					foreach($dom->find('span[itemprop=title]') as $Jobtitle) {
						$job->setJobTitle(trim($Jobtitle->plaintext));
					}
/*
					//Get company name
					foreach($dom->find('dd[itemprop=hiringOrganization] a') as $Company) {
						foreach ($Company->find('span') as $ret1) {
							$ret1->innertext = '';
							$ret1->outertext = '';
						}
						
						$job->setCompanyId(trim($Company->plaintext));
					}*/

					//Get job description
					foreach($dom->find('div.description-box') as $DESCRIPTION) {
						foreach ($DESCRIPTION->find('img') as $ret) {
							$ret->innertext = '';
							$ret->outertext = '';
						}

						foreach ($DESCRIPTION->find('script') as $ret2) {
							$ret2->innertext = '';
							$ret2->outertext = '';
						}

						$job->setDescription($DESCRIPTION->innertext);
					}

					$date = date('Y-m-d');// current date
					$date = strtotime('+1 week', strtotime($date));
					$newdate = date ( 'Y-m-j' , $date);

					$outputDate = $newdate."T".date("h:i").":00Z";
					$job->setExpireDate($outputDate);

					//Get job location
					$location = "Ireland";
					$regionId = 273709;
					
					if (count($dom->find('dd[itemprop=jobLocation]')) >= 1) {
						foreach ($dom->find('dd[itemprop=jobLocation]') as $LOCATION) {							
							if (strlen(trim($LOCATION->plaintext)) !== 0) {
								$str = current(explode(" ", trim($LOCATION->plaintext)));
								if (strpos($str,'Co.') !== false) {
									$str2 = substr(strrchr($str, '.'), 1);
									$location = rtrim($str2,',');							   
								} else {
									 $location = rtrim($str,',');
								}
								print($location."\n");
							}

							if ($location !== "Ireland") {
								$key = ScraperLib::searchForId($location, 'Ireland', $citylist);
								if (strlen($key) === 0) {
									$regionId = 273709;
								} else {
									$regionId = $key;
								}
							}							
						}
					}
				
					$job->addJobLocation($location, false, false, $regionId);
						
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