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
		INDEX	= 0b1,
		COUNT	= 0b10;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var int
	 */
	protected $count;

	/**
	 * @var int
	 */
	protected $assoc;

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * @var array
	 */
	protected $cache = [];

	/**
	 * ChunkIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $count
	 * @param int $assoc
	 */
	function __construct( iterable $array, int $count, int $assoc = self::COUNT )
	{
		if( $count < 2 ) {
			throw new InvalidArgumentException("Count must be 2 or greater.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->count = $count;
		$this->assoc = $assoc;
	}

	/**
	 * ChunkIterator cloner.
	 */
	function __clone()
	{
		$this->index = 0;
		$this->cache = [];
	}

	/**
	 * @return array
	 */
	protected function fetch() : array
	{
		$assoc = $this->assoc & self::INDEX ? false : true;
		$cache = [];
		$count = 0;

		while( $this->array->valid() ) {
			$index = $assoc ? $count : $this->array->key();
			$cache[ $index ] = $this->array->current();

			if( ++$count === $this->count ) {
				break;
			}

			$this->array->next();
		}

		return $cache;
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
		return $this->cache ? true : false;
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
		return $this->index;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		if( $this->array instanceof Countable ) {
			$count = $this->array->count();
		} else {
			$count = iterator_count( $this->array );
		}

		return ceil( $count / $this->count );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
