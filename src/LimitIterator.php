<?php

namespace Kucbel\Iterators;

use Iterator;
use IteratorAggregate;
use Countable;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class LimitIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var int
	 */
	protected $count;

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * @var array
	 */
	protected $cache = [ false, null, null ];

	/**
	 * LimitIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $count
	 */
	function __construct( iterable $array, int $count )
	{
		if( $count <= 0 ) {
			throw new InvalidArgumentException('Count must be 1 or greater.');
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->count = $count;
	}

	/**
	 * LimitIterator cloner.
	 */
	function __clone()
	{
		$this->index = 0;
		$this->cache = [ false, null, null ];
	}

	/**
	 * @return array
	 */
	protected function fetch() : array
	{
		if( $this->index < $this->count and $this->array->valid() ) {
			return [ true, $this->array->current(), $this->array->key() ];
		} else {
			return [ false, null, null ];
		}
	}

	/**
	 * @return void
	 * @throws
	 */
	function rewind() : void
	{
		while( $this->array instanceof IteratorAggregate ) {
			$this->array = $this->array->getIterator();
		}

		$this->array->rewind();

		$this->index = 0;
		$this->cache = $this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->index++;
		$this->cache = $this->fetch();
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->cache[0];
	}

	/**
	 * @return mixed | null
	 */
	function current()
	{
		return $this->cache[1];
	}

	/**
	 * @return mixed | null
	 */
	function key()
	{
		return $this->cache[2];
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return iterator_count( $this );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
