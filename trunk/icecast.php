<?php 
/*
	by Jude <surftheair@gmail.com>
	http://jude.im/
	works with Icecast 2.3.2
*/

require('config.php');
$stream = getStreamInfo();
if($stream['info']['status'] == 'OFF AIR'){
	cacheVar($stream);
}
else{
	$last_song = @file_get_contents('last.txt');
	if($last_song != base64_encode($stream['info']['song'])){
		$stream = init($stream);
		$stream = getInfo($stream);
		file_put_contents('last.txt', base64_encode($stream['info']['song']));
		cacheVar($stream);
		if(RECORD_HISTORY == true){
			cacheHistory($stream);
		}
	}
	else{
		$stream = array_decode(json_decode(@file_get_contents('var/info.json'), TRUE));
	}
}
//print_r($stream);

function obj_to_array($obj){
	$array = (is_object) ? (array)$obj : $obj;
	foreach($array as $k=>$v){
		if(is_object($v) OR is_array($v))
			$array[$k] = obj_to_array($v);
	}
	return $array;
}

function getStreamInfo(){
	$str = @file_get_contents(SERVER.'/status.xsl?mount='.MOUNT);
	if(preg_match_all('/<td\s[^>]*class=\"streamdata\">(.*)<\/td>/isU', $str, $match)){
		$stream['info']['status'] = 'ON AIR';
		$stream['info']['title'] = $match[1][0]; 
		$stream['info']['description'] = $match[1][1]; 
		$stream['info']['type'] = $match[1][2]; 
		$stream['info']['start'] = $match[1][3]; 
		$stream['info']['bitrate'] = $match[1][4]; 
		$stream['info']['listeners'] = $match[1][5]; 
		$stream['info']['msx_listeners'] = $match[1][6]; 
		$stream['info']['genre'] = $match[1][7]; 
		$stream['info']['stream_url'] = $match[1][8];
		$stream['info']['artist_song'] = $match[1][9];
			$x = explode(" - ",$match[1][9]); 
		$stream['info']['artist'] = $x[0]; 
		$stream['info']['song'] = $x[1];
	}
	else{
		$stream['info']['status'] = 'OFF AIR';
	}
	return $stream;
}

//get information of the current song use last.fm's API
function getTrackInfo($stream){
	$url = str_replace('#','','http://ws.audioscrobbler.com/2.0/?method=track.getinfo&artist='.urlencode($stream['info']['artist']).'&track='.urlencode($stream['info']['song']).'&api_key='.LAST_FM_API);
	$xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
	$xml = obj_to_array($xml);
//	print_r($xml);
	if($xml['track']['album']['image']){
		$stream['album']['image_s'] = $xml['track']['album']['image'][0];
		$stream['album']['image_m'] = $xml['track']['album']['image'][1];
		$stream['album']['image_l'] = $xml['track']['album']['image'][2];
		$stream['album']['image_xl'] = $xml['track']['album']['image'][3];
	}
	if($xml['track']['wiki']['summary']){
		$stream['track']['summary'] = $xml['track']['wiki']['summary'];
		$stream['track']['info'] = $xml['track']['wiki']['content'];
	}
	if($xml['track']['album']['title']){
		$stream['album']['title'] = $xml['track']['album']['title'];
		$stream['album']['lastfm_url'] = $xml['track']['album']['url'];
	}
	$stream['track']['lastfm_url'] = $xml['track']['url'];
	if($xml['track']['artist']['url']){
		$stream['artist']['lastfm_url'] = $xml['track']['artist']['url'];
	}
	return $stream;
}
	
//get extra information of the album
function getAlbumInfo($stream){
	$url = str_replace('#','', 'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&artist='.urlencode($stream['info']['artist']).'&album='.($stream['album']['title']).'&api_key='.LAST_FM_API);
	$xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
	$xml = obj_to_array($xml);
	if ($xml['album']['releasedate'] && strlen($xml['album']['releasedate']) > 10){
		$stream['album']['releasedate'] = reset(explode(",",$xml['album']['releasedate']));
	}
	if($xml['album']['tracks']['track']){
		foreach($xml['album']['tracks']['track'] as $track){
			$stream['album']['track_list'][] = array('title' => $track['name'],'url' => $track['url']);
		}
	}
	if($xml['album']['wiki']['summary']){
		$stream['album']['summary'] = $xml['album']['wiki']['summary'];
		$stream['album']['info'] = $xml['album']['wiki']['content'];
	}
	return $stream;
}
		
