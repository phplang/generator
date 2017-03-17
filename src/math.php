<?php declare(strict_types=1);

namespace PhpLang\Generator;

/**
 * Yield a series of integers from $start to $end in increments of $step
 *
 * @param int $start - Number to begin at
 * @param int $end - Number to end at (inclusive)
 * @param int $step - Increment to skip by
 *
 * @yield int
 */
function range(int $start, int $end, int $step = 1) {
  if ($step <= 0) {
    trigger_error("\$step must be a positive integer", \E_USER_NOTICE);
    $step *= -1;
  }

  $stop = 1;
  if ($start > $end) {
    $step *= -1;
    $stop = -1;
  }
  for ($i = $start; ($i <=> $end) !== $stop; $i += $step) {
    yield $i;
  }
}

