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
	 * @var Iterator[] | IteratorAggregate[]
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
	 * @var int
	 */
	protected $index = 0;

	/**
	 * AppendIterator constructor.
	 *
	 * @param iterable ...$arrays
	 */
	function __construct( iterable ...$arrays )
	{
		foreach( $arrays as $array ) {
			if( !$array ) {
				continue;
			}
			
			if( is_array( $array )) {
				$array = new ArrayIterator( $array );
			}

			$this->queue[] = $array;
		}

		$this->array =
		$this->empty = new VoidIterator;
	}

	/**
	 * AppendIterator cloner.
	 */
	function __clone()
	{
		$this->index = 0;
		$this->array = $this->empty;
	}

	/**
	 * @return Iterator | null
	 */
	protected function fetch() : ?Iterator
	{
		$array = $this->queue[ $this->index ] ?? null;

		if( $array ) {
			while( $array instanceof IteratorAggregate ) {
				$array = $array->getIterator();
			}

			return $this->queue[ $this->index ] = $array;
		} else {
			return null;
		}
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->index = 0;

		if( $this->array = $this->fetch() ) {
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
			$this->index++;

			if( $this->array = $this->fetch() ) {
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

		foreach( $this->queue as $array ) {
			if( $array instanceof Countable ) {
				$count += $array->count();
			} else {
				$count += iterator_count( $array );
			}
		}

		return $count;
	}

	/**
	 * @param mixed $index
	 * @return bool
	 */
	function offsetExists( $index ) : bool
	{
		return isset( $this->queue[ $index ] );
	}

	/**
	 * @param mixed $index
	 * @return Iterator
	 */
	function offsetGet( $index )
	{
		return $this->queue[ $index ];
	}

	/**
	 * @param mixed $index
	 * @param mixed $value
	 */
	function offsetSet( $index, $value )
	{
		if( !is_null( $index )) {
			throw new InvalidArgumentException("Offset must be null.");
		} elseif( !is_iterable( $value )) {
			throw new InvalidArgumentException("Value must be iterable.");
		}

		if( !$value ) {
			return;
		} 
		
		if( is_array( $value )) {
			$value = new ArrayIterator( $value );
		}

		$this->queue[] = $value;
	}

	/**
	 * @param mixed $index
	 */
	function offsetUnset( $index )
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
