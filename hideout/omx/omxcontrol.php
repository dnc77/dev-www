<?

// Config file reading.
function readConfig($configFile)
{
   $arr = parse_ini_file($configFile);
   $_SESSION['ctrlPlay'] = $arr['playing'];
   $_SESSION['ctrlLoop'] = $arr['loop'];
   $_SESSION['ctrlShuffle'] = $arr['shuffle'];

   // Check if omxplayer is active. If it is, then change
   // state to playing and re-sync config file.
   $_SESSION['curPid'] = shell_exec('pidof omxplayer.bin');
   if ($_SESSION['curPid'] > 0 ) {
      $_SESSION['ctrlPlay'] = 1;
      writeConfig($configFile);
   } else {
      // No omxplayer running. Turn playback off.
      if ($_SESSION['ctrlPlay'] == 1) {
         $_SESSION['ctrlPlay'] = 0;
         $_SESSION['curPid'] = '';
         writeConfig($configFile);
      }
   }
}

// Config file writing.
function writeConfig($configFile)
{
   $file = fopen($configFile, 'wt');
   fprintf($file, "playing=%d\n", $_SESSION['ctrlPlay']);
   fprintf($file, "loop=%d\n", $_SESSION['ctrlLoop']);
   fprintf($file, "shuffle=%d\n", $_SESSION['ctrlShuffle']);
   fclose($file);
}

function omxStop()
{
   // First get most recent pid.   
   $_SESSION['curPid'] = shell_exec('pidof omxplayer.bin');

   // Then kill it.
   posix_kill($_SESSION['curPid'], 9);
   $_SESSION['curPid'] = '';
}


// Returns 1 if omxplayer is loaded or 0 otherwise.
// OBSOLETE
function isOmxPlaying()
{
   // No process id?
   if ($_SESSION['curPid'] == '') return 0;

   // Ensure recorded session is actually an omxplayer process.
   $cmd = shell_exec('ps -o cmd hp ' . $_SESSION['curPid']);
   if (false === stristr($cmd, 'omxplayer.bin')) {
      // Invalid PID!
      $_SESSION['curPid'] = '';
      return 0;
   }

   // Valid session running.
   return 1;
}

// Plays a file only if an existing process is not already
// running.
// Important notes to ensure successful execution:
// 1: /dev/vchiq must have rwx access to www user.
// 2: www user should also be in the group 'audio'.
// 3: omx.sh script has /usr/bin as the default location of 
//    where omxplayer.bin is found. Please ensure omxplayer.bin
//    does actually reside in that location.
// $omxcmd is the actual omx command to run and any parameters to pass to it.
// $mediaFile is the audio file to play.
// $pidFile will hold the process id of the player. Session PID updated.
// Returns -1 if already playing a song.
// OBSOLETE
function playFile($omxcmd, $mediaFile, $pidFile)
{
   // Do not play anything if a session already exists.
   if (isOmxPlaying() == 1) return -1;

   // Run session capturing the pid along the way.
   $cmd = "$omxcmd $mediaFile &";
   $_SESSION['curPid'] = rtrim(shell_exec($cmd));
   return 1;
}

// Kills the running omx Process and empties the pid file.
// OBSOLETE
function stopFile($pidFile)
{
   if ($_SESSION['curPid'] != '') {
      posix_kill($_SESSION['curPid'], 9);
      $_SESSION['curPid'] = '';
      $_SESSION['omxNowPlaying'] = '';
   
      shell_exec("echo '' > $pidFile");
   }
}

?>
