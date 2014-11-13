<?php

/**
 * Scraping agent for mol.fi.
 */
require_once APP_PATH . '/lib/ScraperLib.php';

class AgentMolFi extends Agent {

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

		if ($dom->find('span[id=ilmoitusnumero]')) {
			if ($dom->find('h3')) {

				//Get foreign ID
				foreach($dom->find('span[id=ilmoitusnumero]') as $FOREIGN_ID) {
					$job->setForeignId('mol_fi_' . trim($FOREIGN_ID->plaintext));
				}	

				//Get job title
				foreach($dom->find('h3') as $this->jobtitle) {
					$job->setJobTitle($this->jobtitle->plaintext);
				}

				//Get job description
				foreach($dom->find('h3') as $DESCRIPTION) {
					$job->setDescription($DESCRIPTION->next_sibling()->innertext);
				}

				foreach($dom->find('span[id=hakuPaattyy]') as $EXPIRE_DATE) {
					$parts = explode(' klo ', $EXPIRE_DATE->plaintext);
					$outputDate = date('Y-m-d', strtotime($parts[0]));
					if (isset($parts[1])) {
						$outputDate = "{$outputDate}T{$parts[1]}:00Z";
					}
					else {
						$outputDate = "{$outputDate}T00:00:00Z";
					}

					$job->setExpireDate($outputDate);

				}

				//Get job location
				$location = "Finland";
				$regionId = 273677;
				
				if (count($dom->find('span[id=tyopaikanOsoite]')) >= 1) {
					foreach ($dom->find('span[id=tyopaikanOsoite]') as $LOCATION) {							
						if (strlen(trim($LOCATION->plaintext)) !== 0) {
							$str = substr(strrchr(trim($LOCATION->plaintext), " "), 1);
							$location = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
						}

						if ($location !== "Finland") {
							$key = ScraperLib::searchForId($location, 'Finland', $citylist);
							if (strlen($key) === 0) {
								$regionId = 273677;
							} else {
								$regionId = $key;
							}
						}							
					}
				}
			
				$job->addJobLocation($location, false, false, $regionId);		

				foreach($dom->find('comment') as $Link) {
					$startString="from ";
					$endString=" by HTTrack";
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