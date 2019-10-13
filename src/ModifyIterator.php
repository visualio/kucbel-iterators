<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\SmartObject;

class ModifyIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator
	 */
	protected $array;

	/**
	 * @var callable
	 */
	protected $value;

	/**
	 * @var callable
	 */
	protected $index;

	/**
	 * @var array
	 */
	protected $cache = [ false, null, null ];

	/**
	 * @var int
	 */
	protected $round = 0;

	/**
	 * ModifyIterator constructor.
	 *
	 * @param iterable $array
	 * @param callable $value
	 * @param callable $index
	 */
	function __construct( iterable $array, callable $value = null, callable $index = null )
	{
		if( !$value and !$index ) {
			throw new InvalidArgumentException("Provide at least one callback.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		while( $array instanceof IteratorAggregate ) {
			$array = $array->getIterator();
		}

		$this->array = $array;
		$this->value = $value ?? function( $value ) { return $value; };
		$this->index = $index ?? function( $value, $index ) { return $index; };
	}

	/**
	 * @return void
	 */
	protected function fetch() : void
	{
		if( $this->array->valid() ) {
			$value = $this->array->current();
			$index = $this->array->key();

			$this->cache = [ true, ( $this->value )( $value, $index ), ( $this->index )( $value, $index ) ?? $this->round ];
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

		$this->round = 0;

		$this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->round++;

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
		if( !$this->array instanceof Countable ) {
			throw new InvalidStateException("Iterator isn't countable.");
		}

		return $this->array->count();
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
