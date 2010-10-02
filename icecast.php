<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<title>Icecast Now Playing Script</title>
</head>
<body>
<p>
Raw PHP code from phil@simplegaming.net, modified by Jude (<a href="mailto:surftheair@gmail.com">surftheair@gmail.com</a>)
</p>
<?php 
/* Raw PHP code from phil@simplegaming.net, modified by Jude (surftheair@gmail.com)
/* 
 * SCRIPT CONFIGURATIONS 
*/ 
$SERVER = 'http://example.com:8000'; //URL TO YOUR ICECAST SERVER 
$STATS_FILE = '/status.xsl?mount=your_mount_point'; //PATH TO STATUS.XSL PAGE OF YOUR MOUNT POINT
$LASTFM_API= 'your_lastfm_api_key'; //YOUR API KEY FROM LAST.FM, GET AT http://www.last.fm/api/account

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

//get information of the current song use last.fm's API, by Jude
$xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=track.getinfo&artist='.$radio_info['now_playing']['artist'].'&track='.$radio_info['now_playing']['track'].'&api_key='.$LASTFM_API;
$xml = new SimpleXMLElement($xml_request_url, null, true);
	if($xml->track->album->image){
		$album_art = $xml->track->album->image[2];}
	else{$album_art = '/static/default.jpg';}
	if ($xml->track->wiki->summary){
		$track_info = $xml->track->wiki->summary;}
	else{$track_info = "No information found for this track, try searching for <a href='http://www.google.com/search?q=".$radio_info['current_song']."'>".$radio_info['current_song']."</a> on Google";}
$track_lastfm_url = $xml->track->url;
$artist_lastfm_url = $xml->track->artist->url;
	if($xml->track->album->title){
		$album_title = $xml->track->album->title;
		$album_lastfm_url = $xml->track->album->url;}
	else{
		$album_title = 'Not found';
		$album_lastfm_url = 'http://www.google.com/search?q='.$radio_info['current_song']	;}
$track_download = 'http://www.google.cn/music/search?q='.$radio_info['current_song'];

//cache album art images
$filename = end(explode('/',$album_art));
$local_album_art_uri = '/cache/'.$filename;
$local_album_art = '/home/wwwroot/icecast/cache/'.$filename;
if (!is_file($local_album_art)){
copy($album_art,$local_album_art );}
?>

<!--output, edit the followings as you like, this is just an example -->
Station title: <?php echo ($radio_info['title']);?><br />
Description: <?php echo ($radio_info['description']);?><br />
Conten type: <?php echo ($radio_info['content_type']);?><br />
Mount start: <?php echo ($radio_info['mount_start']);?><br />
Bitrate: <?php echo ($radio_info['bit_rate']);?><br />
Current listners: <?php echo ($radio_info['listeners']);?><br />
Peak listeners: <?php echo ($radio_info['most_listeners']);?><br />
Genre: <?php echo ($radio_info['genre']);?><br />
Station URL: <?php echo ($radio_info['url']);?><br />
Now palying: <?php echo ($radio_info['current_song']);?><br />
Artist: <?php echo($radio_info['now_playing']['artist']);?><br />
Track: <?php echo($radio_info['now_playing']['track']);?><br />
Album Art: <img src="<?php echo($album_art);?>"/><br />
Track introduction: <?php echo($track_info);?><br />
Track URL on last.fm: <?php echo($track_lastfm_url);?><br />
Artist URL on last.fm: <?php echo($artist_lastfm_url);?><br />
Album title: <?php echo($album_title);?><br />
Album URL on last.fm: <?php echo($album_lastfm_url);?><br />
Download link on Google Music(China): <?php echo($track_download);?><br />
Local cache of album art: <?php echo($local_album_art_uri);?></br>
