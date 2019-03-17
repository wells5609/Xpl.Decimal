<?php

declare(strict_types=1);

namespace Xpl\Decimal\TimeSeries;

use Iterator;
use DateTimeImmutable;
use Decimal\Decimal;
use Xpl\Decimal\DatedDecimalPair;

/**
 * Synchronous iteration over two TimeSeries.
 * 
 * The dates contained in the first TimeSeries (x) determines the set of
 * potentially valid dates, which are used to find the values yielded upon
 * iteration.
 * 
 * The iterator only yields a pair of values if both TimeSeries contain an
 * entry for a given date. For each iteration, the iterator fetches the next
 * timestamp and checks to see if TimeSeries y contains an entry for that date. 
 * If both TimeSeries contain an entry for the current date, the iterator 
 * yields a DatedDecimalPair that contains the associated Decimal entries from 
 * x and y, as well as a DateTimeImmutable instance for the current date.
 * 
 * Using the peek() method, observations (i.e. pairs) can be accessed by index
 * without modifying the current iterator position. This can be used to, for
 * example, perform relative calculations (e.g. % change) between observations.
 * 
 * Modification of either TimeSeries during iteration is not permitted, and
 * doing so results in undefined behavior.
 */
class DualIterator implements Iterator
{

	/** 
	 * Time series of x values
	 * 
	 * @var TimeSeries 
	 */
	private $x;

	/** 
	 * Time series of y values
	 * 
	 * @var TimeSeries
	 */
	private $y;

	/** 
	 * Indexed array of timestamps (from $x)
	 * 
	 * @var array
	 */
	private $timestamps;

	/**
	 * Indexed array of DatedDecimalPair instances (cache)
	 *
	 * @var DatedDecimalPair[]
	 */
	private $pairs = [];

	/** 
	 * Current iterator position
	 * 
	 * @var int 
	 */
	private $index = 0;

	/** 
	 * Current iteration element
	 * 
	 * @var DatedDecimalPair 
	 */
	private $current;

	/**
	 * Constructor.
	 *
	 * @param TimeSeries $x
	 * @param TimeSeries $y
	 */
	public function __construct(TimeSeries $x, TimeSeries $y)
	{
		$this->x = $x;
		$this->y = $y;
		$this->timestamps = $x->keys()->toArray();
	}

	/**
	 * Returns the current pair.
	 *
	 * @return DatedDecimalPair
	 */
	public function current(): DatedDecimalPair
	{
		return $this->current;
	}

	/**
	 * Returns the current iterator position.
	 *
	 * @return int
	 */
	public function key(): int
	{
		return $this->index;
	}

	/**
	 * Advances the iterator to the next element.
	 */
	public function next(): void
	{
		$this->setPosition($this->index + 1);
	}

	/**
	 * Whether the iterator contains more elements.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return $this->current !== null;
	}

	/**
	 * Moves the iterator to the beginning.
	 */
	public function rewind(): void
	{
		$this->setPosition(0);
	}

	/**
	 * Returns the date from the current pair.
	 * 
	 * Shortcut for current()->getDateTime()
	 *
	 * @return DateTimeImmutable|null
	 */
	public function date(): ?DateTimeImmutable
	{
		return $this->current ? $this->current->getDateTime() : null;
	}

	/**
	 * Peek at the observation at a given offset index, if it exists.
	 * 
	 * This method supports negative indexes, for example:
	 * 	- `peek(-1)` peeks at the last observation
	 * 	- `peek(-3)` peeks at the 3rd to last observation
	 *
	 * @param int $index
	 *
	 * @return DatedDecimalPair|null
	 */
	public function peek(int $index): ?DatedDecimalPair
	{
		if ($index < 0) {
			$index = count($this->timestamps) - $index;
			if ($index < 0) {
				return null;
			}
		}

		if (isset($this->pairs[$index])) {
			return $this->pairs[$index];
		}

		$time = $this->timestamps[$index] ?? null;

		if ($time && $this->y->hasTimestamp($time)) {

			$datetime = new DateTimeImmutable("@$time");
			
			return $this->pairs[$index] = new DatedDecimalPair(
				$this->x->get($datetime),
				$this->y->get($datetime),
				$datetime
			);
		}
		
		return null;
	}

	/**
	 * Peek at the next observation, if one exists.
	 *
	 * @return DatedDecimalPair|null
	 */
	public function peekNext(): ?DatedDecimalPair
	{
		return $this->peek($this->index + 1);
	}

	/**
	 * Peek at the previous observation, if one exists.
	 *
	 * @return DatedDecimalPair|null
	 */
	public function peekPrev(): ?DatedDecimalPair
	{
		if ($this->index > 0) {
			return $this->peek($this->index - 1);
		}

		return null;
	}

	/**
	 * Peeks at an entry given an offset relative to the current index.
	 *
	 * For example:
	 * 	- `peekRel(1)` peeks at the next observation
	 * 	- `peekRel(-1)` peeks at the previous observation
	 * 
	 * @param int $rel_offset
	 *
	 * @return DatedDecimalPair|null
	 */
	public function peekRel(int $rel_offset): ?DatedDecimalPair
	{
		return $this->peek($this->index + $rel_offset);
	}

	/**
	 * Sets the current iterator position.
	 *
	 * @param int $index
	 */
	private function setPosition(int $index): void
	{
		$this->index = $index;
		$this->current = $this->peek($index) ?: null;
	}

}