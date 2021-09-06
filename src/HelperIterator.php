<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 * Class HelperIterator
 *
 * @deprecated buggy isFirst()
 */
class HelperIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var array
	 */
	protected $cache = [];

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
	 * HelperIterator constructor.
	 *
	 * @param iterable $array
	 */
	function __construct( iterable $array )
	{
		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
	}

	/**
	 * HelperIterator cloner.
	 */
	function __clone()
	{
		$this->cache = [];
		$this->count = 0;
		$this->exist = false;
		$this->index =
		$this->value = null;
	}

	/**
	 * @param int $count
	 */
	protected function clear( int $count ) : void
	{
		unset( $this->cache[ $count ] );
	}

	/**
	 * @param int $count
	 * @return bool
	 */
	protected function select( int $count ) : bool
	{
		$this->exist = isset( $this->cache[ $count ] );

		if( $this->exist ) {
			[ $this->index, $this->value ] = $this->cache[ $count ];

			return true;
		} else {
			$this->index =
			$this->value = null;

			return false;
		}
	}

	/**
	 * @param int $count
	 * @return bool
	 */
	protected function fetch( int $count ) : bool
	{
		if( $this->array->valid() ) {
			$this->cache[ $count ] = [ $this->array->key(), $this->array->current() ];

			return true;
		} else {
			return true;
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

		$this->count = 0;
		$this->cache = [];

		$this->fetch( $this->count );

		if( $this->select( $this->count )) {
			$this->array->next();

			$this->fetch( $this->count + 1 );
		}
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->clear( $this->count - 1 );

		$this->count++;

		if( $this->select( $this->count )) {
			$this->array->next();

			$this->fetch( $this->count + 1 );
		}
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
	function current()
	{
		return $this->value;
	}

	/**
	 * @return mixed
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
		while( $this->array instanceof IteratorAggregate ) {
			$this->array = $this->array->getIterator();
		}

		if( $this->array instanceof Countable ) {
			return $this->array->count();
		} else {
			return iterator_count( $this->array );
		}
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this->array );
	}

	/**
	 * @return int
	 */
	function getZeroCounter() : int
	{
		return $this->count;
	}

	/**
	 * @return int
	 */
	function getCounter() : int
	{
		return $this->count + 1;
	}

	/**
	 * @return mixed | null
	 */
	function getPreceding()
	{
		return $this->cache[ $this->count - 1 ][1] ?? null;
	}

	/**
	 * @return mixed | null
	 */
	function getFollowing()
	{
		return $this->cache[ $this->count + 1 ][1] ?? null;
	}

	/**
	 * @return mixed | null
	 */
	function getPrecedingKey()
	{
		return $this->cache[ $this->count - 1 ][0] ?? null;
	}

	/**
	 * @return mixed | null
	 */
	function getFollowingKey()
	{
		return $this->cache[ $this->count + 1 ][0] ?? null;
	}

	/**
	 * @return bool
	 */
	function isEmpty() : bool
	{
		while( $this->array instanceof IteratorAggregate ) {
			$this->array = $this->array->getIterator();
		}

		$array = clone $this->array;
		$array->rewind();

		return !$array->valid();
	}

	/**
	 * @return bool
	 */
	function isFirst() : bool
	{
		return !isset( $this->cache[ $this->count - 1 ] );
	}

	/**
	 * @return bool
	 */
	function isLast() : bool
	{
		return !isset( $this->cache[ $this->count + 1 ] );
	}

	/**
	 * @param int $count
	 * @return bool
	 */
	function isFirstIn( int $count ) : bool
	{
		if( $count < 1 ) {
			throw new InvalidArgumentException("Count must be positive number.");
		}

		return $this->count % $count === 0;
	}

	/**
	 * @param int $count
	 * @return bool
	 */
	function isLastIn( int $count ) : bool
	{
		if( $count < 1 ) {
			throw new InvalidArgumentException("Count must be positive number.");
		}

		return $this->count % $count === $count - 1;
	}

	/**
	 * @return bool
	 */
	function isEven() : bool
	{
		return !$this->isOdd();
	}

	/**
	 * @return bool
	 */
	function isOdd() : bool
	{
		return $this->count & 1;
	}
}