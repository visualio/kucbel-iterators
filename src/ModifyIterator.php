<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
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
	protected $alter;

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * Exist, Alter, Value?, Index?, Round?
	 *
	 * @var array
	 */
	protected $cache = [ false, false ];

	/**
	 * ModifyIterator constructor.
	 *
	 * @param iterable $array
	 * @param callable $alter
	 */
	function __construct( iterable $array, callable $alter )
	{
		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->alter = $alter;
	}

	/**
	 * ModifyIterator cloner.
	 */
	function __clone()
	{
		$this->index = 0;
		$this->cache = [ false, false ];
	}

	/**
	 * @return array
	 */
	protected function fetch() : array
	{
		if( $this->array->valid() ) {
			return [ true, true, $this->array->current(), $this->array->key(), $this->index ];
		} else {
			return [ false, false ];
		}
	}

	/**
	 * @param int $fetch
	 * @return mixed
	 */
	protected function modify( int $fetch )
	{
		if( $this->cache[1] ) {
			$this->cache[1] = false;

			( $this->alter )( $this->cache[2], $this->cache[3], $this->cache[4] );
		}

		if( $this->cache[0] ) {
			return $this->cache[ $fetch ];
		} else {
			return null;
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
	 * @return mixed
	 */
	function current()
	{
		return $this->modify( 2 );
	}

	/**
	 * @return mixed
	 */
	function key()
	{
		return $this->modify( 3 );
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
