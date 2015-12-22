How to use the script on your own site:

  * Get an API key from last.fm: http://www.last.fm/api/account
  * Get the script, edit the config file (config.php)
  * Use the available variables to echo what you want, there's also a list of available variables on this wiki page: http://code.google.com/p/icecast-now-playing-script/wiki/Variables
  * Upload the scripts to your webspace which support PHP, Change the attribute of the script directory to be writable("666" for example)

Now you can call the example.php file in any HTML page, to auto refresh the now playing section, use the following javascript:

```
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript">
</script>
<script type="text/javascript">
function nowplaying(){ 
  $.ajax({
  timeout: 10000,
  url: "/radioswisspop.php", 
  cache: false,
  success: function(html){ 
   $("#nowplaying").html(html); 
  }
 }); 
}
nowplaying();
setInterval( "nowplaying()", 10000 ); 
</script>
<div id="nowplaying"></div>
```