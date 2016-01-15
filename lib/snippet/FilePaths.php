<?php

namespace bmca\snippet{
	
	// Import Dependencies
	require 'vendor/autoload.php';

	// Class to make it compatible with php < 5.6
	class FilePaths
	{
		// Performs glob recursively
		public static function rglob($pattern, $NO_DIR = false, &$flags = 0)
		{
			// Run the initial glob
			$files = [];

			//echo $pattern.'<br>';
			//var_dump(glob($pattern . (($pattern[strlen($pattern) - 1] != '/' && strpos($pattern, '*') === false) ? '/*' : '*'), GLOB_NOSORT | GLOB_MARK));echo '<br>';
			// Append each file result, recursively call rglob with each directory result
			foreach (glob($pattern . (($pattern[strlen($pattern) - 1] != '/' && strpos($pattern, '*') === false) ? '/*' : '*'), GLOB_NOSORT | GLOB_MARK) as $path) {
				// Recursive call if a directory, otherwise just add the path
				if (substr($path, -1) == '/') {
					if ($NO_DIR === false) {
						$files[] = $path;
						$files = array_merge($files, self::rglob($path, $flags));
					} else {
						$files = array_merge($files, self::rglob($path, $flags));
					}
				} else {
					$files[] = $path;
				}
			}

			// Return the complete list of files
			return $files;
		}
	}
}