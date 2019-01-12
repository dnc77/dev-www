#!/bin/bash

#
# Generic globals
#
OLDIFS=$IFS
IFS=$'\n'

PROGNAME=getdir

# error codes
ERRSYNTAX=1

#
# USAGE
#
function usage {
   echo "Usage: $PROGNAME"
   echo "$PROGNAME <searchdir> [xmltags]"
   echo
   echo -n  "<searchdir>:  "
   echo     "a directory where to use to list all containing directories"
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

# Outputs all directories in $1. 
# Considers $xmlotag and $xmlctag.
function showDirs {
   # To show all directories in a folder, the command is:
   # ls -1 -d <folder>/*/

   # Set parent dir ending in '/'
   if [ ${1: -1} != '/' ]; then parent=$1/; else parent=$1; fi

   # Since we are showing only directory names, truncate stuff before last '/'.
   for x in `ls -1 -d ${1%/*}/*/ 2>/dev/null`; do
      dir=${x%/}
      dir=${dir##*/}
      echo $xmlotag$dir$xmlctag
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
setXMLTags "$2"
showDirs $rootdir


#
# RESET
#
IFS=$OLDIFS
