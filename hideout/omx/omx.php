<?
// Read all config options.
session_start();

// Session variables:
// curAudioDir: current directory the page is reading from
// ctrlPlay: 1 = omxplayer is playing 0 = otherwise
// ctrlShuffle: 1 = shuffle is on 0 = off
// ctrlLoop: 1 = loop through playlist 0 = do not loop
// curPid: process id of running omxplayer
// omxPlayOrder: order of items played out of count (not index)
// omxPlayNextIndex: index of next item in playlist to play
// omxPlaylistCount: number of playlist items loaded
// omxNowPlaying: name of file playing now
?>

<?
//
// PHP HELPERS
//

// Takes into account session variables omxPlayOrder and
// omxPlaylistCount as well as other omx options to determine
// what next index to fetch from the list. -1 means nothing
// more to play.
function getNextPlaylistIndex()
{
   // If current play order reached count, potential loop.
   if ($_SESSION['omxPlayOrder'] == $_SESSION['omxPlaylistCount']) {
      // Since the order has reached it's limit, it now needs to
      // be reset to 0 indicating that no song is currently playing.
      $_SESSION['omxPlayOrder'] = 0;
      if ($_SESSION['ctrlLoop'] == 0) {
         // Nothing more to play.
         return -1;
      }
   }

   // Next play list item can be fetched.
   if ($_SESSION['ctrlShuffle'] == 1) {
      // If we're shuffling, generate a random index.
      srand(time());
      $index = rand(0, $_SESSION['omxPlaylistCount'] - 1);
   } else {
      // If we are not shuffling, use the play order to determine
      // The index of the song to play next. Since play order is
      // actually not an index and since we are deducing the *next*
      // song to play, then the next play index can be the order of
      // the current song.
      $index = $_SESSION['omxPlayOrder'];
   }

   return $index;
}

?>

<!DOCTYPE HTML>
<html>
<head>
   <link rel="stylesheet" type="text/css" href="../main.css"></link>
   <script>
      setTimeout(
         function() {
            window.location.href = window.location.href;
         },
         5000
      );
   </script>
</head>

<body>
<h1>omx</h1>
<!--
   omx is a simple gui for omxplayer to play audio files through the web.
-->

<?
include_once("omxsettings.php");
include_once("omxplaylist.php");
include_once("omxcontrol.php");

if (count($_POST) > 0) {
   // POST.

   // Directory selection.
   if ($_POST['directory']) {
      if ($_POST['directory'] == '../') {
         $_SESSION['curAudioDir'] = rtrim($_SESSION['curAudioDir'], '/');
         $offset = strrpos($_SESSION['curAudioDir'], '/');
         $_SESSION['curAudioDir'] = substr($_SESSION['curAudioDir'], 0, $offset + 1);
      } else {
         $_SESSION["curAudioDir"] = $_SESSION["curAudioDir"] . $_POST['directory'];
      }
   }

   // Playlist selection.
   if ($_POST['playlist']) {
      // Define the playlist filename to open.
      $filename = $_SESSION['curAudioDir'] . $_POST['playlist'] . '.m3u8';

      // Now read playlist from the temp file.
      $file = fopen($filename, 'r');
      if ($file) {
         while (($item = fgets($file)) !== false) {
            $item = ltrim(rtrim($item));
            $cmd = 'echo "' . $_SESSION['curAudioDir'] . $item .
                     '" >> ' . $defPlaylistFile;
            shell_exec($cmd);
         }

         // Done!
         fclose($file);    
      }
   }

   // Media selection.
   if ($_POST['mp3']) {
      // Just add the file to the temporary play list.
      $filename = '"' . $_SESSION['curAudioDir'] . $_POST['mp3'] . '.mp3"';
      shell_exec("echo $filename >> $defPlaylistFile");
   }

   // Clear omx playlist.
   if ($_POST['clearomxplaylist']) {
      shell_exec("echo -n '' > $defPlaylistFile");
   }

   // Control command - play/stop.
   if ($_POST['ctrlplay']) {
      if ($_POST['ctrlplay'] == 'play') {
         // If the user pressed play, and no current player running,
         // start playing the playlist.
         if (0 == isOmxPlaying()) {
            // Mark play index as 0 to suggest we are playing the first item.
            $_SESSION['omxPlayOrder'] = 0;

            // Identify the next item to play.
            $_SESSION['omxPlayNextIndex'] = getNextPlaylistIndex();
         }

         // Turn on play flag.
         $_SESSION['ctrlPlay'] = 1;
      } else {
         // Stop playing.
         if (1 == isOmxPlaying()) {
            stopFile($defPidFile);
         }

         // Clear omx playing flag.
         $_SESSION['ctrlPlay'] = 0;
      }
   }

   // Control command - next song.
   if ($_POST['ctrlnext']) {
      stopFile($defPidFile);

      // Identify the next item to play.
      $_SESSION['omxPlayNextIndex'] = getNextPlaylistIndex();
   }

   // Control command - shuffle/unshuffle.
   if ($_POST['ctrlshuffle']) {
      if ($_POST['ctrlshuffle'] == 'shuffle') {
         $_SESSION['ctrlShuffle'] = 1;
      } else {
         $_SESSION['ctrlShuffle'] = 0;
      }
   }

   // Control command - loop/unloop.
   if ($_POST['ctrlloop']) {
      if ($_POST['ctrlloop'] == 'loop') {
         $_SESSION['ctrlLoop'] = 1;
      } else {
         $_SESSION['ctrlLoop'] = 0;
      }
   }
}
?>

