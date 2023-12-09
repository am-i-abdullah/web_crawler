<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searched Keywords</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    
<?php
// Include the database process file
include_once("config/connection.php");

// Check if keyword and URL ID are set in the POST request
if (isset($_POST['keyword']) && isset($_POST['urlID'])) {
    // Retrieve values from the POST request
    $keyword = $_POST['keyword'];
    $urlId = $_POST['urlID'];

    global $conn;

        // Using a prepared statement to avoid SQL injection
        $sql = "SELECT * FROM data WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // Bind the parameter
        $stmt->bind_param("i", $urlId);

        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                
                $row = $result->fetch_assoc();
                $resultArray = json_decode($row['content']);
                $i=0;
                // Iterate through the result array
                foreach ($resultArray as $result) {
                    // Get URL from the result
                    $url = $result->url;

                    // Iterate through the data items for the URL
                    foreach ($result->data as $item) {
                        // Check if the keyword is present in the data item (case-insensitive)
                        if (stripos($item, $keyword) !== false) {
                            if($i<1){
                                 // Display header for the search results
                                echo "<h2 style='text-align:center'>URLs Containing Given Keyword</h2><ul>";
                            }

                            // Display the URL in an unordered list item
                            echo "<li><a href='$url' target='__blank'>" . $url . "</a></li>";
                            $i++;
                            // Break the loop once a match is found for the URL
                            break;
                        }
                    }
                }
                if($i==0){
                    echo "<h2 style='text-align:center'>No Results Found<br><a href='keywordSearch.php?id=$urlId'>Search</a> Another Keyword</h2><ul>";
                }else{
                    echo "</ul><h3><a href='keywordSearch.php?id=$urlId'>Search</a> Another Keyword</h3>";
                }
                
            } else {
                echo "<h2 style='text-align:center'>No Results Found<br><a href='keywordSearch.php?id=$urlId'>Search</a> Another Keyword</h2><ul>";
            }
        } else {
            $stmt->close(); 
            echo "<h2 style='text-align:center'>Error Fetching Results<br><a href='keywordSearch.php?id=$urlId'>Search</a> Another Keyword</h2><ul>";
        }

} else {
    // Redirect to the index.html page if keyword or URL ID is not set
    header("location: index.html");
}
?>


</body>
</html>