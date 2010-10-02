<?php 
// By Jude (surftheair@gmail.com), some code from phil@simplegaming.net

// configurations
$SERVER = 'http://zlz-stream10.streaming.init7.net:80'; //URL TO YOUR ICECAST SERVER 
$STATS_FILE = '/status.xsl?mount=/1/rsp/mp3_128'; //PATH TO STATUS.XSL PAGE OF YOUR MOUNT POINT
$LASTFM_API= 'dd40af1da9977c679287dd2d78f017b4'; //YOUR API KEY FROM LAST.FM, GET AT http://www.last.fm/api/account

///////////////////// END OF CONFIGURATION --- DO NOT EDIT BELOW THIS LINE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ 

//create a new curl resource 
$ch = curl_init(); 

//set url 
curl_setopt($ch,CURLOPT_URL,$SERVER.$STATS_FILE); 

//return as a string 
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 

//$output = our stauts.xsl file 
$output = curl_exec($ch); 

//close curl resource to free up system resources 
curl_close($ch); 

//build array to store our radio stats for later use 
$radio_info = array(); 
$radio_info['server'] = $SERVER; 
$radio_info['title'] = ''; 
$radio_info['description'] = ''; 
$radio_info['content_type'] = ''; 
$radio_info['mount_start'] = ''; 
$radio_info['bit_rate'] = ''; 
$radio_info['listeners'] = ''; 
$radio_info['most_listeners'] = ''; 
$radio_info['genre'] = ''; 
$radio_info['url'] = ''; 
$radio_info['now_playing'] = array(); 
   $radio_info['now_playing']['artist'] = ''; 
   $radio_info['now_playing']['track'] = ''; 

//loop through $ouput and sort into our different arrays 
$temp_array = array(); 

$search_for = "<td\s[^>]*class=\"streamdata\">(.*)<\/td>"; 
$search_td = array('<td class="streamdata">','</td>'); 

if(preg_match_all("/$search_for/siU",$output,$matches)) { 
   foreach($matches[0] as $match) { 
      $to_push = str_replace($search_td,'',$match); 
      $to_push = trim($to_push); 
      array_push($temp_array,$to_push); 
   } 
} 

//sort our temp array into our ral array 
$radio_info['title'] = $temp_array[0]; 
$radio_info['description'] = $temp_array[1]; 
$radio_info['content_type'] = $temp_array[2]; 
$radio_info['mount_start'] = $temp_array[3]; 
$radio_info['bit_rate'] = $temp_array[4]; 
$radio_info['listeners'] = $temp_array[5]; 
$radio_info['most_listeners'] = $temp_array[6]; 
$radio_info['genre'] = $temp_array[7]; 
$radio_info['url'] = $temp_array[8];
$radio_info['current_song'] = $temp_array[9];

$x = explode(" - ",$temp_array[9]); 
$radio_info['now_playing']['artist'] = $x[0]; 
$radio_info['now_playing']['track'] = $x[1];

//check if the current song has changed
$last_song = file_get_contents("cache/history.txt");

if($last_song !== $radio_info['current_song']){

//if the current song has changed, refetch information of the new track from remote APIs 
$fp=fopen("cache/history.txt","w");
fwrite($fp,$radio_info['current_song']);
fclose($fp);

//get information of the current song use last.fm's API, by Jude
$xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=track.getinfo&artist='.$radio_info['now_playing']['artist'].'&track='.$radio_info['now_playing']['track'].'&api_key='.$LASTFM_API;
$xml = new SimpleXMLElement($xml_request_url, null, true);
	if($xml->track->album->image){
		$album_art = $xml->track->album->image[2];
		}
	else{$album_art = '/static/default.jpg';
	}
	if ($xml->track->wiki->summary){
		$track_info = $xml->track->wiki->summary;
		}
	else{$track_info = "No information found for this track, try searching for <a href='http://www.google.com/search?q=".$radio_info['current_song']."'>".$radio_info['current_song']."</a> on Google";}
$track_lastfm_url = $xml->track->url;
$artist_lastfm_url = $xml->track->artist->url;
	if($xml->track->album->title){
		$album_title = $xml->track->album->title;
		$album_lastfm_url = $xml->track->album->url;
		}
	else{
		$album_title = 'Not found';
		$album_lastfm_url = 'http://www.google.com/search?q='.$radio_info['current_song'];
		}
$track_download = 'http://www.google.cn/music/search?q='.$radio_info['current_song'];

//cache album art images to local server
$filename = end(explode('/',$album_art));
$local_album_art_uri = '/cache/'.$filename;
$local_album_art = 'cache/'.$filename;
if (!is_file($local_album_art)){
copy($album_art,$local_album_art );}

//get lyrics from chartlyrics.com's API
$xml_request_url = 'http://api.chartlyrics.com/apiv1.asmx/SearchLyricDirect?artist='.$radio_info['now_playing']['artist'].'&song='.$radio_info['now_playing']['track'];
$xml = new SimpleXMLElement($xml_request_url, null, true);
if($xml->LyricId == '0'){
$track_lyric = 'Lyrics not found for this track';}
else{
$track_lyric = $xml->Lyric;}

//write variables to file
$variables = "<?php \$local_album_art_uri = \"$local_album_art_uri\";\$track_info=\"$track_info\" ;\$album_title= \"$album_title\";\$album_lastfm_url = \"$album_lastfm_url\";\$track_lastfm_url=\"$track_lastfm_url\";\$artist_lastfm_url=\"$artist_lastfm_url\";\$track_lyric=\"$track_lyric\";\$album_art=\"$album_art\";\$track_download=\"$track_download\";?>";
$fp=fopen("cache/variales.php","w");
fwrite($fp,$variables);
fclose($fp);
$source = 'remote API';
}

//if the current song is not changed, get the information from local file, so no need to frequently fetch form API again, which may result in baning
else{
include("cache/variales.php");
$source = 'cached file';
}
?>