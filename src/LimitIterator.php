<?php

namespace Kucbel\Iterators;

use Iterator;
use IteratorAggregate;
use Countable;
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
	protected $start;

	/**
	 * @var int
	 */
	protected $abort;

	/**
	 * @var int | null
	 */
	protected $index;

	/**
	 * LimitIterator constructor.
	 *
	 * @param iterable $array
	 * @param int $count
	 * @param int $start
	 */
	function __construct( iterable $array, int $count, int $start = 0 )
	{
		if( $count <= 0 ) {
			throw new InvalidArgumentException('Count must be 1 or greater.');
		} elseif( $start < 0 ) {
			throw new InvalidArgumentException('Start must be 0 or greater.');
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->start = $start;
		$this->abort = $start + $count;
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

		for( $this->index = 0; $this->index < $this->start; $this->index++ ) {
			if( $this->array->valid() ) {
				$this->array->next();
			} else {
				break;
			}
		}
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		if( $this->index < $this->abort ) {
			$this->index++;

			$this->array->next();
		}
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->array->valid() and $this->index < $this->abort;
	}

	/**
	 * @return mixed
	 */
	function current()
	{
		return $this->array->current();
	}

	/**
	 * @return mixed
	 */
	function key()
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
}
