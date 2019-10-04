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
	protected $index = 0;

	/**
	 * ChunkIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $count
	 */
	function __construct( iterable $array, int $count = 100 )
	{
		if( $count < 2 ) {
			throw new InvalidArgumentException;
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		while( $array instanceof IteratorAggregate ) {
			$array = $array->getIterator();
		}

		$this->array = $array;
		$this->count = $count;
	}

	/**
	 * @return void
	 */
	protected function fetch() : void
	{
		$cache = null;

		for( $count = $this->count; $count > 0; $count-- ) {
			if( !$this->array->valid() ) {
				break;
			}

			$index = $this->array->key();
			$value = $this->array->current();

			$cache[ $index ] = $value;

			if( $count > 1 ) {
				$this->array->next();
			}
		}

		$this->cache = $cache;
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->array->rewind();

		$this->index = 0;

		$this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->index++;

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
		return $this->index;
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
}
