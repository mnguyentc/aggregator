<?php

/**
 * Agent - base class for all scraping agents.
 */
abstract class Agent {

    /**
     * Scrapes job data from the provided html dom object. All agents
     * must override this method.
     *
     * @param simple_html_dom $dom Simple html dom object.
     *
     * @return array Job data in the form of an associative array.
     */
    abstract public function scrape($dom);
}
