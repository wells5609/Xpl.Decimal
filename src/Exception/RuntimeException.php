<?php

declare(strict_types=1);

namespace Xpl\Decimal\Exception;

/**
 * Exception thrown when an error occurs that can only be detected at runtime.
 */
class RuntimeException extends \RuntimeException implements Exception
{

}