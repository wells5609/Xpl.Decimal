<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use Countable;
use ArrayAccess;
use IteratorIterator;
use IteratorAggregate;
use Decimal\Decimal;
use Ds\{
	Collection,
	Sequence,
	Vector as DsVector
};
use Xpl\Decimal\Exception\{
	RuntimeException,
	InvalidArgumentException,
	UnexpectedValueException
};

/**
 * A vector of Decimal objects.
 */
class Vector implements IteratorAggregate, ArrayAccess, Countable, Sequence
{

	/**
	 * Internal storage vector
	 *
	 * @var \Ds\Vector
	 */
	private $vector;

	/**
	 * Create a new Vector from an iterable of values.
	 * 
	 * If a non-numeric value is encountered, an exception is thrown if $strict
	 * is true. Otherwise non-numeric values are silently ignored.
	 * 
	 * @throws UnexpectedValueException if $strict is true and a non-numeric value is encounted
	 *
	 * @param iterable $numbers
	 * @param bool $strict [Optional] Default = true
	 *
	 * @return Vector
	 */
	public static function from(iterable $numbers, bool $strict = true): Vector
	{
		$vec = new Vector();

		foreach ($numbers as $num) {
			if ($num instanceof Decimal) {
				$vec->append($num);
			} else if (is_numeric($num)) {
				$vec->append(new Decimal((string)$num));	
			} else if ($strict) {
				throw new UnexpectedValueException("Encountered non-numeric value");
			}
		}

		return $vec;
	}

	/**
	 * Creates a Vector of Decimal instances for the range $min to $max.
	 *
	 * @param int $min
	 * @param int $max
	 * @param int $step [Optional] Default = 1
	 *
	 * @return Vector
	 */
	public static function range(int $min, int $max, int $step = 1): Vector
	{
		$vec = new self();

		for ($i = $min; $i <= $max; $i += $step) {
			$vec->append(new Decimal($i));
		}

		return $vec;
	}

	/**
	 * Constructor.
	 * 
	 * @throws InvalidArgumentException if any given value is not a Decimal instance.
	 * 
	 * @param iterable $values Iterable of Decimal objects.
	 */
	public function __construct(iterable $values = [])
	{
		if ($values) {
			foreach ($values as $v) {
				assert_decimal($v);
			}
		}

		$this->vector = new DsVector($values);
	}

	/**
	 * Clears the vector.
	 */
	public function clear(): void
	{
		$this->vector = new DsVector();
	}

	/**
	 * Returns a copy (clone) of the vector.
	 * 
	 * @return Vector
	 */
	public function copy(): Collection
	{
		return clone $this;
	}

	/**
	 * Returns the number of elements in the vector.
	 * 
	 * @return int
	 */
	public function count(): int
	{
		return count($this->vector);
	}

	/**
	 * Returns an iterator for the vector.
	 * 
	 * @return IteratorIterator
	 */
	public function getIterator(): IteratorIterator
	{
		return new IteratorIterator($this->vector);
	}
	
	/**
	 * Returns whether the vector is empty.
	 * 
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->vector->isEmpty();
	}

	/**
	 * Converts the vector to an array of Decimals.
	 * 
	 * @return Decimal[]
	 */
	public function toArray(): array
	{
		return $this->vector->toArray();
	}

	/**
	 * Returns the data to be JSON-encoded.
	 * 
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return $this->vector->jsonSerialize();
	}

	/**
	 * Allocates enough memory for a required capacity.
	 * 
	 * Implementation only - never necessary to use
	 *
	 * @param int $capacity
	 */
	public function allocate(int $capacity): void
	{
		$this->vector->allocate($capacity);
	}

	/**
	 * Updates all values by applying a callback function to each value.
	 * 
	 * @throws UnexpectedValueException if any given value is not a Decimal instance 
	 * after the callback is applied.
	 *
	 * @param callable $callback
	 */
	public function apply(callable $callback): void
	{
		$this->vector->apply($callback);

		$exception = new UnexpectedValueException(
			"Encountered non-Decimal value after applying callback"
		);

		foreach ($this->vector as $v) {
			assert_decimal($v, $exception);
		}
	}
	
