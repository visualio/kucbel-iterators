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
	protected $count = 0;

	/**
	 * @var bool
	 */
	protected $exist = false;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var mixed
	 */
	protected $index;

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
		$this->array = clone $this->array;
		$this->count = 0;
		$this->exist = false;
		$this->value =
		$this->index = null;
	}

	/**
	 * @return bool
	 */
	protected function match() : bool
	{
		$count = $this->count;

		while( $this->array->valid() ) {
			$this->value = $this->array->current();
			$this->index = $this->array->key();

			if(( $this->match )( $this->value, $this->index, $count )) {
				return true;
			}

			$this->array->next();
		}

		return false;
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

		$this->count = 0;
		$this->exist = $this->match();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->count++;
		$this->exist = $this->match();
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->exist;
	}

	/**
	 * @return mixed
	 */
	function current() : mixed
	{
		return $this->value;
	}

	/**
	 * @return mixed
	 */
	function key() : mixed
	{
		return $this->index;
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
