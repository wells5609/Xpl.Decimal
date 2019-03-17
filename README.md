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

- `min(): Decimal`
- `max(): Decimal`
- `sum(): Decimal`
- `mean(): Decimal`
- `prod(): Decimal`
- `delta(): Vector` Returns a vector of the difference between each value (i.e. `$this[i] - $this[i-1]`).
- `rdelta(): Vector` Returns a vector of the difference between each value as a percent (i.e. `($this[i] - $this[i-1]) / $this[i-1]`)
- `diff(Vector $y): Vector` Returns a vector of the difference between each value in `$this` and `$y` (i.e. `$this[i] - $y[i]`)
- `centralMoment(int $k, bool $sample = true): Decimal` Central moment of the power `$k`
- `skewness(): Decimal`
- `regressionSumOfSquares($ybar): Decimal`
- `residualSumOfSquares(Vector $y): Decimal`
- `totalSumOfSquares(Vector $y): Decimal`
- `var(): Decimal` Variance
- `covar(): Decimal` Covariance
- `stdev(): Decimal` Standard deviation
- `coefficientOfVariation(): Decimal` Coefficient of variation (aka relative standard deviation)
- `indexOfDispersion(): Decimal` Index of dispersion (aka relative variance)
- `correl(Vector $y): Decimal` Pearson's correlation coefficient (aka Pearson-product-moment correlation coefficient, "Pearson's r")
- `varp(): Decimal` Variance, treating the sequence as a population
- `stdevp(): Decimal` Standard deviation, treating the sequence as a population