	/**
	 * Returns the current capacity.
	 *
	 * @return int
	 */
	public function capacity(): int
	{
		return $this->vector->capacity();
	}

	/**
	 * Determines if the sequence contains given values.
	 *
	 * Note: If any of the given values are not a Decimal instance, this method
	 * returns false without raising an error.
	 * 
	 * @param Decimal ...$values
	 *
	 * @return bool
	 */
	public function contains(...$values): bool
	{
		return $this->vector->contains(...$values);
	}
	
	/**
	 * Creates a new sequence using a callable to determine which values to include.
	 *
	 * @param callable $callback
	 *
	 * @return Vector
	 */
	public function filter(callable $callback = null): Sequence
	{
		return new self($this->vector->filter($callback));
	}
	
	/**
	 * Attempts to find a value's index.
	 * 
	 * Note: If $value is not a Decimal instance, this method returns false
	 * without raising an error.
	 * 
	 * @param Decimal $value
	 * 
	 * @return int|false
	 */
	public function find($value)
	{
		if (! $value instanceof Decimal) {
			return false;
		}

		return $this->vector->find($value);
	}
	
	/**
	 * Returns the first value in the sequence.
	 * 
	 * @throws UnderflowException if empty.
	 *
	 * @return Decimal
	 */
	public function first(): Decimal
	{
		return $this->vector->first();
	}
	
	/**
	 * Returns the value at a given index.
	 * 
	 * @throws OutOfRangeException if the index is not valid.
	 *
	 * @param int $index The index to access, starting at 0
	 *
	 * @return Decimal
	 */
	public function get(int $index): Decimal
	{
		return $this->vector->get($index);
	}

	/**
	 * Inserts values into the sequence at a given index.
	 * 
	 * @throws OutOfRangeException if the index is not valid.
	 * @throws InvalidArgumentException if any given value is not a Decimal instance.
	 *
	 * @param int $index The index at which to insert. 0 <= index <= count
	 * @param Decimal ...$values
	 */
	public function insert(int $index, ...$values): void
	{
		foreach ($values as $v) {
			assert_decimal($v);
		}

		$this->vector->insert($index, ...$values);
	}

	/**
	 * Joins all values together as a string.
	 *
	 * @param string $glue An optional string to separate each value.
	 *
	 * @return string
	 */
	public function join(string $glue = null): string
	{
		return implode($glue ?: '', $this->vector->toArray());
	}
	
	/**
	 * Returns the last value in the sequence.
	 * 
	 * @throws UnderflowException if empty
	 *
	 * @return Decimal
	 */
	public function last(): Decimal
	{
		return $this->vector->last();
	}

	/**
	 * Returns the result of applying a callback function to each value in the sequence.
	 * 
	 * @param callable $callback A callable to apply to each value, which 
	 * should return what the new value will be in the new sequence.
	 *
	 * @return Vector
	 */
	public function map(callable $callback): Sequence
	{
		return new self($this->vector->map($callback));
	}
	
	/**
	 * Returns the result of adding all given values to the sequence.
	 * 
	 * @throws InvalidArgumentException if $values is not iterable, or if any
	 * of the values it contains is not a Decimal instance.
	 * 
	 * @param iterable $values
	 *
	 * @return Vector
	 */
	public function merge($values): Sequence
	{
		if (! is_iterable($values)) {
			throw new InvalidArgumentException("Expecting array or Traversable object");
		}
		
		foreach ($values as $v) {
			assert_decimal($v);
		}

		return new self($this->vector->merge($values));
	}
	
	/**
	 * Removes and returns the last value.
	 * 
	 * @throws UnderflowException if empty.
	 *
	 * @return Decimal
	 */
	public function pop(): Decimal
	{
		return $this->vector->pop();
	}
	
	/**
	 * Adds values to the end of the sequence.
	 * 
	 * @throws InvalidArgumentException if any given value is not a Decimal instance
	 *
	 * @param Decimal ...$values
	 */
	public function push(...$values): void
	{
		foreach ($values as $v) {
			assert_decimal($v);
		}

		$this->vector->push(...$values);
	}
	
