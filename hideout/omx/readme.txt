omx

Summary:
omx is a very quick and bare bones www interface to a linux based command line
media player; omxplayer.

This has been written for my own personal needs and should not be treated as a
final product as it was written with the intention of getting something up
started quickly.

Having said that, this may be re-worked to provide further support which may
include extended functionality as well as graphics. These are features which I
do not need so they were not added on due to time constraints.

How it works:
omx was written with Javascript and PHP version 7.2 and needs them along with
omxplayer to be set up on your linux web server.

The player works by running omxplayer, storing it's pid and killing it when not
needed. It stores a list of files (the current OMX Playlist) to identify which
song to play next in a file.

Support for m3u8 playlists and mp3 files exists.

Please note that at the time of writing, this version was set up and working on
a Raspberry Pi 2 with Raspbian installed but should work with other Linux
distributions.

Please note that omxsettings.php has some important settings that need to be set
up for this to work.

The following in your linux system should also be checked:
1: /dev/vchiq must have rwx access to www user.
2: www user should also be in the group 'audio'.
3: omx.sh script has /usr/bin as the default location of
   where omxplayer.bin is found. Please ensure omxplayer.bin
   does actually reside in that location.

How to use:
After all set up is done as explained in 'How it works', the page will present
itself with a list of directories, playlists and files. 

Clicking on any directory will open that directory.
Clicking on any playlist (in the current directory) will add the playlist to the
current OMX Playlist.
Clicking on any mp3 file will add that file to the current OMX Playlist.

The OMX Playlist is the active playlist being used by OMX.

Features to play, stop, move to the next track, shuffle and loop are supported
in the Control panel below.


In case of any difficulties, please do not hesitate to get in touch.

Thanks

Duncan Camilleri
