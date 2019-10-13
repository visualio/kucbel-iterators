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
	 * @var Iterator
	 */
	protected $array;

	/**
	 * @var callable
	 */
	protected $match;

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

		while( $array instanceof IteratorAggregate ) {
			$array = $array->getIterator();
		}

		$this->array = $array;
		$this->match = $match ?? function( $value ) { return isset( $value ); };
	}

	/**
	 * @return void
	 */
	protected function fetch() : void
	{
		$match = false;

		while( $this->array->valid() ) {
			$value = $this->array->current();
			$index = $this->array->key();

			if( $match = (bool) ( $this->match )( $value, $index )) {
				break;
			}

			$this->array->next();
		}

		if( $match ) {
			$this->cache = [ true, $value, $index ];
		} else {
			$this->cache = [ false, null, null ];
		}
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->array->rewind();

		$this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->fetch();
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
		return count( $this->toArray() );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