	/**
	 * Reduces the sequence to a single value using a callback function.
	 * 
	 * @throws InvalidArgumentException if $initial is non-null and not a Decimal instance
	 * @throws UnexpectedValueException if $callback returns a non-null value that is not a Decimal instance
	 * 
	 * @param callable $callback `callback ( mixed $carry , mixed $value ) : Decimal|null`
	 * @param Decimal $initial
	 *
	 * @return Decimal|null
	 */
	public function reduce(callable $callback, $initial = null): ?Decimal
	{
		if (! is_null($initial)) {
			assert_decimal($initial);
		}

		$result = $this->vector->reduce($callback, $initial);

		if (is_null($result) || $result instanceof Decimal) {
			return $result;
		}

		throw new UnexpectedValueException(
			"Callback passed to reduce() must return a Decimal instance or null."
		);
	}
	
	/**
	 * Removes and returns a value by index.
	 * 
	 * @throws OutOfRangeException if the index is not valid.
	 *
	 * @param int $index
	 * 
	 * @return Decimal
	 */
	public function remove(int $index): Decimal
	{
		return $this->vector->remove($index);
	}
	
	/**
	 * Reverses the sequence in-place.
	 */
	public function reverse(): void
	{
		$this->vector->reverse();
	}
	
	/**
	 * Returns a reversed copy of the sequence.
	 *
	 * @return Vector
	 */
	public function reversed(): Sequence
	{
		return new self($this->vector->reversed());
	}
	
	/**
	 * Rotates the sequence by a given number of rotations.
	 *
	 * @param int $rotations
	 */
	public function rotate(int $rotations): void
	{
		$this->vector->rotate($rotations);
	}
	
	/**
	 * Updates a value at a given index.
	 *
	 * @throws OutOfRangeException if the index is not valid.
	 * @throws InvalidArgumentException if $value is not a Decimal instance.
	 *  
	 * @param int $index
	 * @param Decimal $value
	 */
	public function set(int $index, $value): void
	{
		assert_decimal($value);

		$this->vector->set($index, $value);
	}
	
	/**
	 * Removes and returns the first value.
	 * 
	 * @throws UnderflowException if empty.
	 *
	 * @return Decimal
	 */
	public function shift(): Decimal
	{
		return $this->vector->shift();
	}
	
	/**
	 * Returns a sub-sequence of a given range.
	 *
	 * @param int $index
	 * @param int $length
	 *
	 * @return Vector
	 */
	public function slice(int $index, int $length = null): Sequence
	{
		return new self($this->vector->slice($index, $length));
	}
	
	/**
	 * Sorts the sequence in-place.
	 *
	 * @param callable $comparator
	 */
	public function sort(callable $comparator = null): void
	{
		$this->vector->sort($comparator);
	}
	
	/**
	 * Returns a sorted copy of the sequence.
	 *
	 * @param callable $comparator
	 *
	 * @return Vector
	 */
	public function sorted(callable $comparator = null): Sequence
	{
		return new self($this->vector->sorted($comparator));
	}
	
	/**
	 * Adds values to the front of the sequence.
	 * 
	 * @throws InvalidArgumentException if any given value is not a Decimal instance.
	 *
	 * @param Decimal ...$values
	 */
	public function unshift(...$values): void
	{
		foreach ($values as $v) {
			assert_decimal($v);
		}

		$this->vector->unshift(...$values);
	}

	/** 
	 * Alias of get() 
	 */
	public function offsetGet($offset)
	{
		return $this->get((int)$offset);
	}

	/**
	 * Check if a value exists at the given offset.
	 *
	 * @param int $offset
	 * 
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return $offset >= 0 && $offset < count($this->vector);
	}

	/**
	 * Sets the value at the given offset (same as set()) or pushes a value
	 * onto the end of the sequence.
	 *
	 * @throws InvalidArgumentException if $value is not a Decimal instance.
	 * 
	 * @param int|null $offset
	 * @param Decimal $value
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset === null) {
			assert_decimal($value);
			$this->append($value);
		} else {
			$this->set((int)$offset, $value);
		}
	}

	/** 
	 * Alias of remove() 
	 */
	public function offsetUnset($offset)
	{
		$this->remove((int)$offset);
	}

