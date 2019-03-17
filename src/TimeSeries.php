<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use DatePeriod;
use DateInterval;
use DateTimeInterface;
use DateTimeImmutable;
use IteratorAggregate;
use IteratorIterator;
use Decimal\Decimal;
use Ds\{
	Map, 
	Set, 
	Pair, 
	Vector as DsVector, 
	Sequence, 
	Collection
};
use Xpl\DateTime\Interval;
use Xpl\Decimal\Exception\{
	RuntimeException,
	InvalidArgumentException
};

/**
 * A map of associations between DateTimeInterface and Decimal.
 * 
 * i.e. TimeSeries<DateTimeInterface, Decimal>
 * 
 * Or at least that's how it appears... Internally, the map is keyed with Unix
 * timestamps because object keys in a Ds\Map must implement Ds\Hashable, and 
 * wrapping DateTime[Immutable] to implement Hashable is costly.
 */
class TimeSeries implements IteratorAggregate, Collection
{

	/**
	 * Map of timestamps to Decimal instances
	 * 
	 * @var Ds\Map
	 */
	private $map;

	/**
	 * Whether the series is chronologically sorted
	 *
	 * @var bool
	 */
	private $sorted = false;

	/**
	 * Constructor.
	 * 
	 * @throws InvalidArgumentException if any value is not a Decimal instance,
	 * or if any key is not an integer or DateTimeInterface.
	 * 
	 * @param iterable $values [Optional] Map of date/time to Decimal objects
	 */
	public function __construct(iterable $values = []) 
	{
		$this->map = new Map();

		if ($values) {
			$this->hydrate($values);
		}
	}
	
	/**
	 * Associates a Decimal with a DateTimeInterface.
	 *
	 * @param DateTimeInterface $key
	 * @param Decimal $value
	 */
	public function set(DateTimeInterface $key, Decimal $value)
	{
		$this->map->put($key->getTimestamp(), $value);

		$this->sorted = false;
	}

	/**
	 * Checks if there is a Decimal associated with the given date/time.
	 *
	 * @param DateTimeInterface $key
	 * 
	 * @return bool
	 */
	public function has(DateTimeInterface $key): bool
	{
		return $this->map->hasKey($key->getTimestamp());
	}

	/**
	 * Check if there is a Decimal for a date/time given as Unix timestamp.
	 *
	 * @param int $timestamp
	 *
	 * @return bool
	 */
	public function hasTimestamp(int $timestamp): bool
	{
		return $this->map->hasKey($timestamp);
	}

	/**
	 * Returns the Decimal associated with the given date/time.
	 * 
	 * @throws RuntimeException if no value is associated with the given date/time.
	 *
	 * @param DateTimeInterface $key
	 *
	 * @return Decimal
	 */
	public function get(DateTimeInterface $key): Decimal
	{
		$time = $key->getTimestamp();

		if ($this->map->hasKey($time)) {
			return $this->map->get($time);
		}

		throw new RuntimeException("No entry for $datetime");
	}

	/**
	 * Removes the Decimal associated with the given date/time.
	 *
	 * @param DateTimeInterface $key
	 */
	public function unset(DateTimeInterface $key)
	{
		$time = $key->getTimestamp();

		if ($this->map->hasKey($time)) {
			$this->map->remove($time);
			$this->sorted = false;
		}
	}

	/**
	 * Clears the collection.
	 */
	public function clear(): void
	{
		$this->map = new Map();
	}

