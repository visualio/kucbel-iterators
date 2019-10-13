<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\SmartObject;

class ChunkIterator implements Countable, Iterator
{
	use SmartObject;

	const
		INDEXED		= 0b1,
		COUNTED		= 0b10;

	/**
	 * @var Iterator
	 */
	protected $array;

	/**
	 * @var array | null
	 */
	protected $cache;

	/**
	 * @var int
	 */
	protected $count;

	/**
	 * @var int
	 */
	protected $setup;

	/**
	 * @var int
	 */
	protected $round = 0;

	/**
	 * ChunkIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $count
	 * @param int $setup
	 */
	function __construct( iterable $array, int $count = 100, int $setup = self::COUNTED )
	{
		if( $count < 2 ) {
			throw new InvalidArgumentException("Chunk must contain at least 2 values.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		while( $array instanceof IteratorAggregate ) {
			$array = $array->getIterator();
		}

		$this->array = $array;
		$this->count = $count;
		$this->setup = $setup;
	}

	/**
	 * @return void
	 */
	protected function fetch() : void
	{
		$cache = null;

		for( $count = 0; $count < $this->count; $count++ ) {
			if( $count ) {
				$this->array->next();
			}

			if( !$this->array->valid() ) {
				break;
			}

			if( $this->setup & self::INDEXED ) {
				$index = $this->array->key();
			} else {
				$index = $count;
			}

			$cache[ $index ] = $this->array->current();
		}

		$this->cache = $cache;
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
		return $this->cache !== null;
	}

	/**
	 * @return array
	 */
	function current()
	{
		return $this->cache;
	}

	/**
	 * @return int
	 */
	function key()
	{
		return $this->round;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		if( !$this->array instanceof Countable ) {
			throw new InvalidStateException("Iterator isn't countable.");
		}

		return ceil( $this->array->count() / $this->count );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
