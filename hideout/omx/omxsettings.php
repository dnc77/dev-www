<?
// Defaults
$defHomeDir = '/srv/www/omx/';
$defWorkDir = $defHomeDir . 'data/';
$defCmdDir = $defHomeDir . 'scripts/';
$defAudioDir = '/mnt/usb/crystal/_disc/entertainment/audio/';

// Default files
$defPidFile = $defWorkDir . 'omx.pid';
$defLogFile = $defWorkDir . 'omx.log';
$defPlaylistFile = $defWorkDir . 'omx.m3u8';
$defPlayConfig = $defWorkDir . 'config.txt';

// Commands
$cmdGetDir = $defCmdDir . 'getdirs.sh';
$cmdGetFiles = $defCmdDir . 'getfiles.sh';
$cmdPlayControl = $defCmdDir . 'playcontrol.sh';
$cmdOmx = $defCmdDir . 'omx.sh';

// Additional options
$optPlayDevice = 'alsa';
?>

