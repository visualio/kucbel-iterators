<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class SleepIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var int
	 */
	private $delay;

	/**
	 * @var int
	 */
	private $count;

	/**
	 * @var int
	 */
	private $index = 0;

	/**
	 * SleepIterator constructor.
	 *
	 * @param int $delay
	 * @param int $count
	 */
	function __construct( int $delay, int $count )
	{
		if( $delay < 1 ) {
			throw new InvalidArgumentException("Delay must be positive number.");
		} elseif( $count < 1 ) {
			throw new InvalidArgumentException("Count must be positive number.");
		}

		$this->delay = $delay;
		$this->count = $count;
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->index = 0;
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->index++;

		if( $this->valid() ) {
			sleep( $this->delay );
		}
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->index < $this->count;
	}

	/**
	 * @return int
	 */
	function current()
	{
		return $this->index;
	}

	/**
	 * @return int
	 */
	function key()
	{
		return $this->index;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return $this->count;
	}
}