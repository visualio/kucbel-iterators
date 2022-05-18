<?php

namespace Kucbel\Iterators;

use ArrayAccess;
use Countable;
use Iterator;
use Nette\SmartObject;

class ArrayIterator implements ArrayAccess, Countable, Iterator
{
	use SmartObject;

	/**
	 * @var array
	 */
	protected $array;

	/**
	 * ArrayIterator constructor.
	 *
	 * @param array $array
	 */
	function __construct( array $array = null )
	{
		$this->array = $array ?? [];
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		reset( $this->array );
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		next( $this->array );
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return key( $this->array ) !== null;
	}

	/**
	 * @return mixed
	 */
	function current() : mixed
	{
		return current( $this->array );
	}

	/**
	 * @return mixed
	 */
	function key() : mixed
	{
		return key( $this->array );
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return count( $this->array );
	}

	/**
	 * @param mixed $index
	 * @return bool
	 */
	function offsetExists( mixed $index ) : bool
	{
		return isset( $this->array[ $index ] );
	}

	/**
	 * @param mixed $index
	 * @return mixed
	 */
	function offsetGet( mixed $index ) : mixed
	{
		return $this->array[ $index ];
	}

	/**
	 * @param mixed $index
	 * @param mixed $value
	 */
	function offsetSet( mixed $index, mixed $value ) : void
	{
		if( $index === null ) {
			$this->array[] = $value;
		} else {
			$this->array[ $index ] = $value;
		}
	}

	/**
	 * @param mixed $index
	 */
	function offsetUnset( mixed $index ) : void
	{
		unset( $this->array[ $index ] );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return $this->array;
	}
}
