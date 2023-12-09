# Web Crawler

## Setup Instructions

### Prerequisites

1. Web Server with PHP Installed
2. MySQL Database

### Steps to Follow for Execution:

1. **Clone the Repository**

   - URL: https://github.com/am-i-abdullah/web_crawler.git
   

2. **Setting Up Database**

   - Import the file named "dbsearch.sql" into your MySQL database. This file sets up the required tables.

3. **Connect to the Database**

   - Go to config/connection.php and update the connection details (hostname, username, password, database).

4. **Adjusting File Paths**

   - If your file structure is different, update the file paths in the PHP files accordingly.

5. **Configuring Permissions**

   - Ensure the web server can read and write files for storing JSON data.

6. **Run the Application**

   - Run sql script in sql/webcrawler.sql in you mysql database
   - Change connection settings from config/connection.php
   - Type url localhost/webcrawler in your browser and crawler will be working.


### Using Crawler

1. Enter the URL and depth in the search form.
2. Click the "Search" button.
3. Once the crawling process is finished, you'll be redirected to a page where you can search for specific keywords in the crawled data.
4. All crawled data will be stored in the "jsondata" folder, with each unique URL having its own JSON file.


