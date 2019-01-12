#!/bin/bash

#
# Generic globals
#
OLDIFS=$IFS
IFS=$'\n'

PROGNAME=getfiles

# error codes
ERRSYNTAX=1


#
# USAGE
#
function usage {
   echo "Usage: $PROGNAME"
   echo "$PROGNAME <searchdir> <fileext> [xmltags]"
   echo
   echo -n  "<searchdir>:  "
   echo     "a directory which has m3u8 playlists"
   echo -n  "<fileext>:  "
   echo     "a file extension to consider as valid. ex: m3u8"
   echo -n  "[xmltags]:    "
   echo     "a list of xml tags to enclose each directory by"
   echo -n  "              "
   echo     "use as follows \"\`echo -e \"tag1\ntag2\"\`\""
   echo
}

#
# XML TAGS
#

# xml open and close tags
xmlotag=
xmlctag=

# Sets xmlotag and xmlctag based on $1
# $1 one or more tags.
# With tag lists, use "`echo -e "tag1\ntag2"`" in command line.
function setXMLTags {
   for x in $1; do
      xmlotag=$xmlotag\<$x\>
      xmlctag=\</$x\>$xmlctag
   done;
}

#
# DIRECTORY FETCH
#

# Outputs all playlists in $1. 
# Considers $xmlotag and $xmlctag.
function showPlaylists {
   # To show all playlists in a folder, the command is:
   # ls -1 -d <folder>/*.m3u8

   # Set parent dir ending in '/'
   if [ ${1: -1} != '/' ]; then parent=$1/; else parent=$1; fi

   # Get all dirs in parent dir.
   search=$parent\*.$fileext

   # Truncate stuff before last '/' and extension.
   for x in `ls -1 $search 2>/dev/null`; do
      playlist=${x%\.$fileext}
      playlist=${playlist##*/}
      echo $xmlotag$playlist$xmlctag
   done
}

#
# MAIN
#

# TODO: This is not quite right yet...
if [[ -z "$1" || "$1" =~ [-/]h || "$1" =~ [-/]help || "$1" =~ [-/]\? ]]; then
   usage
   return $ERRSYNTAX 2>/dev/null || exit $ERRSYNTAX
fi

rootdir=$1
fileext=$2
setXMLTags "$3"
showPlaylists $rootdir

#
# RESET
#
IFS=$OLDIFS
