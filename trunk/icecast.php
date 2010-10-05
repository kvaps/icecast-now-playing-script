<?php
// By Jude (surftheair@gmail.com)

// configurations
$SERVER = 'http://example.com:8000'; //URL TO YOUR ICECAST SERVER
$STATS_FILE = '/status.xsl?mount=your_mount_point'; //PATH TO STATUS.XSL PAGE OF YOUR MOUNT POINT
$lastfm_api = 'your_lastfm_api_key'; //your last.fm API key, get from http://www.last.fm/api/account
$default_album_art = '/cache/default.jpg'; //the default album art image, will be used if failed to get from last.fm's API
$enable_lastfm_api = 'true'; //get information of the current song from last.fm
$enable_get_album_info = 'true'; //get extra information of the album from last.fm, if enabled, may increase script execute time
$enable_get_artist_info = 'true'; //get extra information of the artist from last.fm, if enabled, may increase script execute time
$enable_get_lyrics = 'true'; //get lyrics of the current song using chartlyrics.com's API
$enable_cache_album_art = 'true'; //cache album art images to local server

//create a new curl resource
$ch = curl_init();

//set url
curl_setopt($ch, CURLOPT_URL, $SERVER . $STATS_FILE);

//return as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

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
$search_td = array('<td class="streamdata">', '</td>');

if (preg_match_all("/$search_for/siU", $output, $matches)) {
    foreach ($matches[0] as $match) {
        $to_push = str_replace($search_td, '', $match);
        $to_push = trim($to_push);
        array_push($temp_array, $to_push);
    }
}

//sort our temp array into our ral array
$stream_title = $temp_array[0];
$stream_description = $temp_array[1];
$stream_content_type = $temp_array[2];
$stream_mount_start = $temp_array[3];
$stream_bit_rate = $temp_array[4];
$listeners = $temp_array[5];
$peak_listeners = $temp_array[6];
$stream_genre = $temp_array[7];
$stream_url = $temp_array[8];
$current_artist_song = $temp_array[9];

$x = explode(" - ", $temp_array[9]);
$artist = $x[0];
$current_song = $x[1];
//above code from phil@simplegaming.net

if ($stream_bit_rate){
	$status = 'On Air';
}
else{
	$status = 'Off Air';
}

//get information of the current song use last.fm's API
function getTrackInfo()
{
    global $lastfm_api, $artist, $current_song, $album_art_small, $album_art_medium,
        $album_art_large, $album_art_extralarge, $track_summary, $track_info, $track_lastfm_url,
        $artist_lastfm_url, $album_title, $album_lastfm_url, $track_download;
    $xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=track.getinfo&artist=' .
        $artist . '&track=' . $current_song . '&api_key=' . $lastfm_api;
    $xml = new SimpleXMLElement($xml_request_url, null, true);
    if ($xml->track->album->image) {
        $album_art_small = $xml->track->album->image[0];
        $album_art_medium = $xml->track->album->image[1];
        $album_art_large = $xml->track->album->image[2];
        $album_art_extralarge = $xml->track->album->image[3];
    }
    if ($xml->track->wiki->summary) {
        $track_summary = $xml->track->wiki->summary;
        $track_info = $xml->track->wiki->content;
    }
    $track_lastfm_url = $xml->track->url;
    $artist_lastfm_url = $xml->track->artist->url;
    if ($xml->track->album->title) {
        $album_title = $xml->track->album->title;
        $album_lastfm_url = $xml->track->album->url;
    }
}

//get extra information of the album
function getAlbumInfo()
{
    global $artist, $album_title, $lastfm_api, $album_releasedate, $album_summary, $album_info,
        $track_list;
    $xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&artist=' .
        $artist . '&album=' . $album_title . '&api_key=' . $lastfm_api;
    $xml = new SimpleXMLElement($xml_request_url, null, true);
    if ($xml->album->releasedate) {
        $album_releasedate = $xml->album->releasedate;
        $x = explode(",", $album_releasedate);
        $album_releasedate = $x[0];
    }
    //get track list and urls
    if ($xml->album->tracks->track) {
        $track_list = array();
        foreach ($xml->album->tracks as $value) {
            foreach ($value->track as $test) {
                array_push($track_list, '<a href="' . $test->url . '">' . $test->name . '</a>');
            }
        }
    }

    if ($xml->album->wiki->summary) {
        $album_summary = $xml->album->wiki->summary;
        $album_info = $xml->album->wiki->content;
    }
}

//get extra information of the artist
function getArtistInfo()
{
    global $artist, $lastfm_api, $artist_summary, $artist_info, $album_list;
    $xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=artist.gettopalbums&artist=' .
        $artist . '&api_key=' . $lastfm_api;
    $xml = new SimpleXMLElement($xml_request_url, null, true);
    if ($xml->topalbums->album) {
        $album_list = array();
        foreach ($xml->topalbums as $value) {
            foreach ($value->album as $test) {
                array_push($album_list, '<a href="' . $test->url . '">' . $test->name . '</a>');
            }
        }
    }

    $xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=' .
        $artist . '&api_key=' . $lastfm_api;
    $xml = new SimpleXMLElement($xml_request_url, null, true);
    if ($xml->artist->bio->summary) {
        $artist_summary = $xml->artist->bio->summary;
        $artist_info = $xml->artist->bio->content;
    }
}


