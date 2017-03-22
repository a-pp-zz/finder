<?php
namespace AppZz\Helpers\Finder;
use RecursiveFilterIterator;
use AppZz\Helpers\Arr;
use AppZz\Helpers\Finder;

class FilterIterator extends RecursiveFilterIterator {

	public function accept()
	{
		$current_file = $this->current()->getRealPath();
		$filename     = $this->current()->getFilename();
		$path         = $this->current()->getPath();

		$include_names = Arr::get(Finder::$filter, 'include_names');
		$exclude_names = Arr::get(Finder::$filter, 'exclude_names');
		$include_paths = Arr::get(Finder::$filter, 'include_paths');
		$exclude_paths = Arr::get(Finder::$filter, 'exclude_paths');
		$types		   = Arr::get(Finder::$filter, 'types');
		$hidden_files  = Arr::get(Finder::$filter, 'hidden_files', FALSE);

		if ($this->current()->isFile())
		{			
			if ($hidden_files !== TRUE AND preg_match ('#^\..*#', $filename))
				return FALSE;

			if ($include_names)
				return preg_match ($include_names, $filename);

			if ($exclude_names)
				return !preg_match ($exclude_names, $filename);

			if ($include_paths)
				return preg_match ($include_paths, basename($path));

			if ($exclude_paths)
				return ! preg_match ($exclude_paths, basename($path));						  	
			
			if ($types) {
				$types = "#.*\.(" . implode ('|', $types) . ")$#iu";
				return preg_match ($types, $filename);
			}

			return TRUE;
		}	  	  	
	  	
	  	return TRUE;	  			  
	}
}