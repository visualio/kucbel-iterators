<?php

namespace Kucbel\Iterators;

use Countable;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Iterator;
use Nette\SmartObject;

class PeriodIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var DateTimeInterface
	 */
	protected $start;

	/**
	 * @var DateTimeInterface
	 */
	protected $finish;

	/**
	 * @var DateInterval
	 */
	protected $period;

	/**
	 * @var DateTime
	 */
	protected $value;

	/**
	 * @var int
	 */
	protected $index;

	/**
	 * @var bool
	 */
	protected $invert;

	/**
	 * PeriodIterator constructor.
	 *
	 * @param DateTimeInterface $start
	 * @param DateTimeInterface $finish
	 * @param DateInterval $period
	 */
	function __construct( DateTimeInterface $start, DateTimeInterface $finish, DateInterval $period )
	{
		$this->start = clone $start;
		$this->finish = clone $finish;
		$this->period = clone $period;
		$this->invert = $start > $finish;

		$this->rewind();
	}

	/**
	 * @return void
	 * @throws
	 */
	function rewind() : void
	{
		$this->value = new DateTime( $this->start->format('Y-m-d H:i:s.u'), $this->start->getTimezone() );
		$this->index = 0;
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		if( $this->invert ) {
			$this->value->sub( $this->period );
		} else {
			$this->value->add( $this->period );
		}
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		if( $this->invert ) {
			return $this->value >= $this->finish;
		} else {
			return $this->value <= $this->finish;
		}
	}

	/**
	 * @return DateTime
	 */
	function current()
	{
		return clone $this->value;
	}

	/**
	 * @return mixed
	 */
	function key()
	{
		return $this->index;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return iterator_count( $this );
	}
}