Currently this script has the following features, will add more in the future:

  * Get the basic information of a given Icecast mount point
  * Server status: ON AIR or OFF AIR
  * Get extra information of the current song using last.fm‘s API:
    * Album art image in different size
    * Introduction and links of the current song, album and artist
  * Buy links on Amazon, iTunes and 7digital
    * Track list of the current album
    * Album list of the current artist
    * Cache album art images to local server
    * Get lyric of the current song using chartlyrics.com's API
    * Store all paly records to local history files for future using
    * Cache all the variables above to a local file, so no need to fetch API everytime the client send a request until the current song has changed. Fetching remote API too frequesntly will result in baning
    * Options to enable or disable certain feature(for cutting down the time the script need to process)
    * Auto refresh the now playing section(div) every several second in a HTML page

See this wiki page for how to use: http://code.google.com/p/icecast-now-playing-script/wiki/How_to_use

A nice demo page: http://jude.me/now/

Please leave comment or ask question also on the link above
