<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ModifyIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator | IteratorAggregate
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
	 * @var int | null
	 */
	protected $round;

	/**
	 * @var array | null
	 */
	protected $cache;

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
			throw new InvalidArgumentException("Callback must be provided.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->value = $value ?? function( $value ) { return $value; };
		$this->index = $index ?? function( $value, $index ) { return $index; };
	}

	/**
	 * ModifyIterator cloner.
	 */
	function __clone()
	{
		$this->array = clone $this->array;
		$this->round =
		$this->cache = null;
	}

	/**
	 * @return array | null
	 */
	protected function fetch() : ?array
	{
		if( $this->array->valid() ) {
			return [ $this->array->current(), $this->array->key(), $this->round ];
		} else {
			return null;
		}
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

		$this->round = 0;
		$this->cache = $this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->round++;
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
		return ( $this->value )( ...$this->cache );
	}

	/**
	 * @return mixed
	 */
	function key()
	{
		return ( $this->index )( ...$this->cache );
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		if( $this->array instanceof Countable ) {
			return $this->array->count();
		} else {
			return iterator_count( $this );
		}
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
