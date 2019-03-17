<?php

declare(strict_types=1);

namespace Xpl\Decimal\Exception;

/**
 * Exception thrown when a serious error occurs that should not be caught.
 */
class ErrorException extends \ErrorException implements Exception
{

}