

<?php

echo "<script>";

echo		"function removeSearch() {";
echo			"document.getElementById('tfheader').style.visibility = 'hidden';";

echo		"}";

echo "</script>";

require_once("search_header.html");


if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
	CALL_ME_MAYBE($id);
	echo "<script>";
	echo "removeSearch();";
	echo "</script>";

}
function CALL_ME_MAYBE($id){


require_once 'alchemyapi.php';

$alchemyapi = new AlchemyAPI();
 http://access.alchemyapi.com/calls/text/TextGetRankedKeywords


$url = "https://www.googleapis.com/books/v1/volumes/" . $id;
  $content= mb_convert_encoding(
    file_get_contents($url),
    "HTML-ENTITIES",
    "UTF-8"
  );


//echo $content;
$obj = json_decode($content);
//var_dump($obj);


$volInfo = $obj->{'volumeInfo'}; // 12345
$industryIdentifiers = $volInfo->{'industryIdentifiers'};

$title = $volInfo->{'title'};
$authorz = $volInfo->{'authors'};
$author = '';
foreach($authorz as $a){
	$author .= $a . " ";
}

$description = $volInfo->{'description'};
if($description == NULL){
	echo '<div id="error">Sorry, not enough data could be collected about this book.</div>';
	
}
$publishedDate = $volInfo->{'publishedDate'};

$imageLink = $volInfo->{'imageLinks'};

$largeImage = $imageLink->{'medium'};

if(!$largeImage)
	$largeImage = $imageLink->{'large'};
if(!$largeImage)
	$largeImage = $imageLink->{'small'};
if(!$largeImage)
	$largeImage = $imageLink->{'thumbnail'};

$lyrickeywords[] = array();

	$response = $alchemyapi->sentiment("text", $description, null);
	
	//echo "Sentiment: ", $response["docSentiment"]["type"], PHP_EOL;
	//echo "<br />";	
/*		$bookurl = "http://books.google.com/books?id=7_bQPm3R4NgC";
		$response = $alchemyapi->keywords('url',$bookurl);
*/
		$response = $alchemyapi->keywords('text',$description, array('sentiment'=>1,'maxRetrieve'=>20,'keywordExtractMode'=>'strict'));
	

		if ($response['status'] == 'OK') {
			echo PHP_EOL;
			//echo '## Keywords ##', PHP_EOL;
			foreach ($response['keywords'] as $keyword) {
				if($keyword['sentiment']['score'] !== NULL){
					$score = substr(abs($keyword['sentiment']['score']),2);
					$lyrickeywords[$score] = $keyword['text'];
				}
//					array_push($lyrickeywords, array($keyword['text'],abs($keyword['sentiment']['score'])));
				//echo 'keyword: ', $keyword['text'], PHP_EOL . "<Br />";
				//echo 'relevance: ', $keyword['relevance'], PHP_EOL;
				//echo 'sentiment: ', $keyword['sentiment']['type']; 			
				if (array_key_exists('score', $keyword['sentiment'])) {
				//	echo ' (' . $keyword['sentiment']['score'] . ')', PHP_EOL;
				} else {
					//echo PHP_EOL;
				}
			}
		} else {
		//	echo 'Error in the keyword extraction call: ', $response['statusInfo'];
		}

	rsort($lyrickeywords);
	//var_dump($lyrickeywords);

function tracks_by_keyword($lyrickeyword)
{
	$count = 0;
	$artistsList = array();
		$lyricsearch = "http://api.musixmatch.com/ws/1.1/track.search?q_lyrics=". str_replace(' ', '%20', trim($lyrickeyword)) . "&apikey=efcc4f8f45cf1a315ce82043ae30f30d&page_size=10&f_lyrics_language=En&s_track_rating=desc";
		  $mycontent= mb_convert_encoding(
			file_get_contents($lyricsearch),
			"HTML-ENTITIES",
			"UTF-8"
		  );
		
		//echo $content;
		$obj2 = json_decode($mycontent);
		$body = $obj2->{'message'}; // 12345
		$body2 = $body->{'body'}; // 12345
		$tracklist = $body2->{'track_list'}; // 12345
		
		for ($i = 0; $i < count($tracklist); $i++) {
			$count++;
				if($count > 3){
				$track = $tracklist[$i];
				//var_dump($track);
		
				$track = $track->{'track'}; // 12345
				$tid = $track->{'track_id'}; // 12345
				$name = $track->{'track_name'}; // 12345
				$artist = $track->{'artist_name'}; // 12345
				if(!in_array($artist,$artistsList)){
					$artistsList[] = $artist;
		
								
					$spotify = "http://ws.spotify.com/search/1/track.json?q=" . str_replace(' ', '%20', trim($name)) . "%20" .  str_replace(' ', '%20', trim($artist));
					$spotifyContent= mb_convert_encoding(
					file_get_contents($spotify),
					"HTML-ENTITIES",
					"UTF-8"
					);
					$spotify_object = json_decode($spotifyContent);
					$spotify_tracks = $spotify_object->{'tracks'}; // 12345
					$spotify_track = $spotify_tracks[0];
					$name2 = $spotify_track->{'name'}; // 12345
		
					
					$artists = $spotify_track->{'artists'}; // 12345
					$artist = $artists[0];
					$artist_name = $artist->{'name'}; // 12345
				
					if (stristr($name2,$name) !== true && stristr($artist_name,$artist) !== true) {
						$href = $spotify_track->{'href'}; // 12345
						$trackURL = explode(":",$href);
						if($trackURL[2] !== null){
							$theReturnTracks[]  = $trackURL[2];
							echo "
<!-- " . $name2 . "-->";
	
						}
					}
				}
			}
		}
	
	return $theReturnTracks;
}

$mySpotifyTracks = array();



foreach($lyrickeywords as $keyword){
	if(!is_array($keyword)){
		echo "
		
<!--  KEYWORD:::::: " . $keyword . "-->
	";
	
		if(count($mySpotifyTracks) < 10){
			if($mySpotifyTracks == NULL)
				$mySpotifyTracks = tracks_by_keyword($keyword);
			else if(is_string($keyword))
				$mySpotifyTracks = array_merge($mySpotifyTracks,tracks_by_keyword($keyword));
		}
	}
}

if(count($mySpotifyTracks) == 0){
	echo '<div id="error">Sorry, not enough data could be collected about this book.</div>';
}

foreach($mySpotifyTracks as $track){
	$tracklist .= $track . ",";
}
trim($tracklist, ",");

print "<h1 class = 'booktitle'>";

echo $title;

print "</h1>";

print "<h2 class = 'author'>";
echo $author;

print "</h2>";
echo "<br />";
echo "<center><a href='search_header.html'>New Search</a></center>";

//echo $publishedDate;
echo "<br />";
//echo $description;
echo "<br />";

echo "<div style='margin:auto;'>";
echo '<center><iframe width="600" height="450" src="https://embed.spotify.com/?uri=spotify:trackset:Browse Playlist Here:' . $tracklist . '" frameborder="0" allowtransparency="true" view="list"></iframe></center>';
//print "<img src='" . $largeImage . "' style='float:right' width = '350' height = '450'/>";

echo "</div>";


//print "<h2>" . $lyrickeyword . "</h2>";

echo "<br />";

}
//CALL_ME_MAYBE($id)

?>