	/**
	 * Fast push() for a single Decimal value.
	 *
	 * @param Decimal $decimal
	 */
	public function append(Decimal $decimal): void
	{
		$this->vector->push($decimal);
	}

	/**
	 * Returns an array of Vectors by breaking $this into chunks of the given size.
	 * 
	 * @throws InvalidArgumentException if $size is negative
	 *
	 * @param int $size
	 *
	 * @return array<Vector>
	 */
	public function chunk(int $size): array
	{
		if ($size < 0) {
			// @todo: should this be an OutOfRangeException ?
			throw new InvalidArgumentException("Size must not be negative");
		} else if ($size === 0) {
			return [new Vector()];
		} else if ($size === count($this)) {
			return [$this->copy()];
		}

		$array = [];

		foreach (array_chunk($this->toArray(), $size, false) as $arr) {
			$array[] = new Vector($arr);
		}

		return $array;
	}

	/**
	 * Pads a copy of $this to the given size using the given value.
	 * 
	 * @throws InvalidArgumentException if $size is negative or less than the current size
	 *
	 * @param int $size A positive integer
	 * @param Decimal $value [Optional] Defaults to Decimal(0)
	 *
	 * @return Vector
	 */
	public function pad(int $size, Decimal $value = null): Vector
	{
		if ($size < 0) {
			// @todo: should this be an OutOfRangeException ?
			throw new InvalidArgumentException("Size must not be negative");
		} else if ($size === 0) {
			return new Vector();
		} 
		
		$n = count($this);

		if ($size < $n) {
			// @todo: should this be an OutOfRangeException ?
			throw new InvalidArgumentException("Cannot pad to less than current size");
		} else if ($size === $n) {
			return $this->copy();
		}

		if ($value === null) {
			$value = new Decimal(0);
		}
		
		$vec = $this->copy();
		
		for ($i = $n; $i < $size; $i++) {
			$vec->append($value);
		}

		return $vec;
	}

	/**
	 * ================================================================
	 * 							MATH METHODS
	 * ================================================================
	 */

	/**
	 * Returns the maximum value in the sequence.
	 *
	 * @return Decimal
	 */
	public function max(): Decimal
	{
		$max = $this->first();

		foreach ($this->vector as $x) {
			if ($x > $max) {
				$max = $x;
			}
		}

		return $max;
	}

	/**
	 * Returns the minimum value in the sequence.
	 *
	 * @return Decimal
	 */
	public function min(): Decimal
	{
		$min = $this->first();

		foreach ($this->vector as $x) {
			if ($x < $min) {
				$min = $x;
			}
		}

		return $min;
	}

	/**
	 * Returns the sum of all values in the sequence.
	 * 
	 * @return Decimal
	 */
	public function sum(): Decimal
	{
		return Decimal::sum($this->vector);
	}

	/**
	 * Returns the average of all values in the sequence.
	 *
	 * @return Decimal
	 */
	public function mean(): Decimal
	{
		return Decimal::avg($this->vector);
	}

	/** Alias of mean() */
	public function avg(): Decimal { return $this->mean(); }

	/**
	 * Returns the product of all values in the sequence.
	 * 
	 * If the vector is empty, returns a Decimal with value '1'. This behavior
	 * is consistent with that of `array_product()`
	 *
	 * @return Decimal
	 */
	public function prod(): Decimal
	{
		$prod = new Decimal(1);

		foreach ($this->vector as $v) {
			$prod = $prod->mul($v);
		}

		return $prod;
	}

	/**
	 * Returns a Vector of the difference between each value.
	 * 
	 * This is equivalent to building a new Vector by iterating over each 
	 * index > 0, `i`, and pushing the result of `$this[i] - $this[i-1]`.
	 *
	 * @return Vector
	 */
	public function delta(): Vector
	{
		$vec = new Vector();

		foreach ($this->vector as $i => $v) {
			
			if ($i === 0) {
				$p = $v;
				continue;
			}

			$vec->append($v - $p);

			$p = $v;
		}
	}

