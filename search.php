<?php

echo "<script>";

echo		"function removeSearch() {";
echo			"document.getElementById('tfheader').style.visibility = 'hidden';";

echo		"}";

echo "</script>";

if(isset($_REQUEST['term'])){
	$term = $_REQUEST['term'];
}

if(isset($_REQUEST['term1'])){
	$term1 = $_REQUEST['term1'];
}

require("search_header.html");
require("test.php");
?>

				<div class='mason-parent'>

<?php
$terms = str_replace(' ', '%20', $term);
$url = "https://www.googleapis.com/books/v1/volumes?q=" . $terms . "&maxResults=20";
  $content= mb_convert_encoding(
    file_get_contents($url),
    "HTML-ENTITIES",
    "UTF-8"
  );

$obj = json_decode($content);
($obj);

$totalitems = $obj->{'items'}; // 12345

echo "<script>";
echo "removeSearch();";
echo "</script>";


for ($i = 1; $i < count($totalitems); $i++) {
	$item = $totalitems[$i];
	$volumeinfo = $item->{'volumeInfo'}; // 12345
	$id = $item->{'id'}; // 12345
	$name = $volumeinfo->{'title'}; // 12345
	$authors = $volumeinfo->{'authors'}; // 12345
	$author = $authors[0]; // 12345

	$imageLink = $volumeinfo->{'imageLinks'};
	$largeImage = $imageLink->{'thumbnail'};

	if (strpos(strtolower($author), strtolower($term1)) !== false) {
		echo "<a href='test.php?id=" . $id ."'>";
		echo "<div class='mason-child id =" . $id . "'>";
		print "<img src='" . $largeImage . "' />";
		echo "<h3>";
		echo $name;
		echo '</h3>';
		echo $author;
		echo "</div>";
		echo "</a>";
	}

}


?>
</div>
</body>
</html>
