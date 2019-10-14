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
	 * @var array | null
	 */
	protected $cache;

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
	 * FilterIterator cloner.
	 */
	function __clone()
	{
		$this->array = clone $this->array;
		$this->cache = null;
	}

	/**
	 * @return array | null
	 */
	protected function fetch() : ?array
	{
		while( $this->array->valid() ) {
			$cache = [ $this->array->current(), $this->array->key() ];

			if(( $this->match )( ...$cache )) {
				return $cache;
			}

			$this->array->next();
		}

		return null;
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->array->rewind();

		$this->cache = $this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->cache = $this->fetch();
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return isset( $this->cache );
	}

	/**
	 * @return mixed
	 */
	function current()
	{
		return $this->cache[0];
	}

	/**
	 * @return mixed
	 */
	function key()
	{
		return $this->cache[1];
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return count( iterator_to_array( $this ));
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