	/**
	 * Returns a Vector of the difference between each value as a percentage.
	 * 
	 * This is equivalent to building a new Vector by iterating over each 
	 * index > 0, `i`, and pushing the result of `($this[i] - $this[i-1]) / $this[i-1]`.
	 *
	 * @return Vector
	 */
	public function rdelta(): Vector
	{
		$vec = new Vector();

		foreach ($this->vector as $i => $v) {
			
			if ($i === 0) {
				$p = $v;
				continue;
			}

			$vec->append(($v - $p) / $p);

			$p = $v;
		}
	}

	/**
	 * Returns a Vector of the difference between each value in $this and $y.
	 *
	 * This is equivalent to building a new Vector by iterating over each
	 * index, `i`, in $this and pushing the result of `$this[i] - $y[i]`.
	 * 
	 * @throws RuntimeException if the given Vector's count is not the same as $this
	 * 
	 * @param Vector $y
	 *
	 * @return Vector
	 */
	public function diff(Vector $y): Vector
	{
		if ($y->count() !== $this->count()) {
			throw new RuntimeException("Vectors must contain the same number of elements");
		}

		$vec = new Vector();

		foreach ($this->vector as $i => $v) {
			$vec->append($v->sub($y->get($i)));
		}

		return $vec;
	}

	/* ================================================================
	 * 							STATISTICS
	 * ============================================================= */

	/**
	 * Calculate the central moment given a power $k.
	 * 
	 * A central moment is a moment of a probability distribution of a random
	 * variable about the random variable's mean; that is, it is the expected
	 * value of a specified integer power ($k) of the deviation of the random
	 * variable from the mean.
	 * 
	 * @link https://en.wikipedia.org/wiki/Central_moment
	 *
	 * @param int $k
	 * @param bool $sample [Optional] Default = true
	 *
	 * @return Decimal
	 */
	public function centralMoment(int $k, bool $sample = true): Decimal
	{
		$mean = $this->mean();
		$count = $sample ? $this->count() - 1 : $this->count();
		$vec = new Vector();
		
		foreach ($this->vector as $v) {
			$vec->append($v->sub($mean)->pow($k));
		}

		return $vec->sum()->div($count);
	}

	/**
	 * Calculate skewness.
	 * 
	 * Skewness is a measure of the asymmetry of the probability distribution
	 * of a real-valued random variable about its mean.
	 * 
	 * @link https://en.wikipedia.org/wiki/Skewness
	 *
	 * @return Decimal
	 */
	public function skewness(): Decimal
	{
		return $this->centralMoment(3)->div(
			$this->centralMoment(2)->pow((new Decimal(2))->div(3))
		);
	}

	/**
	 * Calculates the regression sum of squares.
	 * 
	 * The regression sum of squares is a quantity used in describing how well 
	 * a model, often a regression model, represents the data being modelled. 
	 * In particular, the regression sum of squares measures how much variation
	 * there is in the modelled values.
	 * 
	 * Also known as:
	 *	- Explained sum of squares
	 *	- Model sum of squares
	 * 
	 * @link https://en.wikipedia.org/wiki/Explained_sum_of_squares
	 * 
	 * @throws InvalidArgumentException if $ybar is not a Decimal or Vector instance.
	 * 
	 * @param Decimal|Vector $ybar Response variable mean, or a Vector from which to calculate it
	 *
	 * @return Decimal
	 */
	public function regressionSumOfSquares($ybar): Decimal
	{
		if ($ybar instanceof Vector) {
			$ybar = $ybar->mean();
		} else if (! $ybar instanceof Decimal) {
			throw new InvalidArgumentException("Argument must be Decimal or Vector");
		}

		$squares = new Vector();

		foreach ($this->vector as $v) {
			$squares->append($v->sub($ybar)->pow(2));
		}

		return $squares->sum();
	}

