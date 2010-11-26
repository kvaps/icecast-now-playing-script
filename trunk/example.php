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
<table border="1">
<tr><td>$status</td><td><?php echo ($status);?></td></tr>
<?php
foreach($stream_info as $key => $value){
	echo '<tr><td>$stream_info[\''.$key.'\']</td><td>';
	if(is_array($value)){
		foreach($value as $key_ => $value_){
			echo $value_.'<br />';
		}
	}
	else{
		echo $value;
	}
	echo '</td></tr>';
}
foreach($track_info as $key => $value){
	echo '<tr><td>$track_info[\''.$key.'\']</td><td>';
	if(is_array($value)){
		foreach($value as $key_ => $value_){
			echo $value_.'<br />';
		}
	}
	else{
		echo $value;
	}
	echo '</td></tr>';
}
foreach($album_info as $key => $value){
	echo '<tr><td>$album_info[\''.$key.'\']</td><td>';
	if(is_array($value)){
		foreach($value as $key_ => $value_){
			echo $value_.'<br />';
		}
	}
	else{
		echo $value;
	}
	echo '</td></tr>';
}
foreach($artist_info as $key => $value){
	echo '<tr><td>$artist_info[\''.$key.'\']</td><td>';
	if(is_array($value)){
		foreach($value as $key_ => $value_){
			echo $value_.'<br />';
		}
	}
	else{
		echo $value;
	}
	echo '</td></tr>';
}
?>
<tr><td>$source</td><td><?php echo $source;?></td></tr>
</table>
</body>
</html>

