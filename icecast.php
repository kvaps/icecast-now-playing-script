<?php 
// By Jude (surftheair@gmail.com)

// configurations
$server = 'http://example.com:80'; //url to your icecast server, with "http://", without the ending "/"
$mount= '/mount_point'; //your radio's mount point, with the leading "/"
$lastfm_api = ''; //your last.fm API key, get from http://www.last.fm/api/account
$default_album_art = 'cache/default.jpg';//the default album art image, will be used if failed to get from last.fm's API
$get_track_info = 'true'; //get information of the current song from last.fm
$get_album_info = 'true'; //get extra information of the album from last.fm, if enabled, may increase script execute time
$get_artist_info = 'true'; //get extra information of the artist from last.fm, if enabled, may increase script execute time
$get_lyrics = 'true'; //get lyrics of the current song using chartlyrics.com's API
$cache_album_art = 'true';//cache album art images to local server

function getStreamStatus($server, $mount){
	$header = get_headers($server.$mount);
	if($header[0] == 'HTTP/1.0 200 OK'){
		return 'On Air';
	}
	else{
		return  'Off Air';
	}
}

function getStreamInfo($server, $mount){
	$output = file_get_contents($server.'/status.xsl?mount='.$mount);
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
		 
	$stream_info['title'] = $temp_array[0]; 
	$stream_info['description'] = $temp_array[1]; 
	$stream_info['content_type'] = $temp_array[2]; 
	$stream_info['mount_start'] = $temp_array[3]; 
	$stream_info['bit_rate'] = $temp_array[4]; 
	$stream_info['listeners'] = $temp_array[5]; 
	$stream_info['peak_listeners'] = $temp_array[6]; 
	$stream_info['genre'] = $temp_array[7]; 
	$stream_info['url'] = $temp_array[8];
	$stream_info['artist_song'] = $temp_array[9];
		$x = explode(" - ",$temp_array[9]); 
	$stream_info['artist'] = $x[0]; 
	$stream_info['song'] = $x[1];
//	print_r($stream_info);
	return $stream_info;
}

//get information of the current song use last.fm's API
function getTrackInfo($artist, $song, $api){
	$xml_request_url = str_replace('#','','http://ws.audioscrobbler.com/2.0/?method=track.getinfo&artist='.$artist.'&track='.$song.'&api_key='.$api);
	$xml = new SimpleXMLElement($xml_request_url, null, true);
		if($xml->track->album->image){
			$album_info['image_s'] = $xml->track->album->image[0];
			$album_info['image_m'] = $xml->track->album->image[1];
			$album_info['image_l'] = $xml->track->album->image[2];
			$album_info['image_xl'] = $xml->track->album->image[3];
		}
		if ($xml->track->wiki->summary){
			$track_info['summary'] = rawurlencode($xml->track->wiki->summary);
			$track_info['info'] = rawurlencode($xml->track->wiki->content);
		}
		else{
			$album_info['summary'] = $album_info['info'] = rawurlencode('No information found for this album, try searching for <a href="http://www.google.com/search?q='.$artist.' - '.$song.'">'.$artist.' - '.$song.'</a> on Google');
		}
		if($xml->track->album->title){
			$album_info['title'] = $xml->track->album->title;
			$album_info['lastfm_url'] = $xml->track->album->url;
		}
		else{
			$stream_info['title'] = 'Not Found';
		}
		$track_info['lastfm_url'] = $xml->track->url;
		if($xml->track->artist->url){
			$artist_info['lastfm_url'] = $xml->track->artist->url;
		}
		else{
			$artist_info['lastfm_url'] = '';
		}
		$temp_array = array();
		$temp_array[0] = $album_info;
		$temp_array[1] = $track_info;
		$temp_array[2] = $artist_info;
		return $temp_array;
}
	
//get extra information of the album
function getAlbumInfo($artist, $album, $api){
	$xml_request_url = str_replace('#','', 'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&artist='.$artist.'&album='.$album.'&api_key='.$api);
	$xml = new SimpleXMLElement($xml_request_url, null, true);
	if ($xml->album->releasedate && strlen($xml->album->releasedate) > 10){
		$album_info['releasedate'] = reset(explode(",",$xml->album->releasedate));
	}
	if($xml->album->tracks->track){
		$album_info['track_list'] = array();
		foreach($xml->album->tracks as $value){
			foreach($value->track as $test){
				array_push($album_info['track_list'],'<a href="'.$test->url.'">'.$test->name.'</a>');}
			}
	}
	if($xml->album->wiki->summary){
		$album_info['summary'] = rawurlencode($xml->album->wiki->summary);
		$album_info['info'] = rawurlencode($xml->album->wiki->content);
	}
	return $album_info;
}
		