//get extra information of the artist		
function getArtistInfo($stream){
	$url = 'http://ws.audioscrobbler.com/2.0/?method=artist.gettopalbums&artist='.urlencode($stream['info']['artist']).'&api_key='.LAST_FM_API.'&autocorrect=1';
	$xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
	$xml = obj_to_array($xml);
//	print_r($xml);
	if($xml['topalbums']['album']){
		foreach($xml['topalbums']['album'] as $album){
			$stream['artist']['top_albums'][] = array('title'=>$album['name'], 'url'=>$album['url'], 'image'=>$album['image']);
		}
	}
	
	$url = 'http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&artist='.urlencode($stream['info']['artist']).'&api_key='.LAST_FM_API.'&autocorrect=1';
	$xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
	$xml = obj_to_array($xml);
//	print_r($xml);
	if($xml['artist']['bio']['summary']){
		$stream['artist']['summary'] = $xml['artist']['bio']['summary'];
		$stream['artist']['info'] = $xml['artist']['bio']['content'];
	}
	return $stream;
}

//get buylink	
function getTrackBuyLink($stream){
	$url = 'http://ws.audioscrobbler.com/2.0/?method=track.getbuylinks&artist='.urlencode($stream['info']['artist']).'&track='.urlencode($stream['info']['song']).'&api_key='.LAST_FM_API.'&country='.urlencode('united states').'&autocorrect=1';
	$xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
	$xml = obj_to_array($xml);
//	print_r($xml);
	if($xml['affiliations']['physicals']['affiliation']){
		foreach($xml['affiliations']['physicals']['affiliation'] as $buy){
			$supplier = str_replace('iTuens', 'iTunes', $buy['supplierName']);
			if($buy['isSearch'] == 0){
				$new = array('link' => $buy['buyLink'], 'price'=>$buy['price']['amount'], 'currency'=>$buy['price']['currency'], 'icon'=>$buy['supplierIcon']);
			}
			else{
				$new = array('link' => $buy['buyLink'],'icon'=>$buy['supplierIcon']);
			}
			$stream['track']['buylink']['physical'][$supplier] = $new;
		}
	}
	if($xml['affiliations']['downloads']['affiliation']){
		foreach($xml['affiliations']['downloads']['affiliation'] as $buy){
			$supplier = str_replace('Amazon MP3', 'Amazon', $buy['supplierName']);
			if($buy['isSearch'] == 0){
				$new = array('link' => $buy['buyLink'], 'price'=>$buy['price']['amount'], 'currency'=>$buy['price']['currency'], 'icon'=>$buy['supplierIcon']);
			}
			else{
				$new = array('link' => $buy['buyLink'],'icon'=>$buy['supplierIcon']);
			}
			$stream['track']['buylink']['download'][$supplier] = $new;
		}
	}
	return $stream;
}

		
//cache album art images to local server, change the image size if you want
function cacheAlbumArt($image_url){
	$filename = end(explode('/', $image_url));
	$local_image = 'cache/'.$filename;
	if (!is_file($stream['album']['local_image'])){
		copy($image_url, $local_image);
	}
	return $local_image;
}
		
//get lyrics from chartlyrics.com's API
function getLyric($artist, $song){
	$url = str_replace('\'','','http://api.chartlyrics.com/apiv1.asmx/SearchLyricDirect?artist='.urlencode($artist).'&song='.urlencode($song));
	$xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
	$xml = obj_to_array($xml);
//	print_r($xml);
	if($xml['LyricId'] && ($xml['Lyric'] != array())){
		return $xml['Lyric'];
	}
	else{
		return 'Sorry, there\'s no lyric found for this song';
	}
}

