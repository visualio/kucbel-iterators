<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
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
	protected $limit;

	/**
	 * @var int
	 */
	protected $first;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var bool
	 */
	protected $exist = false;

	/**
	 * LimitIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $limit
	 * @param int $first
	 */
	function __construct( iterable $array, int $limit, int $first = 0 )
	{
		if( $limit < 0 ) {
			throw new InvalidArgumentException('Limit must be positive number.');
		} elseif( $first < 0 ) {
			throw new InvalidArgumentException('First must be positive number.');
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->limit = $first + $limit;
		$this->first = $first;
	}

	/**
	 * LimitIterator cloner.
	 */
	function __clone()
	{
		$this->array = clone $this->array;
		$this->count = 0;
		$this->exist = false;
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

		while( $this->count < $this->first ) {
			if( $this->array->valid() ) {
				$this->array->next();

				$this->count++;
			} else {
				break;
			}
		}

		if( $this->count === $this->first and $this->count < $this->limit ) {
			$this->exist = $this->array->valid();
		} else {
			$this->exist = false;
		}
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		if( $this->count >= $this->first ) {
			$this->count++;

			if( $this->count < $this->limit ) {
				$this->array->next();

				$this->exist = $this->array->valid();
			} else {
				$this->exist = false;
			}
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
	function current() : mixed
	{
		return $this->array->current();
	}

	/**
	 * @return mixed
	 */
	function key() : mixed
	{
		return $this->array->key();
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
