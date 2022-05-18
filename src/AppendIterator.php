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
	 * @var int
	 */
	protected $count = 0;

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

		$this->array = new VoidIterator;
	}

	/**
	 * AppendIterator cloner.
	 */
	function __clone()
	{
		$this->count = 0;
		$this->array = new VoidIterator;
	}

	/**
	 * @param int $count
	 * @return Iterator | null
	 * @throws
	 */
	protected function fetch( int $count ) : Iterator | null
	{
		$array = $this->queue[ $count ] ?? null;

		if( $array ) {
			while( $array instanceof IteratorAggregate ) {
				$array = $array->getIterator();
			}

			return $this->queue[ $count ] = $array;
		} else {
			return null;
		}
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->count = 0;

		if( $array = $this->fetch( $this->count )) {
			$array->rewind();

			$this->array = $array;
		}
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		while( !$this->array->valid() ) {
			if( $array = $this->fetch( $this->count + 1 )) {
				$array->rewind();

				$this->array = $array;
				$this->count++;
			} else {
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
	function offsetExists( mixed $index ) : bool
	{
		return isset( $this->queue[ $index ] );
	}

	/**
	 * @param mixed $index
	 * @return Iterator
	 */
	function offsetGet( mixed $index ) : mixed
	{
		return $this->queue[ $index ];
	}

	/**
	 * @param mixed $index
	 * @param mixed $value
	 */
	function offsetSet( mixed $index, mixed $value ) : void
	{
		if( !$value ) {
			return;
		}

		if( !is_null( $index )) {
			throw new InvalidArgumentException("Index must be null.");
		} elseif( !is_iterable( $value )) {
			throw new InvalidArgumentException("Value must be iterable.");
		}
		
		if( is_array( $value )) {
			$value = new ArrayIterator( $value );
		}

		$this->queue[] = $value;
	}

	/**
	 * @param mixed $index
	 */
	function offsetUnset( mixed $index ) : void
	{
		throw new MemberAccessException("Hello?, this is AppendIterator.");
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
