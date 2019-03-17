# Xpl.Decimal

Library built atop the `ds` and `decimal` PHP extensions.

__Namespace__: `Xpl\Decimal`

__Dependencies__:

  - wells5609\datetime
  - `ds` extension
  - `decimal` extension
  - php >= 7.3

___

## __`Vector`__ (_class_)

A strict implementation of `Ds\Sequence` that restricts values to `Decimal\Decimal` instances.

Has additional methods for doing common math operations - most are self-explanatory:

__`min(): Decimal`__

__`max(): Decimal`__

__`sum(): Decimal`__

__`mean(): Decimal`__

__`prod(): Decimal`__

__`delta(): Vector`__ 

Returns a vector containing the difference between each value (i.e. `$this[i] - $this[i-1]`).

__`rdelta(): Vector`__

Returns a vector containing the difference between each value as a percent (i.e. `($this[i] - $this[i-1]) / $this[i-1]`)

__`diff(Vector $y): Vector`__

Returns a vector containing the difference between each value in `$this` and `$y` (i.e. `$this[i] - $y[i]`)

__`var(): Decimal`__ 

Variance

__`covar(): Decimal`__ 

Covariance

__`stdev(): Decimal`__

Standard deviation

__`coefficientOfVariation(): Decimal`__

Coefficient of variation (aka relative standard deviation)

__`indexOfDispersion(): Decimal`__

Index of dispersion (aka relative variance)

__`correl(Vector $y): Decimal`__

Pearson's correlation coefficient (aka Pearson-product-moment correlation coefficient, "Pearson's r")

__`varp(): Decimal`__ 

Variance, treating the data as a population

__`stdevp(): Decimal`__

Standard deviation, treating the data as a population

__`centralMoment(int $k, bool $sample = true): Decimal`__

Central moment of the power `$k`

__`skewness(): Decimal`__

__`regressionSumOfSquares($ybar): Decimal`__

__`residualSumOfSquares(Vector $y): Decimal`__

__`totalSumOfSquares(Vector $y): Decimal`__
