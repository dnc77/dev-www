#!/bin/bash
IFS=$(echo -en "\r\n\b")
pwd=$(pwd)

PLAYLISTFILE="$1"
CONFIGFILE="/srv/www/omx/data/config.txt"
OMX="/srv/www/omx/scripts/omx.sh"
PIDFILE="/srv/www/omx/data/omx.pid"
PID=
eval `cat $CONFIGFILE`

# Is playback enabled?
#if [ $playing -eq 0 ]; do
   # Nothing to do since no playback desired.
#   return
#done

# Just loop through the contents of the playlist.
until [ $playing -eq 0 ]; do
   # Variable determines if seqchange sequence/shuffle shifted
   seqchange=0

   # Get playlist count.
   count=`wc -l "$PLAYLISTFILE"`
   count=${count%%\ *}

   # Shuffle or play?
   if [ $shuffle -eq 1 ]
   then 
      n=0
      while [ $n -lt $count -a $shuffle -eq 1 -a $playing -eq 1 ]
      do
         # Play one song.
         PLAYTHING=`shuf -n 1 "$PLAYLISTFILE"`
         PID=`$OMX -o alsa "$PLAYTHING"`;
         n=$((n+1));
        
         # Record PID and reread config values.
         echo $PID > $PIDFILE
         eval `cat $CONFIGFILE`

         # Check seqchange change request.
         if [ $shuffle -eq 0 ]
         then
            seqchange=1
         fi

         # Check for stop playing.
         if [ $playing -eq 0 ]
         then
            break
         fi
      done
   else
      # Play each song.
      for x in `cat $pwd/$1`; do
         # Play one.
         PID=`$OMX -o alsa "$x"`

         # Record PID and reread config values.
         echo $PID > $PIDFILE
         eval `cat $CONFIGFILE`

         # Check for seqchange change.
         if [ $shuffle -eq 1 ]
         then
            seqchange=1
            break
         fi

         # Check for stop playing.
         if [ $playing -eq 0 ]
         then
            break
         fi
      done
   fi

   # No loop. Update status.
   if [ $loop -eq 0 -a $seqchange -eq 0 ]
   then
      echo "playing=0" > $CONFIGFILE
      echo "loop=$loop" >> $CONFIGFILE
      echo "shuffle=$shuffle" >> $CONFIGFILE
   fi

   # Review config values.
   seqchange=0
   eval `cat $CONFIGFILE`
done

