<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\SmartObject;

class FilterIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var callable
	 */
	protected $match;

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * @var array
	 */
	protected $cache = [ false, null, null ];

	/**
	 * FilterIterator constructor.
	 *
	 * @param iterable $array
	 * @param callable $match
	 */
	function __construct( iterable $array, callable $match = null )
	{
		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->match = $match ?? function( $value ) { return isset( $value ); };
	}

	/**
	 * FilterIterator cloner.
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
		while( $this->array->valid() ) {
			$index = $this->array->key();
			$value = $this->array->current();

			if(( $this->match )( $value, $index, $this->index )) {
				return [ true, $value, $index ];
			}

			$this->array->next();
		}

		return [ false, null, null ];
	}

	/**
	 * @return void
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
	 * @return mixed
	 */
	function current()
	{
		return $this->cache[1];
	}

	/**
	 * @return mixed
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
