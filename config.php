<?php

//error_reporting(0);
define(SERVER, 'http://127.0.0.1:8000');//your icecast server address, without the ending "/"
define(MOUNT, '/stream'); //your radio's mount point, with the leading "/"
define(LAST_FM_API, ''); //your last.fm API key, get from http://www.last.fm/api/account
define(DEFAULT_ALBUM_ART, 'cache/default.jpg');//the default album art image, will be used if failed to get from last.fm's API
define(GET_TRACK_INFO, true); //get information of the current song from last.fm
define(GET_ALBUM_INFO, true); //get extra information of the album from last.fm, if enabled, may increase script execute time
define(GET_ARTIST_INFO, true); //get extra information of the artist from last.fm, if enabled, may increase script execute time
define(GET_TRACK_BUY_LINK, true); //get buy links on Amazon, iTune and 7digital
define(GET_LYRICS, true); //get lyrics of the current song using chartlyrics.com's API
define(CACHE_ALBUM_ART, true);//cache album art images to local server
define(RECORD_HISTORY, true);//record play history of your radio

?>
