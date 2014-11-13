<?php

/**
 * Scraping agent for {site-name}.
 */
class {
    class-name} extends Agent {

    /**
     * Scrapes job data from the provided html dom object.
     *
     * @param simple_html_dom $dom Simple html dom object.
     *
     * @return Job Instance of Job class containing scraped job data.
     */
    public function scrape($dom) {

        $job = new Job();

        // TODO: Populate $job object with scraped data.

        return $job;
    }

}

