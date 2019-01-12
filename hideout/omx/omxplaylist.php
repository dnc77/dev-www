<?
// Implements omx playlist functionality
include_once("omxsettings.php");

// Returns a list of items from $filename.
function createPlaylistItems($filename)
{
   $list = array();
   
   // Try and read the filename provided line by line and treat each file as an
   // entry in the playlist.
   $file = fopen($filename, 'r');
   if (!$file) return $list;

   // Read.
   while (!feof($file)) {
      $item = trim(fgets($file));
      if (strlen($item) > 0) $list[] = $item;
   }

   // Close and return.
   fclose($file);
   return $list;
}

// Rewrites the playlist file with items.
function recreatePlaylistFile($filename, $items)
{
   // Open playlist file.
   $file = fopen($filename, 'wt');
   if (!$file) return false;

   // Write each item.
   foreach ($items as $item) {
      fprintf($file, '%s\n', $item);
   }

   // Close the file.
   fclose($file);
   return true;
}

// Adds item to filename.
function addItemToPlaylistFile($filename, $item)
{
   $item = trim($item);
   if (strlen($item) <= 0) return true;

   // Open file.
   $file = fopen($filename, 'at');
   if (!$file) return false;

   // Write and close.
   fprintf($file, '%s\n', $item);
   fclose($file);

   // Done.
   return true;
}

?>
