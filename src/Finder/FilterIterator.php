<?php
namespace AppZz\Filesystem\Finder;
use RecursiveFilterIterator;
use AppZz\Helpers\Arr;
use AppZz\Filesystem\Finder;

/**
 * Class FilterIterator
 * @package AppZz\Filesystem\Finder
 * @author CoolSwitcher
 * @team AppZz
 * @version 2.0
 */
class FilterIterator extends RecursiveFilterIterator {

	public function accept()
	{
		$fullpath = $this->current()->getRealPath();
		$filename = $this->current()->getFilename();
		$dir      = $this->current()->getPath();

		$types         = Arr::get(Finder::$filter, 'types');
		$search        = Arr::get(Finder::$filter, 'search');
		$exclude       = Arr::get(Finder::$filter, 'exclude');
		$exclude_paths = Arr::get(Finder::$filter, 'exclude_paths');
		$hidden        = Arr::get(Finder::$filter, 'hidden', FALSE);

		$p0 = $p1 = $p2 = $p3 = $p4 = TRUE;

		if ($this->current()->isFile())
		{
			if ($hidden !== TRUE AND preg_match ('#^\..*#', $filename)) {
				$p0 = FALSE;
			}

			if ($types) {
				$p1 = preg_match ($types, $filename);
			}

			if ($search) {
				$p2 = preg_match ($search, $filename);
			}

			if ($exclude) {
				$p3 = ! preg_match ($exclude, $fullpath);
			}

			if ($exclude_paths) {
				$p4 = ! preg_match ($exclude_paths, $dir);
			}

			return ($p0 AND $p1 AND $p2 AND $p3 AND $p4);
		}

	  	return TRUE;
	}
}
