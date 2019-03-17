<?php

declare(strict_types=1);

namespace Xpl\Decimal\Exception;

/**
 * Exception thrown when a non-numeric value is provided to a function that 
 * expects a valid number or parsable number representation.
 */
class InvalidNumberException extends InvalidArgumentException 
{
	
}
