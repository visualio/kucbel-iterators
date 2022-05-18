<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use Nette\SmartObject;

class VoidIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @return void
	 */
	function rewind() : void
	{

	}

	/**
	 * @return void
	 */
	function next() : void
	{

	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return false;
	}

	/**
	 * @return null
	 */
	function current() : mixed
	{
		return null;
	}

	/**
	 * @return null
	 */
	function key() : mixed
	{
		return null;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return 0;
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return [];
	}
}