<?
   // Determine current audio dir.
   if (strlen($_SESSION["curAudioDir"]) == 0) {
      $_SESSION["curAudioDir"] = $defAudioDir;
   }

   // Determine running process pid (if it exists).
   if ($_SESSION['curPid'] == '') {
      $_SESSION['curPid'] = rtrim(shell_exec("cat $defPidFile"));
      if (1 == isOmxPlaying()) {
         $_SESSION['ctrlPlay'] = 1;
      } else {
         shell_exec("echo '' > $defPidFile");
         $_SESSION['omxNowPlaying'] = '';
      }
   } else {
      // If a process id exists but there is no valid omx running,
      // kick off omx with the next song!
      if (0 == isOmxPlaying()) {
         $_SESSION['omxNowPlaying'] = '';
         $_SESSION['omxPlayNextIndex'] = getNextPlaylistIndex();
      }
   }

   // Get a list of directories and playlists.
   $sourceDir = '"' . $_SESSION['curAudioDir'] . '"';
   $directories = '..' . "\n" . shell_exec($cmdGetDir . ' ' . $sourceDir);
   $playlists = shell_exec($cmdGetFiles . ' ' .  $sourceDir . ' m3u8');
   $mp3list = shell_exec($cmdGetFiles . ' ' . $sourceDir . ' mp3');

   // Split strings by \r and \n.
   $regsplit = "#[\r\n]+#";
   $directories = preg_split($regsplit, $directories);
   $playlists = preg_split($regsplit, $playlists);
   $mp3list = preg_split($regsplit, $mp3list);
?>

<form action="omx.php" method="post"><table class="listtable">
<tr><th>Directories</th><th>Playlists</th><th>Audio files</th></tr>
<?
   // Find largest list and use that as a reference for maximum count.
   $countDirs = count($directories) - 1;
   $countPlaylists = count($playlists) - 1;
   $countMp3 = count($mp3list) - 1;
   $count = $countDirs;
   if ($countPlaylists > $count) $count = $countPlaylists;
   if ($countMp3 > $count) $count = $countMp3;

   // Go through each entry and add a record.
   for ($n = 0; $n < $count; $n++) {
      $directory =   ($countDirs > $n) ? $directories[$n] . '/' : '';
      $playlist =    ($countPlaylists > $n) ? $playlists[$n] : '';
      $mp3 =         ($countMp3 > $n) ? $mp3list[$n] : '';
?>
<tr>
   <td><input type="submit" class="tabledata" name="directory" value="<? echo $directory; ?>"/></td>
   <td><input type="submit" class="tabledata" name="playlist" value="<? echo $playlist; ?>"/></td>
   <td><input type="submit" class="tabledata" name="mp3" value="<? echo $mp3; ?>"/></td>
</tr>
<?
   }