//cache album art images to local server, change the image size if you want
function cacheAlbumArt()
{
    global $album_art_large, $local_album_art_uri;
    $filename = end(explode('/', $album_art_large));
    $local_album_art_uri = '/cache/' . $filename;
    $local_album_art = 'cache/' . $filename;
    if (!is_file($local_album_art)) {
        copy($album_art_large, $local_album_art);
    }
}

//get lyrics from chartlyrics.com's API
function getLyric()
{
    global $artist, $current_song, $track_lyric;
    $xml_request_url = 'http://api.chartlyrics.com/apiv1.asmx/SearchLyricDirect?artist=' .
        $artist . '&song=' . $current_song;
    $xml = new SimpleXMLElement($xml_request_url, null, true);
    if ($xml->LyricId !== '0') {
        $track_lyric = $xml->Lyric;
    }
}

//wtite new variables to file
function writeVariables()
{
    global $local_album_art_uri, $track_info, $album_title, $album_lastfm_url, $track_lastfm_url,
        $artist_lastfm_url, $track_lyric, $album_art, $track_download, $track_summary, $album_summary,
        $album_info, $source, $album_art_small, $album_art_medium, $album_art_large, $album_art_extralarge,
        $album_releasedate, $track_list, $album_list, $artist_summary, $artist_info;
    $temp_track_list = implode(",", $track_list);
    $temp_album_list = implode(",", $album_list);
    $variables = '<?php $local_album_art_uri = rawurldecode("' . rawurlencode($local_album_art_uri) .
        '");$track_info = rawurldecode("' . rawurlencode($track_info) .
        '");$album_title= rawurldecode("' . rawurlencode($album_title) .
        '");$album_lastfm_url = rawurldecode("' . rawurlencode($album_lastfm_url) .
        '");$track_lastfm_url=rawurldecode("' . rawurlencode($track_lastfm_url) .
        '");$artist_lastfm_url=rawurldecode("' . rawurlencode($artist_lastfm_url) .
        '");$track_lyric=rawurldecode("' . rawurlencode($track_lyric) .
        '");$album_art=rawurldecode("' . rawurlencode($album_art) .
        '");$track_download=rawurldecode("' . rawurlencode($track_download) .
        '");$track_summary = rawurldecode("' . rawurlencode($track_summary) .
        '");$album_summary = rawurldecode("' . rawurlencode($album_summary) .
        '");$album_info= rawurldecode("' . rawurlencode($album_info) .
        '");$album_art_small = rawurldecode("' . rawurlencode($album_art_small) .
        '");$album_art_medium= rawurldecode("' . rawurlencode($album_art_medium) .
        '");$album_art_large= rawurldecode("' . rawurlencode($album_art_large) .
        '");$album_art_extralarge = rawurldecode("' . rawurlencode($album_art_extralarge) .
        '");$album_releasedate = rawurldecode("' . rawurlencode($album_releasedate) .
        '");$track_list = explode(",",rawurldecode("' . rawurlencode($temp_track_list) .
        '"));$artist_summary= rawurldecode("' . rawurlencode($artist_summary) .
        '");$artist_info= rawurldecode("' . rawurlencode($artist_info) .
        '");$album_list = explode(",",rawurldecode("' . rawurlencode($temp_album_list) .
        '"));?>';
    $fp = fopen("cache/variables.php", "w");
    fwrite($fp, $variables);
    fclose($fp);
    $source = 'remote API';
}


//check if the current song has changed,if the current song has changed, refetch information of the new track from remote APIs
$last_song = file_get_contents("cache/history.txt");
if ($last_song !== $current_song) {

    //default value for variables if failed to get value from remote API or feature is disabled
    $album_art_small = $album_art_medium = $album_art_large = $album_art_extralarge =
        $default_album_art;
    $track_info = $track_summary =
        "No information found for this track, try searching for <a href='http://www.google.com/search?q=" .
        $current_song . "'>" . $current_song . "</a> on Google";
    $album_title = 'Not found';
    $album_lastfm_url = 'http://www.google.com/search?q=' . $current_song;
    $track_download = 'http://www.google.cn/music/search?q=' . $current_artist_song;
    $album_summary = $album_info =
        'No information found for this album, try searching for <a href="http://www.google.com/search?q=' .
        $current_artist_song . '">' . $current_artist_song . '</a> on Google';
    $album_releasedate = 'Unknown';
    $track_lyric = 'Lyrics not found for this track';
    $track_list = array('No track found for this album');
    $album_list = array('No album found for this artist');
    $artist_summary = $artist_info =
        'No information found for this album, try searching for <a href="http://www.google.com/search?q=' .
        $artist . '">' . $artist . '</a> on Google';

    $fp = fopen("cache/history.txt", "w");
    fwrite($fp, $current_song);
    fclose($fp);
    if ($enable_lastfm_api == 'true') {
        getTrackInfo();
    }
    if ($enable_get_album_info == 'true') {
        getAlbumInfo();
    }
    if ($enable_get_artist_info == 'true') {
        getArtistInfo();
    }
    if ($enable_cache_album_art == 'true') {
        cacheAlbumArt();
    }
    if ($enable_get_lyrics == 'true') {
        getLyric();
    }
    writeVariables();
} else {
    include ("cache/variables.php");
    $source = 'cached file';
}
?>