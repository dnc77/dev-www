<?
// Read all config options.
session_start();

// Session variables:
// curAudioDir: current directory the page is reading from
// ctrlPlay: 1 = omxplayer is playing 0 = otherwise
// ctrlShuffle: 1 = shuffle is on 0 = off
// ctrlLoop: 1 = loop through playlist 0 = do not loop
?>


<!DOCTYPE HTML>
<html>
<head>
   <link rel="stylesheet" type="text/css" href="../main.css"></link>
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

// First read current configuration.
readConfig($defPlayConfig);

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

      // When the playlist is cleared, make sure playing is stopped.
      $_SESSION['ctrlPlay'] = 0;
      writeConfig($defPlayConfig);
   }

   // Control command - play/stop.
   if ($_POST['ctrlplay']) {
      if ($_POST['ctrlplay'] == 'play') {
         // Don't do anything if playing already.
         if ($_SESSION['ctrlPlay'] == 0) {
            // Update config and start playing.
            $_SESSION['ctrlPlay'] = 1;
            writeConfig($defPlayConfig);
            $cmd = 'nohup ' . 
               '"' . $cmdPlayControl . '" ' .
               '"' . $defPlaylistFile . '" > ' .
               '"' . $defLogFile . '" &';
            shell_exec($cmd);
         }
      } else {
         // Update config, and stop playing.
         $_SESSION['ctrlPlay'] = 0;
         writeConfig($defPlayConfig);
         omxStop();
      }
   }

   // Control command - next song.
   if ($_POST['ctrlnext']) {
      // Stop current song. May kick off another instance
      // for next song.
      omxStop();
   }

   // Control command - shuffle/unshuffle.
   if ($_POST['ctrlshuffle']) {
      if ($_POST['ctrlshuffle'] == 'shuffle') {
         // Shuffle on.
         $_SESSION['ctrlShuffle'] = 1;
         writeConfig($defPlayConfig);
      } else {
         // Shuffle off.
         $_SESSION['ctrlShuffle'] = 0;
         writeConfig($defPlayConfig);
      }
   }

   // Control command - loop/unloop.
   if ($_POST['ctrlloop']) {
      if ($_POST['ctrlloop'] == 'loop') {
         // Loop on.
         $_SESSION['ctrlLoop'] = 1;
         writeConfig($defPlayConfig);
      } else {
         // Loop off.
         $_SESSION['ctrlLoop'] = 0;
         writeConfig($defPlayConfig);
      }
   }
}
?>

<?
   // Determine current audio dir.
   if (strlen($_SESSION["curAudioDir"]) == 0) {
      $_SESSION["curAudioDir"] = $defAudioDir;
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

<p><a href=../index.php>back to main</a></p>

</body>
</html>

