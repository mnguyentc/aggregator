# Job Aggregator with ActiveMQ

Job Aggregator consists of a series of command line scripts that has the ability to crawl job boards, scrape relevant data from the collected files
and push that data into the main system. The scripts are tailored to run in a Linux environment. Ubuntu 14.04 was used during development,
both on the development machine and on the staging server. The runnable scripts are located in the "scripts" folder. The main ones are crawler.php,
scraper.php and pusher.php. Run the scripts with the "--help" switch to get usage information.

# The main scripts

## crawler.php

The crawler is basically a wrapper around the open source crawler "httrack". There are three supported operations: "run", "list" and "kill". Use "run" to 
launch httrack instances in the background, "list" to enumerate the running processes and "kill" to terminate them.

In order to crawl a job board crawler.php needs a httrack command to run. These httrack commands are specified in site configuration files located under
scripts/config/crawler/sites-enabled. The site configurations are named {site name}.json.

Since the httrack instances are launched in the background the crawler has no way of tracking their progress or know when they are done.

Usage examples:

Show help:

	./crawler.php --help

Launch httrack instances for all enabled sites:

	./crawler.php run

Launch httrack instance for mol.fi:

	./crawler.php run -s mol.fi

List sites that are being crawled currently:

	./crawler.php list

Kill httrack instance for mol.fi:

	./crawler.php kill -s mol.fi

Kill all httrack instances without confirmation:

	./crawler.php kill -y

Note that the kill command sometimes needs to be run multiple times to ensure that all httrack instances are killed. Httrack doesn't shut down immediately
when receiving the signal. It performs some cleanup tasks like renaming of temporary files.

After the crawler has been there will a number of html files in it's output folder. These files need to be scraped for data, which brings us to the next item.

## scraper.php

The scraper reads the html files fetched by the crawler and scrapes them for job data. Every scraped job is placed in an individual json file with the name {foreign id}.json. In order to scrape different page structures each job site requires a "scraping agent". Scraping agents are small scripts invoked by the scraper with the single purpose of extracting job data from html files. The html is parsed using the Simple HTML DOM library.

Usage examples:

Show help:

	./scraper.php --help

Scrape job data for mol.fi. Assumes that all html files under /tmp/crawler_output/ are fetched from mol.fi:

	./scraper.php -i /tmp/crawler_output/ -o /tmp/scraper_output/ -s mol.fi

Scrape job data from multiple sites. Assumes that each subdirectory under /tmp/crawler_output/ represents a site. In other words, the names of the subdirectories determines which scraping agents are loaded and executed:

	./scraper.php -i /tmp/crawler_output/ -o /tmp/scraper_output/ --subdirs-are-sites

## pusher.php

The pusher reads the job data from each json file and pushes it into the ActiveMQ message queue.

Usage examples:

Show help:

	./pusher.php --help

Push all jobs:

	./pusher.php -i /tmp/scraper_output/

Push a maximum of 10 jobs:

	./pusher.php -i /tmp/scraper_output/ -l 10

Push all jobs and delete eache json file once it has been pushed:

	./pusher.php -i /tmp/scraper_output/ --delete-after-push


# Additional scripts

## generate-agent.php

Generates a skeleton for a new scraping agent. Note that the generated script doesn't continue any scraping logic. That must be added by a developer.

Usage examples:

Show help:

	./generate-agent.php --help

Generate agent for monster.se:

	./generate-agent -s monster.se

## report.php

Shows a summary of crawler and scraper output data. Information such as amount of data and number of files.

Usage examples:

Show summary of crawler output:

	./report.php -c /tmp/crawler_output/

Show summary of scraper output:

	./report.php -s /tmp/scraper_output/

Show summary of both crawler and scraper output:

	./report.php -c /tmp/crawler_output/ -s /tmp/scraper_output/

# Installation

The scripts can either be run manually or scheduled to run through something like cron.