	/**
	 * Calculate residual sum of squares (RSS).
	 * 
	 * The residual sum of squares is the sum of the squares of residuals 
	 * (deviations predicted from actual empirical values of data). It is a 
	 * measure of the discrepancy between the data and an estimation model. A 
	 * small RSS indicates a tight fit of the model to the data. It is used as 
	 * an optimality criterion in parameter selection and model selection.
	 * 
	 * Also known as:
	 *	- Sum of squared residuals (SSR)
	 *	- Sum of squared errors of prediction (SSE)
	 *
	 * @link https://en.wikipedia.org/wiki/Residual_sum_of_squares
	 *
	 * @throws RuntimeException if the given Vector's count is not the same as $this
	 * 
	 * @param Vector $y
	 *
	 * @return Decimal
	 */
	public function residualSumOfSquares(Vector $y): Decimal
	{
		if ($y->count() !== $this->count()) {
			throw new RuntimeException("Vectors must contain the same number of elements");
		}

		$squares = new Vector();

		foreach ($this->vector as $i => $v) {
			$squares->append($v->sub($y->get($i))->pow(2));
		}

		return $squares->sum();
	}

	/**
	 * Returns the total sum of squares.
	 * 
	 * The total sum of squares (TSS or SST) is a quantity that appears as part
	 * of a standard way of presenting results of such analyses. It is defined 
	 * as being the sum, over all observations, of the squared differences of 
	 * each observation from the overall mean. 
	 * 
	 * In statistical linear models, (particularly in standard regression 
	 * models), the TSS is the sum of the squares of the difference of the 
	 * dependent variable and its mean. For wide classes of linear models, the 
	 * total sum of squares equals the explained sum of squares plus the 
	 * residual sum of squares.
	 * 
	 * @link https://en.wikipedia.org/wiki/Total_sum_of_squares
	 *
	 * @throws RuntimeException if the given Vector's count is not the same as $this
	 * 
	 * @param Vector $y
	 *
	 * @return Decimal
	 */
	public function totalSumOfSquares(Vector $y): Decimal
	{
		return $this->regressionSumOfSquares($y) + $this->residualSumOfSquares($y);
	}

	/**
	 * Calculate variance, treating the data as a sample.
	 * 
	 * Variance is the expectation of the squared deviation of a random 
	 * variable from its mean. Informally, it measures how far a set of 
	 * (random) numbers are spread out from their average value.
	 * 
	 * @link https://en.wikipedia.org/wiki/Variance
	 *
	 * @return Decimal
	 */
	public function var(): Decimal
	{
		return $this->centralMoment(2);
	}

	/**
	 * Calculate covariance.
	 * 
	 * Covariance is a measure of the joint variability of two random variables.
	 * If the greater values of one variable mainly correspond with the greater
	 * values of the other variable, and the same holds for the lesser values, 
	 * (i.e., the variables tend to show similar behavior), the covariance is 
	 * positive. In the opposite case, when the greater values of one variable
	 * mainly correspond to the lesser values of the other, (i.e., the 
	 * variables tend to show opposite behavior), the covariance is negative. 
	 * The sign of the covariance therefore shows the tendency in the linear 
	 * relationship between the variables. 
	 * 
	 * The magnitude of the covariance is not easy to interpret because it is 
	 * not normalized and hence depends on the magnitudes of the variables. The 
	 * normalized version of the covariance, the correlation coefficient, 
	 * however, shows by its magnitude the strength of the linear relation.
	 *
	 * @link https://en.wikipedia.org/wiki/Covariance
	 * 
	 * @throws RuntimeException if the given Vector's count is not the same as $this
	 * 
	 * @param Vector $y Response variable data
	 *
	 * @return Decimal
	 */
	public function covar(Vector $y): Decimal
	{
		if ($y->count() !== $this->count()) {
			throw new RuntimeException("Vectors must contain the same number of elements");
		}

		$n = $this->count();
		$a = $this->sum()->mul($y->sum())->div($n);
		$sum = new Decimal(0);

		foreach ($this->vector as $i => $v) {
			$sum = $sum->add($v->mul($y->get($i)));
		}

		return $sum->sub($a)->div($n);
	}