	/**
	 * Returns a copy (clone).
	 *
	 * @return Collection
	 */
	public function copy(): Collection
	{
		return clone $this;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->map);
	}

	/**
	 * Returns an iterator that yields DateTimeImmutable keys and Decimal values.
	 * 
	 * @return \Xpl\Decimal\TimeSeries\Iterator
	 */
	public function getIterator(): TimeSeries\Iterator
	{
		$this->sort();

		return new TimeSeries\Iterator($this);
	}

	/**
	 * Whether the time series is empty.
	 * 
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->map->isEmpty();
	}

	/**
	 * Returns an associative array of Unix timestamps and Decimal instances.
	 * 
	 * @return array
	 */
	public function toArray(): array
	{
		$this->sort();

		return $this->map->toArray();
	}

	/**
	 * Returns a two-dimensional array of DateTime and Decimal instances.
	 *
	 * @return array
	 */
	public function toArrayPairs(): array
	{
		$this->sort();

		$array = [];

		foreach ($this->map as $timestamp => $decimal) {
			$array[$timestamp] = [
				new DateTimeImmutable("@$timestamp"), 
				$decimal
			];
		}

		return $array;
	}

	/**
	 * Returns the internal Ds\Map of timestamps and decimals.
	 *
	 * @return Map
	 */
	public function getMap(): Map
	{
		$this->sort();

		return $this->map;
	}

	/**
	 * Returns the data to be JSON-encoded.
	 * 
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Ensures the series is date-sorted.
	 * 
	 * Note that the series is always sorted in ascending order (i.e. earlier
	 * date/times are earlier in the collection).
	 * 
	 * Normally, users should not need to call this method, as it is called
	 * internally by all methods that return multiple elements.
	 */
	public function sort(): void
	{
		if (! $this->sorted) {
			$this->map->ksort();
			$this->sorted = true;
		}
	}

	/**
	 * Returns a Ds\Set of Unix timestamps for the series.
	 *
	 * @return Set<int>
	 */
	public function keys(): Set
	{
		$this->sort();

		return $this->map->keys();
	}

	/**
	 * Returns a Vector of the Decimal instances in the series.
	 *
	 * @return Vector<Decimal>
	 */
	public function values(): Vector
	{
		$this->sort();

		return new Vector($this->map->values());
	}

	/**
	 * Returns the first Pair in the series, determined chronologically.
	 *
	 * @return Pair
	 */
	public function first(): Pair
	{
		$this->sort();

		return $this->map->first();
	}

	/**
	 * Returns the last Pair in the series, determined chronologically.
	 *
	 * @return Pair
	 */
	public function last(): Pair
	{
		$this->sort();
		
		return $this->map->last();
	}

	/**
	 * Returns the first key in the series as a DateTimeImmutable, determined chronologically.
	 *
	 * @return DateTimeImmutable
	 */
	public function firstDateTime(): DateTimeImmutable
	{
		return new DateTimeImmutable('@'.$this->first()->key);
	}

	/**
	 * Returns the last key in the series as a DateTimeImmutable, determined chronologically.
	 *
	 * @return DateTimeImmutable
	 */
	public function lastDateTime(): DateTimeImmutable
	{
		return new DateTimeImmutable('@'.$this->last()->key);
	}

	/**
	 * Returns a DatePeriod representing the dates in the time series.
	 * 
	 * The interval used to create the period is determined by finding the
	 * minumum interval between all dates in the series. Therefore, if your 
	 * data has inconsistent intervals between dates, the returned DatePeriod 
	 * will include DateTime instances for which there is no associated data.
	 * 
	 * For example, given the following TimeSeries:
	 * 
	 * 	$ts->set(new DateTime('2019-01-01'), new Decimal(1));
	 * 	$ts->set(new DateTime('2019-01-02'), new Decimal(2));
	 * 	$ts->set(new DateTime('2019-01-31'), new Decimal(3));
	 *
	 * The minimum DateInterval is 1 day (1/1 to 1/2), and therefore the 
	 * DatePeriod will include a DateTime for every day between 1/1 and 1/31,
	 * inclusive.
	 *
	 * @return DatePeriod
	 */
	public function getDatePeriod(): DatePeriod
	{
		$interval = $this->getMinDateInterval();

		return new DatePeriod(
			$this->firstDateTime(),
			$interval,
			$this->lastDateTime()->add($interval) // add the interval to include the last date
		);
	}

	/**
	 * Returns the smallest interval between dates in the series, in seconds.
	 * 
	 * @see TimeSeries::getDatePeriod() for information on usage 
	 *
	 * @return DateInterval
	 */
	public function getMinDateInterval(): DateInterval
	{
		$previous = $this->firstDateTime();
		$minimum = PHP_INT_MAX;

		foreach ($this->map as $timestamp => $_) {

			$datetime = new DateTimeImmutable("@$timestamp");
			$diff = Interval::toSeconds($datetime->diff($previous));
			
			if ($diff < $minimum && $diff > 0) {
				// guard against 0 interval, as first iteration compares to itself
				$minimum = $diff;
			}
			
			$previous = $datetime;
		}

		return new DateInterval("PT{$minimum}S");
	}

	/**
	 * Returns a Ds\Vector of DateIntervals representing the interval between
	 * each entry in the series.
	 * 
	 * @return Sequence
	 */
	public function getDateIntervals(): Sequence
	{
		$intervals = new DsVector();
		$previous = $this->firstDateTime();

		foreach ($this->map as $timestamp => $_) {
			$datetime = new DateTimeImmutable("@$timestamp");
			$intervals[] = $datetime->diff($previous);
			$previous = $datetime;
		}

		return $intervals;
	}

	/**
	 * Hydrates the series from a user-supplied iterable.
	 * 
	 * @throws InvalidArgumentException if any value is not a Decimal instance.
	 *
	 * @param iterable $map
	 */
	protected function hydrate(iterable $map)
	{
		foreach ($map as $key => $value) {
			assert_decimal($value);
			$this->set($this->toDateTime($key), $value);
		}
	}

	/**
	 * Returns a DateTimeInterface from a timestamp or DateTimeInterface.
	 * 
	 * @throws InvalidArgumentException if $time is not a DateTimeInterface or timestamp
	 *
	 * @param mixed $time
	 *
	 * @return DateTimeInterface
	 */
	protected function toDateTime($time): DateTimeInterface
	{
		if ($time instanceof DateTimeInterface) {
			return $time;
		} else if (is_numeric($time)) {
			return new DateTimeImmutable("@$time");
		}

		throw new InvalidArgumentException("Expecting timestamp or instance of DateTimeInterface");
	}

}