<?php
// Check if 'id' parameter is set in the URL
if (isset($_GET['id']) && $_GET['id']!="") {
    // Assign the 'id' value to $urlID
    $urlID = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keyword Search</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="cont">
        <h1 class="heading">Search Keyword in Crawled Url</h1>
  
        <form action="keywords.php" method="POST">
            <div class="container">
                <!-- Input field for entering the keyword -->
                <div class="input-cont">
                    <input class="input" type="text" placeholder="Keyword..." name="keyword" id="keyword">
                    <!-- Hidden input field to store the URL ID -->
                    <input type="hidden" value='<?php echo $urlID ?>' name="urlID" id="urlID">
                </div>
                <br>
                <!-- Submit button for the form -->
                <button id="submitbutton" type="submit" class="search">Search</button>
            </div>
        </form>
        <p><a style='text-align:center;' href="index.html">Crawl Another URL?</a></p>
    </div>
</body>
</html>

<?php
} else {
    // Redirect to the index.html page if 'id' is not set
    header("location: index.html");
}
?>
