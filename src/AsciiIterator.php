<?php

namespace Kucbel\Iterators;

use Countable;
use Iterator;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\SmartObject;
use Psr\Http\Message\StreamInterface;

class AsciiIterator implements Countable, Iterator
{
	use SmartObject;

	/**
	 * @var StreamInterface
	 */
	protected $stream;

	/**
	 * @var array
	 */
	protected $accept = [];

	/**
	 * @var string
	 */
	protected $result = '';

	/**
	 * @var int
	 */
	protected $length;

	/**
	 * @var int
	 */
	protected $memory;

	/**
	 * @var int
	 */
	protected $buffer = 0;

	/**
	 * @var int
	 */
	protected $cursor = 0;

	/**
	 * @var int
	 */
	protected $number = 0;

	/**
	 * @var bool
	 */
	protected $finish = true;

	/**
	 * AsciiIterator constructor.
	 *
	 * @param StreamInterface $stream
	 * @param int $length
	 * @param int $memory
	 */
	function __construct( StreamInterface $stream, int $length = 1024 * 4, int $memory = 1024 * 16 )
	{
		if( $length <= 0 ) {
			throw new InvalidArgumentException('Length must be positive non-zero number.');
		}

		$this->stream = $stream;
		$this->length = $length;
		$this->memory = $memory;
	}

	/**
	 * @return void
	 */
	function rewind() : void
	{
		$this->warmup();

		$this->stream->rewind();

		$this->result = '';
		$this->buffer =
		$this->number = 0;
		$this->finish = $this->stream->eof();

		$this->search();
	}

	/**
	 * @return void
	 */
	function next() : void
	{
		if( $this->cursor === $this->buffer ) {
			$this->result = '';
			$this->buffer = 0;
		} else {
			$this->result = substr( $this->result, $this->cursor );
			$this->buffer -= $this->cursor;
		}

		$this->number++;

		$this->search();
	}

	/**
	 * @return bool
	 */
	function valid() : bool
	{
		return (bool) $this->cursor;
	}

	/**
	 * @return string
	 */
	function current() : string
	{
		if( $this->cursor === $this->buffer ) {
			return $this->result;
		} else {
			return substr( $this->result, 0, $this->cursor );
		}
	}

	/**
	 * @return int
	 */
	function key() : int
	{
		return $this->number;
	}

	/**
	 * @return int
	 */
	function count() : int
	{
		return iterator_count( $this );
	}

	/**
	 * @return void
	 */
	protected function warmup() : void
	{
		if( !$this->accept ) {
			for( $number = 32; $number <= 126; $number++ ) {
				$letter = chr( $number );

				$this->accept[ $letter ] = $letter;
			}
		}
	}

	/**
	 * @return void
	 */
	protected function search() : void
	{
		$this->cursor = 0;

		while( !$this->finish ) {
			$result = $this->stream->read( $this->length );
			$offset = $this->buffer;

			$this->result .= $result;
			$this->buffer += strlen( $result );

			if( $this->buffer > $this->memory ) {
				throw new InvalidStateException("Memory limit reached.");
			}

			if( $this->stream->eof() ) {
				$this->finish = true;

				break;
			}

			for( $cursor = $this->buffer - 1; $cursor >= $offset; $cursor-- ) {
				$letter = $this->result[ $cursor ];

				if( isset( $this->accept[ $letter ] )) {
					$this->cursor = $cursor;

					break 2;
				}
			}
		}

		if( $this->cursor === 0 and $this->buffer !== 0 ) {
			$this->cursor = $this->buffer;
		}
	}
}