?>
<tr><td/><td/><td/></tr>
</table>
</form>

<form action="omx.php" method="post"><table class="listtable">
<tr><th>Current OMX Playlist</th></tr>
<?
   $omxPlaylist = createPlaylistItems($defPlaylistFile);
   foreach ($omxPlaylist as $item) {
      echo '<tr><td>' . basename($item) . '</td></tr>';
   }
?><!-- clear playlist and trailing rows -->
<tr><td> </td></tr>
<tr><td><input type="submit" class="tabledata" name="clearomxplaylist" value="Clear OMX Playlist"/></td></tr>
<tr><td> </td></tr>

<?
   // Record number of playlist items.
   $_SESSION['omxPlaylistCount'] = count($omxPlaylist);
?>
</table>
</form>

<form action="omx.php" method="post"><table class="listtable">
<tr><th>Control panel</th></tr>
<!-- play button --!>
<tr><td>
   <input type="submit" class="tabledata" name="ctrlplay" value="<?
      if ($_SESSION['ctrlPlay'] == 1) {
         echo 'stop';
      } else {
         echo 'play';
      }
   ?>"/>
</td></tr>
<!-- next button --!>
<?
   if ($_SESSION['ctrlPlay'] == 1) {
?>
<tr><td><input type="submit" class="tabledata" name="ctrlnext" value="next"/></td></tr>
<?
   }
?>

<!-- shuffle button --!>
<tr><td>
   <input type="submit" class="tabledata" name="ctrlshuffle" value="<?
      if ($_SESSION['ctrlShuffle'] == 1) {
         echo 'unshuffle';
      } else {
         echo 'shuffle';
      }
   ?>"/>
</td></tr>
<!-- loop button --!>
<tr><td>
   <input type="submit" class="tabledata" name="ctrlloop" value="<?
      if ($_SESSION['ctrlLoop'] == 1) {
         echo 'unloop';
      } else {
         echo 'loop';
      }
   ?>"/>
</td></tr>

<td><tr> </tr></td>
</table></form>

<?
   // Control loop here.

   // If a request has been made to play a next song file, play it.
   // This request is checked at the beginning of the post request.
   // If the play button is pressed, then the next song to play is calculated
   // at that point into _SESSION['omxPlayNextIndex'].
   if ($_SESSION['ctrlPlay'] == 1 && $_SESSION['omxPlayNextIndex'] > -1) {
      $playCmd = $cmdOmx . " -o $optPlayDevice";
      $playFile = '"' .
         $omxPlaylist[$_SESSION['omxPlayNextIndex']] .
         '"';
      $playing = playFile($playCmd, $playFile, $defPidFile);
      if ($playing > -1) {
         // Play request was successful.
         $playFile = basename($omxPlaylist[$_SESSION['omxPlayNextIndex']]);
         $_SESSION['omxNowPlaying'] = $playFile;
         $_SESSION['omxPlayNextIndex'] = -1; // do not play again until end
         $_SESSION['omxPlayOrder'] = $_SESSION['omxPlayOrder'] + 1;

         // Update current pid.
         $writePid = "echo '" . $_SESSION['curPid'] . "' > $defPidFile";
         shell_exec($writePid);
      }
   }
?>

<p>Status: <? echo ($_SESSION['ctrlPlay'] == 1) ? 'playing ' . $_SESSION['omxNowPlaying'] . ' (' . $_SESSION['omxPlayOrder'] . ')' : 'stopped'; ?></p>

<p><a href=../index.php>back to main</a></p>

</body>
</html>

