<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<title>Icecast Now Playing Script</title>
</head>
<body>
<p>
Code by Jude (<a href="mailto:surftheair@gmail.com">surftheair@gmail.com</a>)
</p>
<?php include("icecast.php");?>

<!--Edit the followings as you like, this is just an example -->
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
Album art URL: <?php echo($album_art);?><br />
Track introduction: <?php echo($track_info);?><br />
Track URL on last.fm: <?php echo($track_lastfm_url);?><br />
Artist URL on last.fm: <?php echo($artist_lastfm_url);?><br />
Album title: <?php echo($album_title);?><br />
Album URL on last.fm: <?php echo($album_lastfm_url);?><br />
Download link on Google Music(China): <?php echo($track_download);?><br />
Local cache of album art: <?php echo($local_album_art_uri);?></br>
Lyric of the current song: <?php echo($track_lyric);?></br>
</body>
</html>

