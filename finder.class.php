<?php
/**
 * Helper class for filter items
 */
class Finder_Filter_Iterator extends RecursiveFilterIterator {

		public static $types   = NULL;
		public static $names   = NULL;
		public static $ignores = NULL;
		public static $hidden  = TRUE;   

    public function accept() {

    		$current_file = $this->current()->getFilename();

    		$t1 = TRUE;
    		$t2 = TRUE;
    		$t3 = TRUE;
    		$t4 = TRUE;
			  
			  if ( $this->current()->isFile() ) {
    			
			  	if ( !empty ( self::$types ) ) {
			  		$types = "#.*\.(" . implode ('|', self::$types) . ")$#iu";
			  		$t1 = preg_match ( $types, $current_file );
			  	}
			  	if ( !empty ( self::$names ) ) {
			  		$names = "#(" . implode ('|', self::$names) . ")#iu";
			  		$t2 = preg_match ( $names, $current_file );
			  	}
			  	if ( !empty ( self::$ignores ) ) {
			  		$ignores = "#(" . implode ('|', self::$ignores) . ")#iu";
			  		$t3 = !preg_match ( $ignores, $current_file );
			  	}
				  if ( self::$hidden !== TRUE && preg_match ('#^\..*#', $current_file ) ) {
				    $t4 = FALSE;
				  }			  				  				  	
			  	return ( $t1 && $t2 && $t3 && $t4 );
			  }
			  else {
			  	return TRUE;
			  }
    }
}
/**
 * Helper class for sort items
 */
class Finder_Sorter_Iterator extends SplHeap {
    
    private $sortby;
    private $asc;

    public function __construct(Iterator $iterator, $sortby, $asc)
    {
				$this->sortby = $sortby;
				$this->asc    = $asc;
        foreach ($iterator as $item) {
            $this->insert($item);
        }
    }

    public function compare($b,$a)
    {
        switch ( $this->sortby ) {

        	case 'name':
        	default:
        	if ( $this->asc )
        		$r = strcmp($a->getPathname(), $b->getPathname());
        	else
        		$r = !strcmp($a->getPathname(), $b->getPathname());
        	break;

        	case 'date':
        	if ( $this->asc )
        		$r = intval ( $a->getMTime() >= $b->getMTime() );
        	else
        		$r = intval ( $a->getMTime() < $b->getMTime() );
        	if ( $r === 0)
        		$r = -1;
        	break;        	
        }
        return $r;
    }
}
/**
 * Lib Finder
 * Search files, filter by types, sort items, ignore or not hidden files
 */
class Finder {

	private $dit = NULL;

	public function __construct ( $path ) {
		$this->dit = new RecursiveDirectoryIterator ( $path );
	}

	public static function factory ( $path ) {
		return new Finder ( $path );
	}

	/*
	Public methods	
	 */
	
	/*
	Set option methods
	 */
	public function setTypes ( $types = array () ) {
		return $this->setOption ('types', (array) $types );
	}

	public function setNames ( $names = array () ) {
		return $this->setOption ('names', (array) $names );
	}

	public function setIgnores ( $ignores = array () ) {
		return $this->setOption ('ignores', (array) $ignores );
	}

	public function setHidden ( $hidden = FALSE ) {
		return $this->setOption ('hidden', (bool) $hidden );
	}		

	public function Find () {
		$this->dit = new Finder_Filter_Iterator ($this->dit);
		return $this;
	}

	/*
	Get sorted objects by name, or by date	
	 */
	public function getSorted ( $sortby = 'name', $asc = TRUE, $attrs = NULL ) {
		$rit = new RecursiveIteratorIterator ( $this->dit, RecursiveIteratorIterator::SELF_FIRST );
		$sit = new Finder_Sorter_Iterator ($rit, $sortby, $asc);
		return $this->_Get ($sit, $attrs);
	}

	/*
	Get objects without sorting	
	 */
	public function getAll ( $attrs = NULL ) {
		$rit = new RecursiveIteratorIterator ( $this->dit, RecursiveIteratorIterator::SELF_FIRST );
		return $this->_Get ($rit, $attrs);
	}	

	/*
	Private methods	
	 */
	private function setOption ( $option, $value ) {
		Finder_Filter_Iterator::$$option = $value;
		return $this;
	}	

	/**
	 * [Generic get objects by RiT]
	 * @param  RecursiveIteratorIterator $rit
	 * @param  mixed                     $attrs
	 * @return array                     $files
	 */
	private function _Get ( RecursiveIteratorIterator $rit, $attrs = NULL ) {
		if ( !empty ($rit) ) {
			$files = array ();
			
			if ( is_string ($attrs) && strpos ( $attrs, ',') ) {
				$attrs = explode (',', $attrs);
			}

			$attrs = (array) $attrs;
			
			foreach ( $rit as $filePath => $fileInfo )
				if ( $fileInfo->isFile() )
					if ( empty ($attrs) )
						$files[] = $fileInfo->getRealPath ();
					else {
						$file_attr = array ();
						$file_attr['realpath'] = $fileInfo->getRealPath ();
						
						foreach ( $attrs as $attr) {
							$attr = trim ($attr);
							$method = 'get' . mb_convert_case( $attr, MB_CASE_TITLE);
							if ( method_exists ( $fileInfo, $method ) )
								$file_attr[$attr] = call_user_func ( array ( &$fileInfo, $method ) );
							switch ($attr) {
								case 'owner':
									$file_attr[$attr] = posix_getpwuid ($file_attr[$attr]);
								break;
								case 'group':
									$file_attr[$attr] = posix_getgrgid ($file_attr[$attr]);
								break;																								
								case 'perms':
									$file_attr[$attr] = substr(sprintf('%o', $file_attr[$attr]), -4);
								break;																								
							}
						}
						$files[] = $file_attr;
					}
			return $files;			
		}
		return FALSE;
	}	
}