function getInfo($stream){
	if(!$stream['info']['song']){
		$stream['info']['song'] == 'Not found';
		return $stream;
	}
	if(GET_TRACK_INFO == TRUE){
		$stream = getTrackInfo($stream);
	}
	if(GET_ALBUM_INFO && isset($stream['album']['title'])){
		$stream = getAlbumInfo($stream);
	}
	if(GET_ARTIST_INFO == TRUE){
		$stream = getArtistInfo($stream);
	}
	if(GET_TRACK_BUY_LINK == TRUE){
		$stream = getTrackBuyLink($stream);
	}
	if(CACHE_ALBUM_ART == TRUE){
		$stream['album']['local_image'] = cacheAlbumArt($stream['album']['image_l']);
	}
	if(GET_LYRICS == TRUE){
		$stream['track']['lyric'] = getLyric($stream['info']['artist'], $stream['info']['song']);
	}
	$stream['fetch_time'] = time();
	return $stream;
}

function array_encode($array){
	foreach($array as $key=>$value){
		if(is_array($value)){
			$array[$key] = array_encode($value);
		}
		else{
			$array[$key] = base64_encode($value);
		}
	}
	return $array;
}

function array_decode($array){
	foreach($array as $key=>$value){
		if(is_array($value)){
			$array[$key] = array_decode($value);
		}
		else{
			$array[$key] = base64_decode($value);
		}
	}
	return $array;
}

function cacheVar($stream){
	$stream = array_encode($stream);
	file_put_contents('var/info.json', json_encode($stream));
}

function cacheHistory($stream){
	if($stream['song'] == 'Not found'){
		return;
	}
	$year = date('Y');
	$month = date('m');
	$day = date('d');
	if(!is_dir('history')){
		mkdir('history', 0777);
	}
	if(!is_dir('history/'.$year)){
		mkdir('history/'.$year);
	}
	if(!is_dir('history/'.$year.'/'.$month)){
		mkdir('history/'.$year.'/'.$month);
	}
	$file = 'history/'.$year.'/'.$month.'/'.$day.'.json';
	$history['time'] = gmdate('c');
	$history['artist'] = $stream['info']['artist'];
	$history['song'] = $stream['info']['song'];
	$history['image'] = $stream['album']['image_s'];
	$history['itunes'] = $stream['track']['buylink']['download']['iTunes']['link'];
	$history['Amazon'] = $stream['track']['buylink']['download']['Amazon']['link'];
	$history = array_encode($history);
	file_put_contents($file, json_encode($history));
	createHistory();
}

function createHistory(){
	$history = json_decode(@file_get_contents('var/history.json'), TRUE);
	$year = date('Y');
	$month = date('m');
	$day = date('d');
	$history[$year][$month][$day] = $year.$month.$day;
	$file = 'history/'.$year.'/'.$month.'/'.$day.'.json';
	file_put_contents('var/history.json', json_encode($history));
}


function init($stream){
	$stream['album']['image_s'] = $stream['album']['image_m'] = $stream['album']['image_l'] = $stream['album']['image_xl'] = DEFAULT_ALBUM_ART;
	$stream['track']['summary']  = $stream['track']['info'] = "No information found for this track, try searching for <a target='_blank' href='http://www.google.com/search?q=".urlencode($stream['info']['artist']." - ".$stream['info']['song'])."'>".$stream['info']['artist']." - ".$stream['info']['song']."</a> on Google";
	$stream['album']['title'] = 'Not found';
	$stream['album']['lastfm_url'] = 'http://www.google.com/search?q='.urlencode($stream['info']['artist']." - ".$stream['info']['song']);
	$stream['track']['download_cn'] = 'http://www.google.cn/music/search?q='.urlencode($stream['info']['artist']." - ".$stream['info']['song']);
	$stream['album']['summary'] = $stream['album']['info'] = 'No information found for this album, try searching for <a target="_blank" href="http://www.google.com/search?q='.urlencode($stream['info']['artist']." - ".$stream['info']['song']).'">'.$stream['info']['artist']." - ".$stream['info']['song'].'</a> on Google';
	$stream['album']['releasedate'] = 'Unknown';
	$stream['artist']['summary'] = $stream['artist']['info'] = 'No information found for this artist, try searching for <a target="_blank" href="http://www.google.com/search?q='.urlencode($stream['info']['artist']).'">'.$stream['info']['artist'].'</a> on Google';
	return $stream;
}

?>