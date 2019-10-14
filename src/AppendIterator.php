<?php

namespace Kucbel\Iterators;

use ArrayAccess;
use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\MemberAccessException;
use Nette\SmartObject;

class AppendIterator implements ArrayAccess, Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator[]
	 */
	protected $queue = [];

	/**
	 * @var Iterator
	 */
	protected $array;

	/**
	 * @var Iterator
	 */
	protected $empty;

	/**
	 * @var int | null
	 */
	protected $index;

	/**
	 * AppendIterator constructor.
	 *
	 * @param iterable ...$arrays
	 */
	function __construct( iterable ...$arrays )
	{
		foreach( $arrays as $array ) {
			if( is_array( $array )) {
				$array = new ArrayIterator( $array );
			}

			while( $array instanceof IteratorAggregate ) {
				$array = $array->getIterator();
			}

			$this->queue[] = $array;
		}

		$this->array =
		$this->empty = new ArrayIterator;
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->index = 0;

		if( $this->array = $this->queue[ $this->index ] ?? null ) {
			$this->array->rewind();
		} else {
			$this->array = $this->empty;
		}
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		while( !$this->array->valid() ) {
			if( $this->array = $this->queue[ ++$this->index ] ?? null ) {
				$this->array->rewind();
			} else {
				$this->array = $this->empty;

				break;
			}
		}
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->array->valid();
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
		$count = 0;

		foreach( $this->queue as $i => $array ) {
			if( $array instanceof Countable ) {
				$count += $array->count();
			} else {
				$count += count( iterator_to_array( $array ));
			}
		}

		return $count;
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	function offsetExists( $offset ) : bool
	{
		return isset( $this->queue[ $offset ] );
	}

	/**
	 * @param mixed $offset
	 * @return Iterator
	 */
	function offsetGet( $offset )
	{
		return $this->queue[ $offset ];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	function offsetSet( $offset, $value )
	{
		if( !is_null( $offset )) {
			throw new InvalidArgumentException("Offset must be null.");
		} elseif( !is_iterable( $value )) {
			throw new InvalidArgumentException("Value must be iterable.");
		}

		if( is_array( $value )) {
			$value = new ArrayIterator( $value );
		}

		while( $value instanceof IteratorAggregate ) {
			$value = $value->getIterator();
		}

		$this->queue[] = $value;
	}

	/**
	 * @param mixed $offset
	 */
	function offsetUnset( $offset )
	{
		throw new MemberAccessException("This is AppendIterator.");
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
