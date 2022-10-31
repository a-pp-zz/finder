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
		$fullpath  = $this->current()->getRealPath();
		$filename  = $this->current()->getFilename();
		$dir       = $this->current()->getPath();
		$filemtime = $this->current()->getMTime();

		$types         = Arr::get(Finder::$filter, 'types');
		$type          = Arr::get(Finder::$filter, 'type');
		$search        = Arr::get(Finder::$filter, 'search');
		$exclude       = Arr::get(Finder::$filter, 'exclude');
		$exclude_paths = Arr::get(Finder::$filter, 'exclude_paths');
		$hidden        = Arr::get(Finder::$filter, 'hidden', FALSE);
		$mtime         = Arr::get(Finder::$filter, 'mtime', array());

		$p0 = $p1 = $p2 = $p3 = $p4 = $p5 = TRUE;

		$check = call_user_func ([$this->current(), 'is'.mb_convert_case ($type, MB_CASE_TITLE)]);

		if ($check)
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

			if ( ! empty ($mtime) AND is_array ($mtime)) {
				if (count($mtime) === 3) {
					list ($ts_1, $ts_2, $cmp) = $mtime;

					if ($ts_1 AND ! is_numeric($ts_1)) {
						$ts_1 = strtotime ($ts_1);
					}

					if ($ts_2 AND ! is_numeric($ts_2)) {
						$ts_2 = strtotime ($ts_2);
					}

					switch ($cmp) :
						case 'newer':
							$p5 = ($filemtime > $ts_1);
						break;
						case 'newera':
							$p5 = ($filemtime >= $ts_1);
						break;
						case 'older':
							$p5 = ($filemtime < $ts_1);
						break;
						case 'oldera':
							$p5 = ($filemtime <= $ts_1);
						break;
						case 'between':
							$p5 = (($filemtime > $ts_1) AND ($filemtime < $ts_2));
						break;
						case 'betweena':
							$p5 = (($filemtime >= $ts_1) AND ($filemtime <= $ts_2));
						break;
					endswitch;
				}
			}

			return ($p0 AND $p1 AND $p2 AND $p3 AND $p4 AND $p5);
		}

	  	return TRUE;
	}
}