	/**
	 * Calculate standard deviation, treating the data as a sample.
	 * 
	 * The standard deviation (SD, or sigma, σ) is a measure that is used to 
	 * quantify the amount of variation or dispersion of a set of data values.
	 * A low standard deviation indicates that the data points tend to be close 
	 * to the mean (also called the expected value) of the set, while a high 
	 * standard deviation indicates that the data points are spread out over a 
	 * wider range of values.
	 *
	 * @link https://en.wikipedia.org/wiki/Standard_deviation
	 * 
	 * @return Decimal
	 */
	public function stdev(): Decimal
	{
		return $this->var()->sqrt();
	}

	/**
	 * Calculates the coefficient of variation (CV).
	 * 
	 * The coefficient of variation is a standardized measure of dispersion of
	 * a probability distribution or frequency distribution that shows the
	 * extent of variability in relation to the mean. It is often expressed as
	 * a percentage, and is defined as the ratio of the standard deviation to
	 * the mean.
	 * 
	 * @link https://en.wikipedia.org/wiki/Coefficient_of_variation
	 * 
	 * Also known as:
	 *	- Relative standard deviation (RSD)
	 *
	 * @return Decimal
	 */
	public function coefficientOfVariation(): Decimal
	{
		return $this->stdev()->div($this->mean());
	}

	/** Alias of coefficientOfVariation() */
	public function relstdev(): Decimal { return $this->coefficientOfVariation(); }

	/**
	 * Calculates the index of dispersion.
	 * 
	 * The index of dispersion is a normalized measure of the dispersion of a
	 * probability distribution. It is used to quantify whether a set of 
	 * observed occurrences are clustered or dispersed compared to a standard
	 * statistical model.
	 * 
	 * Also known as:
	 *	- Dispersion index
	 *	- Coefficient of dispersion
	 *	- Relative variance
	 *	- Variance-to-mean ratio (VMR)
	 *
	 * @link https://en.wikipedia.org/wiki/Index_of_dispersion
	 *
	 * @return Decimal
	 */
	public function indexOfDispersion(): Decimal
	{
		return $this->var()->div($this->mean());
	}

	/** Alias of indexOfDispersion() */
	public function relvar(): Decimal { return $this->indexOfDispersion(); }

	/**
	 * Calculates the Pearson correlation coefficient.
	 * 
	 * Generally, correlation refers to any of several specific types of 
	 * relationship between mean values. There are several correlation 
	 * coefficients measuring the degree of correlation, the most common of
	 * which is the Pearson correlation coefficient (PCC).
	 * 
	 * The PCC is a measure of the linear correlation between two variables. 
	 * It has a value between +1 and −1, where 1 is total positive linear 
	 * correlation, 0 is no linear correlation, and −1 is total negative 
	 * linear correlation.
	 * 
	 * Note that when using the PCC, a linear relationship between two 
	 * variables may be present even when one variable is a nonlinear function
	 * of the other.
	 * 
	 * Also known as:
	 *	- Pearson's r
	 *	- Pearson product-moment correlation coefficient (PPMCC)
	 *	- Bivariate correlation
	 *
	 * @link https://en.wikipedia.org/wiki/Correlation_and_dependence
	 * @link https://en.wikipedia.org/wiki/Pearson_correlation_coefficient
	 * 
	 * @param Vector $y Response variable data
	 *
	 * @return Decimal
	 */
	public function correl(Vector $y): Decimal
	{
		return $this->covar($y)->div($this->stdevp()->mul($y->stdevp()));
	}

	/** Alias of correl() */
	public function pearsonR(Vector $y): Decimal { return $this->correl($y); }

	/* ================================================================
	 * 					POPULATION STATISTICS
	 * ============================================================= */

	/**
	 * Calculate variance, treating the data as a population.
	 *
	 * @return Decimal
	 */
	public function varp(): Decimal
	{
		return $this->centralMoment(2, false);
	}

	/**
	 * Calculate standard deviation, treating the data as a population.
	 *
	 * @return Decimal
	 */
	public function stdevp(): Decimal
	{
		return $this->varp()->sqrt();
	}

}