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
	function current()
	{
		return current( $this->array );
	}

	/**
	 * @return mixed
	 */
	function key()
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
	 * @param mixed $offset
	 * @return bool
	 */
	function offsetExists( $offset ) : bool
	{
		return array_key_exists( $offset, $this->array );
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	function offsetGet( $offset )
	{
		return $this->array[ $offset ];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	function offsetSet( $offset, $value ) : void
	{
		if( $offset === null ) {
			$this->array[] = $value;
		} else {
			$this->array[ $offset ] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 */
	function offsetUnset( $offset ) : void
	{
		unset( $this->array[ $offset ] );
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return $this->array;
	}
}
