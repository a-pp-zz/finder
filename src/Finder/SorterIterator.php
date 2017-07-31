<?php
namespace AppZz\Filesystem\Finder;
use SplHeap;
use Iterator;

/**
 * Class SorterIterator
 * @package AppZz\Filesystem\Finder
 * @author CoolSwitcher
 * @team AppZz
 * @version 2.0
 */
class SorterIterator extends SplHeap {

	private $_sortby;
	private $_asc;

	public function __construct (Iterator $iterator, $sortby, $asc)
	{
		$this->_sortby = $sortby;
		$this->_asc    = $asc;

		foreach ($iterator as $item)
		{
			$this->insert($item);
		}
	}

	public function compare($b, $a)
	{
		$r = 0;

		switch ($this->_sortby)
		{
			case 'name':
			//default:
				$r = strcasecmp ($a->getPathname(), $b->getPathname());
			  	if ( ! $this->_asc) {
			  		$r = ! $r;
			  	}
			break;

			case 'date':
			  	if ($this->_asc)
			  		$r = ($a->getMTime() >= $b->getMTime());
			  	else
			  		$r = ($a->getMTime() < $b->getMTime());
			break;

			case 'size':
				if ($this->_asc)
					$r = ($a->getSize() >= $b->getSize());
				else
					$r = ($a->getSize() < $b->getSize());
			break;

			case 'type':
				if ($this->_asc)
					$r = ($a->getExtension() >= $b->getExtension());
				else
					$r = ($a->getExtension() < $b->getExtension());
			break;
		}

		if ( ! $r)
			$r = -1;

		return intval ($r);
	}
}
