<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use Decimal\Decimal;

/**
 * Represents a pair of Decimals.
 * 
 * @property-read \Decimal\Decimal $x
 * @property-read \Decimal\Decimal $y
 */
class DecimalPair
{

	/**
	 * Decimal representing the X value
	 *
	 * @var Decimal
	 */
	protected $x;

	/**
	 * Decimal representing the Y value
	 *
	 * @var Decimal
	 */
	protected $y;

	/**
	 * Constructor.
	 *
	 * @param Decimal $x
	 * @param Decimal $y
	 */
	public function __construct(Decimal $x, Decimal $y) 
	{
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 * Magic get for read-only property access
	 *
	 * @param string $key
	 */
	public function __get($key)
	{
		return $this->$key;
	}

	/**
	 * Magic isset for read-only property access
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->$key);
	}

	/**
	 * Returns the X value
	 *
	 * @return Decimal
	 */
	public function getX(): Decimal
	{
		return $this->x;
	}

	/**
	 * Returns the Y value
	 *
	 * @return Decimal
	 */
	public function getY(): Decimal
	{
		return $this->y;
	}

	/**
	 * Returns the difference between Y and X
	 *
	 * @return Decimal
	 */
	public function diff(): Decimal
	{
		return $this->y->sub($this->x);
	}

}
