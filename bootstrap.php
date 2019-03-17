<?php

declare(strict_types=1);

namespace Xpl\Decimal;

use Decimal\Decimal;

/** 
 * Pi to 98 decimal digits - far more than you'll ever need.
 * @var string
 */
const PI = '3.14159265358979323846264338327950288419716939937510582097494459230781640628620899862803482534211707';

/**
 * Default Decimal precision (28 significant digits)
 * @var int 
 */
const DEFAULT_PRECISION = Decimal::DEFAULT_PRECISION;

/** 
 * IEEE 754R Decimal32 precision (7 decimal digits)
 * @var int
 */
const IEEE_DECIMAL32 = 7;

/** 
 * IEEE 754R Decimal64 precision (16 decimal digits)
 * @var int 
 */
const IEEE_DECIMAL64 = 16;

/** 
 * IEEE 754R Decimal128 precision (34 decimal digits)
 * @var int 
 */
const IEEE_DECIMAL128 = 34;

/** 
 * Load helper functions 
 */
require __DIR__.'/functions.php';
