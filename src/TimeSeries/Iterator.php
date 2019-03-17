<?php

declare(strict_types=1);

namespace Xpl\Decimal\TimeSeries;

use DateTimeImmutable;
use Decimal\Decimal;
use Xpl\Decimal\TimeSeries;

/**
 * Iterator for a TimeSeries.
 * 
 * Provides iteration over a TimeSeries with the following behavior:
 *	- Order is always chronological, from oldest to newest.
 *	- Values are always an instance of Decimal\Decimal.
 *	- Keys are either an integer or DateTimeImmutable (see below).
 *	- Modification of the TimeSeries after iteration has started results in 
 *	  undefined behavior.
 * 
 * The current key can be yielded as one of three different values, as
 * determined by the $flags argument given in the constructor:
 * 
 *  KEY_AS_INDEX       (default) Key is the current iterator position as an
 *                     integer.
 *  KEY_AS_TIMESTAMP   Key is the Unix timestamp associated with the current
 *                     element as an integer.
 *  KEY_AS_DATETIME    Key is a DateTimeImmutable instance for the timestamp
 *                     associated with the current element.
 * 
 * The flags can only be set before iteration starts. Once started, attempting
 * to change the flags will throw an exception.
 * 
 * Modification of the TimeSeries during iteration is not permitted, and
 * doing so results in undefined behavior.
 */
class Iterator implements \Iterator
{

	/**
	 * Flag to yield the current iterator index as the key
	 * 
	 * @var int
	 */
	public const KEY_AS_INDEX = 0x00;

	/**
	 * Flag to yield the current timestamp as the key
	 * 
	 * @var int
	 */
	public const KEY_AS_TIMESTAMP = 0x01;

	/**
	 * Flag to yield the current DateTimeImmutable as the key
	 * 
	 * @var int
	 */
	public const KEY_AS_DATETIME = 0x02;

	/**
	 * TimeSeries instance
	 *
	 * @var TimeSeries
	 */
	private $series;

	/**
	 * Indexed array of Unix timestamps
	 *
	 * @var int[]
	 */
	private $timestamps;

	/**
	 * Lazy indexed array of DateTimeImmutable instances
	 * 
	 * @var DateTimeImmutable[]
	 */
	private $datetimes = [];

	/**
	 * Flags used to determine keys
	 *
	 * @var int
	 */
	private $flags;

	/**
	 * Current iterator position
	 *
	 * @var int
	 */
	private $index;

	/**
	 * Constructor.
	 *
	 * @param TimeSeries $timeseries
	 * @param int $flags [Optional] Determines the value returned as the key.
	 */
	public function __construct(TimeSeries $timeseries, int $flags = 0x00)
	{
		$this->series = $timeseries;
		$this->flags = $flags;
	}

	/**
	 * Sets the flags used to determine the value returned as the current key.
	 *
	 * @param int $flags
	 * 
	 * @throws RuntimeException if iteration has already started
	 */
	public function setFlags(int $flags)
	{
		if (isset($this->index)) {
			throw new RuntimeException("Cannot modify flags after iteration starts");
		}

		$this->flags = $flags;
	}

	/**
	 * Returns the current element.
	 *
	 * @return Decimal
	 */
	public function current(): Decimal
	{
		return $this->series->get($this->getDateTime($this->index));
	}

	/**
	 * Returns the key of the current element.
	 * 
	 * The key returned is determined by the flags setting:
	 *	- KEY_AS_INDEX - returns the current iterator position as an integer
	 *	- KEY_AS_TIMESTAMP - returns the current Unix timestamp as an integer
	 *	- KEY_AS_DATETIME - returns the current date as a DateTimeImmutable

	 * @return int|DateTimeImmutable
	 */
	public function key()
	{
		switch ($this->flags) {
			default:
			case self::KEY_AS_INDEX:
				return $this->index;
			case self::KEY_AS_TIMESTAMP:
				return $this->timestamps[$this->index];
			case self::KEY_AS_DATETIME:
				return $this->getDateTime($this->index);
		}
	}

	/**
	 * Moves the current position to the next element.
	 */
	public function next(): void
	{
		$this->index++;
	}

	/**
	 * Rewinds the iterator to the first element.
	 */
	public function rewind(): void
	{
		$this->index = 0;
		$this->timestamps = $this->series->keys()->toArray();
		$this->datetimes = [];
	}

	/**
	 * Checks if the current position is valid.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return isset($this->timestamps[$this->index]);
	}
	
	/**
	 * Returns a DateTimeImmutable for the current iteration.
	 *
	 * @return DateTimeImmutable|null
	 */
	public function currentDateTime(): ?DateTimeImmutable
	{
		return $this->getDateTime($this->index);
	}

	/**
	 * Fetches/creates a DateTimeImmutable for the given index.
	 *
	 * @param int $index
	 *
	 * @return DateTimeImmutable|null
	 */
	private function getDateTime(int $index): ?DateTimeImmutable
	{
		if (! isset($this->datetimes[$index])) {

			if (! isset($this->timestamps[$index])) {
				return null;
			}
			
			$this->datetimes[$index] = new DateTimeImmutable('@'.$this->timestamps[$index]);
		}

		return $this->datetimes[$index];
	}

}