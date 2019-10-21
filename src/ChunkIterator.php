<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ChunkIterator implements Countable, Iterator
{
	use SmartObject;

	const
		INDEXED		= 0b1,
		COUNTED		= 0b10;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var int
	 */
	protected $batch;

	/**
	 * @var int
	 */
	protected $setup;

	/**
	 * @var int | null
	 */
	protected $round;

	/**
	 * @var array | null
	 */
	protected $cache;

	/**
	 * ChunkIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $batch
	 * @param int $setup
	 */
	function __construct( iterable $array, int $batch = 100, int $setup = self::COUNTED )
	{
		if( $batch < 2 ) {
			throw new InvalidArgumentException("Chunk must contain at least 2 values.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->batch = $batch;
		$this->setup = $setup;
	}

	/**
	 * ChunkIterator cloner.
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
		$setup = $this->setup & self::COUNTED;
		$count = 0;
		$cache = null;

		while( $this->array->valid() ) {
			$cache[ $setup ? $count : $this->array->key() ] = $this->array->current();

			if( ++$count === $this->batch ) {
				break;
			}

			$this->array->next();
		}

		return $cache;
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
		if( $this->array instanceof Countable ) {
			$count = $this->array->count();
		} else {
			$count = iterator_count( $this );
		}

		return ceil( $count / $this->batch );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
