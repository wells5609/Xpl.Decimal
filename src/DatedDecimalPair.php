<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use DateTimeInterface;
use Decimal\Decimal;
use Xpl\DateTime\DatedTrait;

/**
 * Represents a pair of Decimals associated with a specific date/time.
 * 
 * @property-read \Decimal\Decimal $x
 * @property-read \Decimal\Decimal $y
 * @property-read \DateTimeImmutable $datetime
 */
class DatedDecimalPair extends DecimalPair
{

	use DatedTrait;

	/**
	 * Constructor.
	 *
	 * @param Decimal $x
	 * @param Decimal $y
	 * @param DateTimeInterface $datetime
	 */
	public function __construct(Decimal $x, Decimal $y, DateTimeInterface $datetime)
	{
		parent::__construct($x, $y);

		$this->setDateTime($datetime);
	}

}
