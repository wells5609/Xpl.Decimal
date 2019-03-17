<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use Traversable;
use Decimal\Decimal;
use Xpl\Decimal\Exception\{
	Exception,
	InvalidArgumentException,
	UnexpectedValueException
};

/**
 * Check that $value is an instance of Decimal and if not, throw an exception.
 * 
 * @throws InvalidArgumentException if $value is not a Decimal instance
 *
 * @param mixed $value
 * @param Exception $exception [Optional]
 */
function assert_decimal($value, Exception $exception = null)
{
	if (! $value instanceof Decimal) {
		throw $exception ?: new InvalidArgumentException(sprintf(
			"Expected instance of Decimal\\Decimal, given: %s", 
			is_object($value) ? get_class($value) : gettype($value)
		));
	}
}

/**
 * Returns a Decimal instance from a given numeric value.
 *
 * @throws InvalidArgumentException if $number is not a Decimal, int, float, or numeric string
 * 
 * @param Decimal|int|float|string $number
 * @param int $precision
 *
 * @return Decimal
 */
function to_decimal($number, int $precision = Decimal::DEFAULT_PRECISION): Decimal
{
	if ($number instanceof Decimal) {
		return $number;
	} else if (is_float($number)) {
		return new Decimal((string)$number, $precision);
	} else if (is_numeric($number)) {
		return new Decimal($number, $precision);
	}

	throw new InvalidArgumentException(sprintf(
		"Number must be Decimal, int, float, or numeric string, given: %s",
		is_object($number) ? get_class($number) : gettype($number)
	));
}

/**
 * Parses a string to a numeric value and returns it as a Decimal instance.
 * 
 * Use this function to parse:
 * 	- Percentage strings (e.g. "23.5%" -> 0.235)
 * 	- Formatted numbers (e.g. "2,345" -> 2345)
 * 	- Formatted monetary numbers (e.g. "$ 123.00" -> 123.00)
 * 
 * @throws UnexpectedValueException if the string cannot be parsed to a number
 *
 * @param string $value
 * @param int $precision [Optional]
 *
 * @return Decimal
 */
function parse_to_decimal(string $value, int $precision = Decimal::DEFAULT_PRECISION): Decimal
{
	if (strpos($value, '%') !== false) {
		$value = trim(str_replace('%', '', $value));
		if (is_numeric($value)) {
			return (new Decimal($value, $precision))->div(100);
		}
	} else {
		$number = filter_var(
			$value, 
			FILTER_SANITIZE_NUMBER_FLOAT, 
			FILTER_FLAG_ALLOW_FRACTION|FILTER_FLAG_ALLOW_SCIENTIFIC
		);
		if (is_numeric($number)) {
			return new Decimal((string)$number, $precision);
		}
	}

	throw new UnexpectedValueException("String could not be parsed to a number");
}

/**
 * Returns pi as a Decimal to the given number of decimal digits.
 * 
 * Note: Unlike Decimal precision, the precision given is the number of digits
 * after the decimal point, NOT the number of significant digits.
 *
 * @param int $digits [Optional] Default = 34
 *
 * @return Decimal
 */
function pi(int $digits = IEEE_DECIMAL128): Decimal
{
	return new Decimal(PI, $digits + 1);
}

/**
 * Returns the cube root of $x (³√x)
 *
 * @param Decimal $x
 *
 * @return Decimal
 */
function cuberoot(Decimal $x): Decimal
{
	if ($x >= 0) {
		return $x->pow((new Decimal(1, IEEE_DECIMAL128))->div(3));
	}

	return $x->abs()->pow((new Decimal(1, IEEE_DECIMAL128))->div(3))->negate();
}

/**
 * Returns the greatest common denominator/divisor of $a and $b - the largest
 * positive integer that divides both numbers without a remainder.
 *
 * @param Decimal $a
 * @param Decimal $b
 *
 * @return Decimal
 */
function gcd(Decimal $a, Decimal $b): Decimal
{
	if ($a->isZero() || $b->isZero()) {
		return new Decimal(0);
	}

	return gcd($b, $a->mod($b));
}

/**
 * Returns the least common multiple of $a and $b - the smallest positive 
 * integer that is divisible by both numbers.
 *
 * @param Decimal $a
 * @param Decimal $b
 *
 * @return Decimal
 */
function lcm(Decimal $a, Decimal $b): Decimal
{
	if ($a->isZero() || $b->isZero()) {
		return new Decimal(0);
	}

	$gcd = gcd($a, $b);

	return $a->mul($b)->abs()->div($gcd);
}


/**
 * Returns an array of Decimal instances for the range $min to $max.
 * 
 * @see Vector::range() to create a range of Decimals as a Vector
 *
 * @param int $min
 * @param int $max
 * @param int $step [Optional] Default = 1
 *
 * @return Decimal[]
 */
