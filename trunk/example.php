<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<title>Icecast Now Playing Script</title>
</head>
<body>
<p>
Code by Jude (<a href="mailto:surftheair@gmail.com">surftheair@gmail.com</a>)
</p>
<?php include ("icecast.php"); ?>

<!--Edit the followings as you like, this is just an example -->
<table border="1">
<tr><td>Status</td><td><?php echo ($status); ?></td></tr>
<tr><td>Station title</td><td><?php echo ($stream_title); ?></td></tr>
<tr><td>Description</td><td><?php echo ($stream_description); ?></td></tr>
<tr><td>Conten type</td><td><?php echo ($stream_content_type); ?></td></tr>
<tr><td>Mount start</td><td><?php echo ($stream_mount_start); ?></td></tr>
<tr><td>Bitrate</td><td><?php echo ($stream_bit_rate); ?></td></tr>
<tr><td>Current listners</td><td><?php echo ($listeners); ?></td></tr>
<tr><td>Peak listeners</td><td><?php echo ($peak_listeners); ?></td></tr>
<tr><td>Genre</td><td><?php echo ($stream_genre); ?></td></tr>
<tr><td>Station URL</td><td><?php echo ($stream_url); ?></td></tr>
<tr><td>Now palying</td><td><?php echo ($current_artist_song); ?></td></tr>
<tr><td>Artist</td><td><?php echo ($artist); ?></td></tr>
<tr><td>Track</td><td><?php echo ($current_song); ?></td></tr>
<tr><td>Album art URL (small)</td><td><?php echo ($album_art_small); ?></td></tr>
<tr><td>Album art URL (medium)</td><td><?php echo ($album_art_medium); ?></td></tr>
<tr><td>Album art URL (large)</td><td><?php echo ($album_art_large); ?></td></tr>
<tr><td>Album art URL (extralarge)</td><td><?php echo ($album_art_extralarge); ?></td></tr>
<tr><td>Track URL on last.fm</td><td><?php echo ($track_lastfm_url); ?></td></tr>
<tr><td>Artist URL on last.fm</td><td><?php echo ($artist_lastfm_url); ?></td></tr>
<tr><td>Album title</td><td><?php echo ($album_title); ?></td></tr>
<tr><td>Album URL on last.fm</td><td><?php echo ($album_lastfm_url); ?></td></tr>
<tr><td>Download link on Google Music(China)</td><td><?php echo ($track_download); ?></td></tr>
<tr><td>Local cache of album art</td><td><?php echo ($local_album_art_uri); ?></br>
<tr><td>Track summary</td><td><?php echo ($track_summary); ?></td></tr>
<tr><td>Track introduction</td><td><?php echo ($track_info); ?></td></tr>
<tr><td>Album summary</td><td><?php echo ($album_summary); ?></td></tr>
<tr><td>Album information</td><td><?php echo ($album_info); ?></td></tr>
<tr><td>Album release date</td><td><?php echo ($album_releasedate); ?></td></tr>
<tr><td>Lyric of the current song</td><td><?php echo ($track_lyric); ?></td></tr>
<tr><td>Album track list</td><td><?php foreach ($track_list as $track) {
    echo ($track . '<br />');
} ?></td></tr>
<tr><td>Artist summary</td><td><?php echo ($artist_summary); ?></td></tr>
<tr><td>Artist information</td><td><?php echo ($artist_info); ?></td></tr>
<tr><td>Artist album list</td><td><?php foreach ($album_list as $album) {
    echo ($album . '<br />');
} ?></td></tr>

</table>
</body>
</html>

