<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use Decimal\Decimal;
use Xpl\Traits\PropertyAccessReadable;

/**
 * Represents a pair of Decimals.
 * 
 * @property-read \Decimal\Decimal $x
 * @property-read \Decimal\Decimal $y
 */
class DecimalPair
{

	use PropertyAccessReadable;

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