function range(int $min, int $max, int $step = 1): array
{
	return array_map(
		function ($n) { return new Decimal($n); }, 
		range($min, $max, $step)
	);
}

/**
 * Returns the maximum number among the given arguments.
 * 
 * If only 1 argument is provided and it is an array of Traversable object,
 * its elements will be used as the numbers, which is the same behavior as the 
 * standard PHP function max().
 * 
 * Note: This function works with any number, including Decimal instances, 
 * integers, floats, and numeric strings.
 *
 * @param Decimal|number|array|Traversable $value1
 * @param Decimal|number ...$values
 *
 * @return Decimal
 */
function max($value1, ...$values): Decimal
{
	if (func_num_args() === 1) {
		
		if ($value1 instanceof Vector) {
			return $value1->max();
		} else if ($value1 instanceof Traversable) {
			return max(...iterator_to_array($value1, false));
		} else if (is_array($value1)) {
			return max(...$value1);
		}

		return to_decimal($value1);
	}

	$max = $value1;

	foreach ($values as $dec) {
		if ($dec > $max) {
			$max = $dec;
		}
	}

	return to_decimal($max);
}

/**
 * Returns the minimum number among the given arguments.
 * 
 * If only 1 argument is provided and it is an array of Traversable object,
 * its elements will be used as the numbers, which is the same behavior as the 
 * standard PHP function min().
 * 
 * Note: This function works with any number, including Decimal instances, 
 * integers, floats, and numeric strings.
 *
 * @param Decimal|number|array|Traversable $value1
 * @param Decimal|number ...$values
 *
 * @return Decimal
 */
function min($value1, ...$values): Decimal
{
	if (func_num_args() === 1) {
		
		if ($value1 instanceof Vector) {
			return $value1->min();
		} else if ($value1 instanceof Traversable) {
			return min(...iterator_to_array($value1, false));
		} else if (is_array($value1)) {
			return min(...$value1);
		}

		return to_decimal($value1);
	}

	$min = $value1;

	foreach ($values as $dec) {
		if ($dec < $min) {
			$min = $dec;
		}
	}

	return to_decimal($min);
}

/**
 * Returns the sum of all values.
 * 
 * If only 1 argument is provided and it is an array of Traversable object,
 * its elements will be used as the numbers.
 * 
 * Note: This function works with any number, including Decimal instances, 
 * integers, floats, and numeric strings.
 *
 * @param Decimal|number|array|Traversable $value1
 * @param Decimal|number ...$values
 * 
 * @return Decimal
 */
function sum($value1, ...$values): Decimal
{
	if (func_num_args() === 1) {
		
		if ($value1 instanceof Vector) {
			return $value1->sum();
		} else if ($value1 instanceof Traversable) {
			return sum(...iterator_to_array($value1, false));
		} else if (is_array($value1)) {
			return sum(...$value1);
		}

		return to_decimal($value1);
	}

	return to_decimal($value1) + Decimal::sum($values);
}

/**
 * Returns the average (arithmetic mean) of all values.
 *
 * Note: This function works with any number, including Decimal instances, 
 * integers, floats, and numeric strings.
 *
 * @param Decimal|number|array|Traversable $value1
 * @param Decimal|number ...$values
 * 
 * @return Decimal
 */
function mean($value1, ...$values): Decimal
{
	if (func_num_args() === 1) {
		
		if ($value1 instanceof Vector) {
			return $value1->mean();
		} else if ($value1 instanceof Traversable) {
			return mean(...iterator_to_array($value1, false));
		} else if (is_array($value1)) {
			return mean(...$value1);
		}

		return to_decimal($value1);
	}

	$values[] = $value1;

	return Decimal::avg($values);
}

/**
 * Returns the product of all values in the sequence.
 * 
 * If only one number is present, returns a Decimal with value '1'. This 
 * behavior is consistent with that of `array_product()`
 *
 * Note: This function works with any number, including Decimal instances, 
 * integers, floats, and numeric strings.
 *
 * @param Decimal|number|array|Traversable $value1
 * @param Decimal|number ...$values
 * 
 * @return Decimal
 */
function prod($value1, ...$values): Decimal
{
	if (func_num_args() === 1) {

		if ($value1 instanceof Vector) {
			return $value1->prod();
		} else if ($value1 instanceof Traversable) {
			return prod(...iterator_to_array($value1, false));
		} else if (is_array($value1)) {
			return prod(...$value1);
		}

		return to_decimal($value1);
	}

	$prod = new Decimal(1);

	foreach ($values as $v) {
		$prod = $prod->mul($v);
	}

	return $prod;
}
