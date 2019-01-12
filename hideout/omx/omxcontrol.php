<?

// Returns 1 if omxplayer is loaded or 0 otherwise.
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
function playFile($omxcmd, $mediaFile, $pidFile)
{
   // Do not play anything if a session already exists.
   if (isOmxPlaying() == 1) return -1;

   // Run session caoturing the pid along the way.
   $cmd = "$omxcmd $mediaFile &";
   $_SESSION['curPid'] = rtrim(shell_exec($cmd));
   return 1;
}

// Kills the running omx Process and empties the pid file.
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