//get extra information of the artist		
function getArtistInfo($artist, $api){
	$album_list = array();
	$xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=artist.gettopalbums&artist='.$artist.'&api_key='.$api;
	$xml = new SimpleXMLElement($xml_request_url, null, true);
	if($xml->topalbums->album){
		$artist_info['album_list'] = array();
		foreach($xml->topalbums as $value){
			foreach($value->album as $test){
				array_push($album_list,'<a href="'.$test->url.'">'.$test->name.'</a>');
			}
		}
	}
	if($xml->artist->bio->summary){
		$artist_info['summary'] = $xml->artist->bio->summary;
		$artist_info['info'] = $xml->artist->bio->content;
	}
	return $artist_info;
}
		
//cache album art images to local server, change the image size if you want
function cacheAlbumArt($image_url){
	$filename = end(explode('/',$image_url));
	$local_image = 'cache/'.$filename;
	if (!is_file($album_info['local_image'])){
		copy($image_url,$local_image);
	}
	return $local_image;
}
		
//get lyrics from chartlyrics.com's API
function getLyric($artist, $song){
	$xml_request_url = str_replace('\'','','http://api.chartlyrics.com/apiv1.asmx/SearchLyricDirect?artist='.$artist.'&song='.$song);
	$xml = new SimpleXMLElement($xml_request_url, null, true);
	if($xml->LyricId !== '0'){
		return rawurlencode($xml->Lyric);
	}
}

//wtite variables to file
function cacheVariables($track_info, $album_info, $artist_info){
	$radio_info['track'] = $track_info;
	$radio_info['album'] = $album_info;
	$radio_info['artist'] = $artist_info;
	if(!is_file('cache/variables.txt')){
		touch ('cache/variables.txt');
	}
	file_put_contents('cache/variables.txt',json_encode($radio_info));
	$source = 'remote API';
}
//chu shi hua
function init(){
	global $stream_info, $track_info, $album_info, $artist_info, $default_album_art;
	$track_info = array();
	$album_info = array();
	$artist_info = array();
	$album_info['image_s'] = $album_info['image_m'] = $album_info['image_l'] = $album_info['image_xl'] = $default_album_art;
	$track_info['info']  = $track_info['summary'] = rawurlencode("No information found for this track, try searching for <a href='http://www.google.com/search?q=".$stream_info['song']."'>".$stream_info['song']."</a> on Google");
	$album_info['title'] = 'Not found';
	$album_info['lastfm_url'] = 'http://www.google.com/search?q='.$stream_info['artist_song'];
	$track_info['download'] = 'http://www.google.cn/music/search?q='.$stream_info['artist_song'];
	$album_info['summary'] = $album_info['info'] = rawurlencode('No information found for this album, try searching for <a href="http://www.google.com/search?q='.$stream_info['artist_song'].'">'.$stream_info['artist_song'].'</a> on Google');
	$album_info['releasedate'] = 'Unknown';
	$track_info['lyric'] = rawurlencode('Lyrics not found for this track');
	$album_info['track_list'] = array('No track found for this album');
	$artist_info['album_list'] = array('No album found for this artist');
	$artist_info['summary'] = $artist_info['info'] = rawurlencode('No information found for this album, try searching for <a href="http://www.google.com/search?q='.$stream_info['artist'].'">'.$stream_info['artist'].'</a> on Google');
}
	
/////////////////////////////////////////////////////////////////////////////	
$status = getStreamStatus($server, $mount);
if($status == 'On Air'){
	$stream_info = getStreamInfo($server, $mount);
	init();

//check if the current song has changed,if the current song has changed, refetch information of the new track from remote APIs
	$last_song = file_get_contents("cache/history.txt");
	if($last_song !== $stream_info['song']){
		file_put_contents('cache/history.txt',$stream_info['song']);
		if($get_track_info == 'true'){
			$temp_array = getTrackInfo($stream_info['artist'], $stream_info['song'], $lastfm_api);
			$album_info = array_merge($album_info, $temp_array[0]);
			$track_info = array_merge($track_info, $temp_array[1]);
			$artist_info = array_merge($artist_info, $temp_array[2]);
		}
		if($get_album_info == 'true' && $album_info['title'] !== 'Not found'){
			$album_info = array_merge($album_info, getAlbumInfo($stream_info['artist'], $album_info['title'], $lastfm_api));
		}
		if($get_artist_info == 'true'){
			$artist[info] = getArtistInfo($stream_info['artist'], $lastfm_api);
		}
		if($cache_album_art  == 'true'){
			$album_info['local_image'] = cacheAlbumArt($album_info['image_l']);
		}
		if($get_lyrics == 'true'){
			$track_info['lyric'] = getLyric($stream_info['artist'], $stream_info['song']);
		}
		cacheVariables($track_info, $album_info, $artist_info);
		$source = 'remote api';
	}
	else{
		$json = file_get_contents('cache/variables.txt');
		$radio_info = json_decode($json, true);
		$track_info = $radio_info['track'];
		$album_info = $radio_info['album'];
		$artist_info = $radio_info['artist'];
		$source = 'cached file';
	}
}
?>