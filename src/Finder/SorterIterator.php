<?php
namespace AppZz\Filesystem\Finder;
use SplHeap;
use Iterator;

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
		switch ($this->_sortby)
		{
			case 'name':
			default:
			  	if ($this->_asc)
			  		$r = strcmp($a->getPathname(), $b->getPathname());
			  	else
			  		$r = !strcmp($a->getPathname(), $b->getPathname());
			break;

			case 'date':
			  	if ($this->_asc)
			  		$r = intval ($a->getMTime() >= $b->getMTime());
			  	else
			  		$r = intval ($a->getMTime() < $b->getMTime());
			  	if ( $r === 0)
			  		$r = -1;
			break;
		}

		return $r;
	}
}
