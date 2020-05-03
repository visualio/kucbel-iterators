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

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var int
	 */
	protected $chunk;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var bool
	 */
	protected $assoc;

	/**
	 * @var array
	 */
	protected $value = [];

	/**
	 * ChunkIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $chunk
	 * @param bool $assoc
	 */
	function __construct( iterable $array, int $chunk, bool $assoc = false )
	{
		if( $chunk < 1 ) {
			throw new InvalidArgumentException("Chunk must be positive number.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->chunk = $chunk;
		$this->assoc = $assoc;
	}

	/**
	 * ChunkIterator cloner.
	 */
	function __clone()
	{
		$this->count = 0;
		$this->value = [];
	}

	/**
	 * @return array
	 */
	protected function fetch() : array
	{
		$value = [];
		$count = 0;

		while( $this->array->valid() ) {
			$index = $this->assoc ? $this->array->key() : $count;
			$value[ $index ] = $this->array->current();

			if( ++$count === $this->chunk ) {
				break;
			}

			$this->array->next();
		}

		return $value;
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
		$this->value = $this->fetch();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->count++;
		$this->value = $this->fetch();
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->value ? true : false;
	}

	/**
	 * @return array
	 */
	function current() : array
	{
		return $this->value;
	}

	/**
	 * @return int
	 */
	function key() : int
	{
		return $this->count;
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

		return ceil( $count / $this->chunk );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
