<?php
include_once("config/connection.php");
set_time_limit(2000);
$crawledUrls = [];
$resultsArray = [];
$maxDepth;
$parentUrl;

function search($url, $maxDep = 0) {
    global $maxDepth,$parentUrl,$crawledUrls,$resultsArray;
    $maxDepth = $maxDep;
    $parentUrl=$url;
    $crawledUrls = []; // Reset visited URLs for each search
    $resultsArray = []; // Reset search results for each search
    crawlUrl($parentUrl, 0);
    // Store the results to JSON after processing each URL
    $id=storeResultsToDB($parentUrl);
    return $id;
}

function crawlUrl($url, $depth) {
    global $maxDepth,$crawledUrls,$parentUrl;
    $url=resolveUrl($parentUrl,$url);
    if ($depth <= $maxDepth && !in_array($url, $crawledUrls) && robotCheck($url)) {
        $htmlContent = fetchHtmlContent($url);
        if ($htmlContent !== false) {
            parseHtmlContent($url, $htmlContent,$depth);
            $crawledUrls[] = $url; // Mark the URL as visited
        }
    }else{
        return;
    }
}

function fetchHtmlContent($url) {
    $curl = curl_init($url);

    // Set cURL options
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // Execute cURL session
    $htmlContent = curl_exec($curl);

    // test for errors during the cURL request
    if (curl_errno($curl)) {
        echo 'Error fetching URL: ' . curl_error($curl) . PHP_EOL;
        $htmlContent = false;
    }

    // Close cURL session
    curl_close($curl);
    return $htmlContent;
}

function generateUrl($urlParts) {
    $scheme   = isset($urlParts['scheme']) ? $urlParts['scheme'] . '://' : '';
    $host     = isset($urlParts['host']) ? $urlParts['host'] : '';
    $port     = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
    $path     = isset($urlParts['path']) ? $urlParts['path'] : '';    
    return $scheme . $host . $port . $path;
}

function robotCheck($url) {
    $robotsUrl = parse_url($url);
    $robotsUrl['path'] = '/robots.txt';
    $robotsTxtUrl = generateUrl($robotsUrl);   
    $robotsTxtContent = fetchHtmlContent($robotsTxtUrl);

    // Implement logic to test if $robotsTxtContent allows crawling of $url
    if ($robotsTxtContent !== false) {
        // Split the robots.txt content into lines
        $lines = explode("\n", $robotsTxtContent);

        // Loop through each line in the robots.txt file
        foreach ($lines as $line) {
            // Remove leading and trailing whitespaces
            $line = trim($line);

            // test for comments and ignore them
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            
            // test for Disallow directive if the user agent is applicable
            if (strpos($line, 'Disallow:') !== false) {
                // Extract the disallowed path
                $disallowedPath = trim(str_ireplace('Disallow:', '', $line));

                // test if the URL matches the disallowed path
                if (isset($robotsUrl['path']) && $robotsUrl['path'] == $disallowedPath) {
                    return false; // URL is disallowed by robots.txt
                }   
            }
        }
    }

    return true; // URL is allowed by robots.txt
}

function parseHtmlContent($baseUrl,$htmlContent,$depth) {
    global $resultsArray;
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($htmlContent);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $query = "//a | //p | //h1 | //h2 | //h3 | //h4 | //h5 | //h6 | //li";

    $results = $xpath->query($query);

    if ($results->length > 0) {
        $urlResults = ['url' => $baseUrl, 'data' => []];

        foreach ($results as $result) {
            if ($result->nodeName === 'a') {
                $href=$result->getAttribute("href");
                //crawl each url recursively till the given max depth
                crawlUrl($href,$depth+1);
            } else {
                $content = $result->nodeValue;
                $urlResults['data'][] = $content;
            }
        }

        $resultsArray[] = $urlResults;
    }
}

function storeResultsToDB($parentUrl) {
    global $resultsArray, $conn;
    if(!empty($resultsArray)){
        $jsonData = json_encode($resultsArray, JSON_PRETTY_PRINT);
        // Using a prepared statement to avoid SQL injection
        $sql = "INSERT IGNORE INTO data (content, url) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $jsonData, $parentUrl);

        // Execute the statement
        if ($stmt->execute()) {
            $lastInsertedID = $stmt->insert_id;
            $stmt->close();
            header("location: keywordSearch.php?id=$lastInsertedID");
        } else {
            $stmt->close();
            return false;
        }
    }
}

function resolveUrl($baseUrl, $url) {
    // test if the URL is already absolute
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;  // If it's absolute, return as it is
    }
    // Use PHP's built-in function to resolve relative URLs
    $absoluteUrl = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');

    return $absoluteUrl;
}



if(isset($_POST['url']) && isset($_POST['depth']) && !empty($_POST['url'])){
    global $conn;
    $url=rtrim($_POST['url'],'/');
    $depth=trim($_POST['depth']);
    $sql = "SELECT id FROM data WHERE url = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $url);
    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row =($result->fetch_assoc())['id'];
            $stmt->close();
            header("location: keywordSearch.php?id=$row");
        }
    else{
        $searchResultId=search($url,$depth);
        if($searchResultId){
            header("location: keywordSearch.php?id=$res");
        }else{
            echo "Couldn't crawl the provided site, goto <a href='index.html'>homepage</a>";
        }
    }
    }else{
        header("location: index.html");
    }
}
else{
    header("location: index.html");
}
?>