<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use IteratorAggregate;
use Nette\SmartObject;

class ModifyIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var Iterator | IteratorAggregate
	 */
	protected $array;

	/**
	 * @var callable
	 */
	protected $alter;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var bool
	 */
	protected $exist = false;

	/**
	 * @var bool
	 */
	protected $delay = false;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var mixed
	 */
	protected $index;

	/**
	 * ModifyIterator constructor.
	 *
	 * @param iterable $array
	 * @param callable $alter
	 */
	function __construct( iterable $array, callable $alter )
	{
		if( is_array( $array )) {
			$array = new ArrayIterator( $array );
		}

		$this->array = $array;
		$this->alter = $alter;
	}

	/**
	 * ModifyIterator cloner.
	 */
	function __clone()
	{
		$this->array = clone $this->array;
		$this->count = 0;
		$this->exist =
		$this->delay = false;
		$this->value =
		$this->index = null;
	}

	/**
	 * @return void
	 */
	protected function modify() : void
	{
		if( $this->delay ) {
			$this->delay = false;
			$this->value = $this->array->current();
			$this->index = $this->array->key();

			$count = $this->count;

			( $this->alter )( $this->value, $this->index, $count );
		}
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
		$this->exist =
		$this->delay = $this->array->valid();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		$this->array->next();

		$this->count++;
		$this->exist =
		$this->delay = $this->array->valid();
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return $this->exist;
	}

	/**
	 * @return mixed
	 */
	function current() : mixed
	{
		$this->modify();

		return $this->value;
	}

	/**
	 * @return mixed
	 */
	function key() : mixed
	{
		$this->modify();

		return $this->index;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		while( $this->array instanceof IteratorAggregate ) {
			$this->array = $this->array->getIterator();
		}

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
