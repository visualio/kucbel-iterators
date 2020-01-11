<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ModifyIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var callable[] | null[]
	 */
	protected $alter;

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * Exist, Map value, Map index, New value, New index, Old value, Old index
	 *
	 * @var array
	 */
	protected $cache = [ false, false, false, null, null ];

	/**
	 * ModifyIterator constructor.
	 *
	 * @param iterable $array
	 * @param callable $value
	 * @param callable $index
	 */
	function __construct( iterable $array, callable $value = null, callable $index = null )
	{
		if( !$value and !$index ) {
			throw new InvalidArgumentException("Callback must be provided.");
		}

		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->alter = [ $value, $index ];
	}

	/**
	 * ModifyIterator cloner.
	 */
	function __clone()
	{
		$this->index = 0;
		$this->cache = [ false, false, false, null, null ];
	}

	/**
	 * @return array
	 */
	protected function fetch() : array
	{
		if( $this->array->valid() ) {
			return [ true, true, true, null, null, $this->array->current(), $this->array->key() ];
		} else {
			return [ false, false, false, null, null ];
		}
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
		return $this->cache[0];
	}

	/**
	 * @return mixed
	 */
	function current()
	{
		if( $this->cache[1] ) {
			$this->cache[3] = $this->alter[0] ? ( $this->alter[0] )( $this->cache[5], $this->cache[6], $this->index ) : $this->cache[5];
			$this->cache[1] = false;
		}

		return $this->cache[3];
	}

	/**
	 * @return mixed
	 */
	function key()
	{
		if( $this->cache[2] ) {
			$this->cache[4] = $this->alter[1] ? ( $this->alter[1] )( $this->cache[5], $this->cache[6], $this->index ) : $this->cache[6];
			$this->cache[2] = false;
		}

		return $this->cache[4];
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		if( $this->array instanceof Countable ) {
			return $this->array->count();
		} else {
			return iterator_count( $this );
		}
	}

	/**
	 * @return array
	 */
	function toArray() : array
	{
		return iterator_to_array( $this );
	}
}
