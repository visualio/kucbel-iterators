<?php

namespace Kucbel\Iterators;

class ArrayReverseIterator extends ArrayIterator
{
	/**
	 * @return void
	 */
	function rewind() : void
	{
		end( $this->array );
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		prev( $this->array );
	}
}
