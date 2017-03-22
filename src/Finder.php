<?php
namespace AppZz\Helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AppZz\Helpers\Finder\SorterIterator;
use AppZz\Helpers\Finder\FilterIterator;

/**
 * Search files, filter by types, sort items, ignore or not hidden files
 */
class Finder {
	
	/**
	 * Directory Operator Instance
	 * @var object
	 */
	private $_dit;
	
	/**
	 * Recursicive Iterator Instance
	 * @var object
	 */
	private $_rit;
	
	/**
	 * Max depth
	 * @var integer
	 */
	private $_max_depth = -1;
	
	/**
	 * Filter params
	 * @var array
	 */
	public static $filter = array (
		'types'         => NULL,
		'include_names' => NULL,
		'exclude_names' => NULL,
		'include_paths' => NULL,
		'exclude_paths' => NULL,
		'hidden_files'  => FALSE,
	);

	/**
	 * Attributes arr owner|group|perms|atime|mtime|ctime|size|gid|uid etc
	 * @var array
	 */
	private $_attrs = array ();

	public function __construct ($path, $max_depth = -1)
	{
		$this->_dit       = new RecursiveDirectoryIterator ($path);
		$this->_max_depth = $max_depth;
	}

	public static function factory ($path, $depth = -1)
	{
		return new Finder ($path, $depth);
	}

	public function attrs (array $attrs = array())
	{
		$this->_attrs = $attrs;
		return $this;
	}

	public function filter ($option, $value = NULL)
	{
		if ($option)
		{
			switch ($option)
			{
				case 'types':
					
					if (is_string ($value) AND strpos ($value, ','))
					{
						$value = explode (',', $value);
						$value = array_map ('trim', $value);				
					}
					
					$value = (array) $value;
				break;

				case 'hidden_files':	
					$value = (bool) $value;
				break;
				
				default:
					$value = (string) $value;
				break;		
			}
			
			Finder::$filter[$option] = $value;
		}
		
		return $this;
	}			

	public function find ($sortby = NULL, $asc = TRUE)
	{
		$this->_dit = new FilterIterator ($this->_dit);
		$this->_rit = new RecursiveIteratorIterator ($this->_dit, RecursiveIteratorIterator::SELF_FIRST);
		$this->_rit->setMaxDepth ($this->_max_depth);
		
		if ($sortby)
		{
			$this->_rit = new SorterIterator ($this->_rit, $sortby, $asc);	
		}
		
		return $this->_get();		
	}
	
	/**
	 * [Generic get objects by RiT]
	 * @param  RecursiveIteratorIterator $rit
	 * @param  mixed                     $attrs
	 * @return array                     $files
	 */
	private function _get ()
	{
		if ( ! empty ($this->_rit))
		{
			$files = array ();
			
			foreach ($this->_rit as $filePath => $fileInfo)
			{
				if ($fileInfo->isFile())
				{
					if (empty ($this->_attrs))
					{
						$files[] = $fileInfo->getRealPath ();
					}
					else
					{
						$obj = new \stdClass;
						$obj->path = $fileInfo->getRealPath ();
						$obj->attrs = array ();
						
						foreach ($this->_attrs as $attr)
						{
							$attr = trim ($attr);
							$method = 'get' . mb_convert_case($attr, MB_CASE_TITLE);
							
							if (method_exists ($fileInfo, $method))
							{
								$value = call_user_func (array (&$fileInfo, $method));
							}
							else 
							{
								continue;
							}
							
							switch ($attr)
							{
								case 'owner':
									$value = posix_getpwuid ($value);
								break;

								case 'group':
									$value = posix_getgrgid ($value);
								break;																								
								
								case 'perms':
									$value = substr (sprintf ('%o', $value), -4);
								break;																								
							}

							$obj->attrs[$attr] = $value;
						}

						$files[] = $obj;
						unset ($obj);
					}
				}
			}

			return $files;			
		}
		
		return FALSE;
	}	
}