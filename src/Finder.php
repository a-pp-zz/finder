<?php
namespace AppZz\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AppZz\Filesystem\Finder\SorterIterator;
use AppZz\Filesystem\Finder\FilterIterator;

/**
 * Search files, filter by types, sort items, ignore or not hidden files
 *
 * @package AppZz\Filesystem
 * @author CoolSwitcher
 * @team AppZz
 * @version 2.0.1
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
		'hidden'  => FALSE,
	);

	/**
	 * Attributes array owner|group|perms|atime|mtime|ctime|size|gid|uid etc
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

	/**
	 * Find files
	 * @return $this
	 */
	public function find ()
	{
		$this->_dit = new FilterIterator ($this->_dit);
		$this->_rit = new RecursiveIteratorIterator ($this->_dit, RecursiveIteratorIterator::SELF_FIRST);
		$this->_rit->setMaxDepth ($this->_max_depth);
		return $this;
	}

	/**
	 * Sort files
	 * @param string $sortby name|type|date|size
	 * @param bool $asc sort order by asc
	 * @return $this
	 */
	public function sort ($sortby = NULL, $asc = TRUE)
	{
		if (in_array($sortby, array('name', 'type', 'date', 'size')))
		{
			$this->_rit = new SorterIterator ($this->_rit, $sortby, $asc);
		}

		return $this;
	}

	/**
	 * Get files as array
	 * @param array $attrs
	 * @return array|bool
	 */
	public function get_files (array $attrs = array ())
	{
		if ( ! empty ($this->_rit))
		{
			$files = array ();

			foreach ($this->_rit as $filePath => $fileInfo)
			{
				if ($fileInfo->isFile())
				{
					if (empty ($attrs))
					{
						$files[] = $fileInfo->getRealPath ();
					}
					else
					{
						$obj = new \stdClass;
						$obj->path = $fileInfo->getRealPath ();

						foreach ($attrs as $attr)
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

							$obj->$attr = $value;
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

	/**
	 * Include hidden files or not
	 * @param bool $hidden
	 * @return $this
	 */
	public function hidden ($hidden = FALSE)
	{
		$this->_filter ('hidden', (bool) $hidden);
		return $this;
	}

	/**
	 * Include files contained text
	 * @param string $text
	 * @return $this
	 */
	public function search ($text = '')
	{
		$this->_filter ('search', $text);
		return $this;
	}

	/**
	 * Include types
	 * @param array $types
	 * @return $this
	 */
	public function types ($types = array())
	{
		$this->_filter ('types', $types);
		return $this;
	}

	/**
	 * Exclude filenames
	 * @param string $text
	 * @return $this
	 */
	public function exclude ($text = '')
	{
		$this->_filter ('exclude', $text);
		return $this;
	}

	/**
	 * Exclude paths
	 * @param string $text
	 * @return $this
	 */
	public function exclude_paths ($text = '')
	{
		$this->_filter ('exclude_paths', $text);
		return $this;
	}

	/**
	 * Filter setter
	 * @param $option
	 * @param mixed $value
	 * @return $this
	 */
	private function _filter ($option, $value = NULL)
	{
		if ($option)
		{
			switch ($option)
			{
				case 'types':
					$value = (array) $value;
					$value = "#.*\.(" . implode ('|', $value) . ")$#iu";
				break;

				case 'hidden':
					$value = (bool) $value;
				break;

				case 'search':
				case 'exclude':
				case 'exclude_paths':
					$value = (string) $value;
					$value = '#' . $value . '#iu';
				break;

				default:
					$value = FALSE;
				break;
			}

			if ($option AND $value) {
				Finder::$filter[$option] = $value;
			}
		}

		return $this;
	}
}
