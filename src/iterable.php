<?php declare(strict_types=1);

namespace PhpLang\Generator;

/* Return true if all elements of $in return true for $predicate.
 *
 * @param Iterable $in - Iterable to be examined.
 * @param Callable $predicate - Function to call with each element.
 * @return bool - TRUE if all elements of $in satisfy $predicate, FALSE otherwise.
 */
function iterable_all(Iterable $in, Callable $predicate): bool {
  foreach ($in as $k => $v) {
    if (!$predicate($v, $k)) {
      return false;
    }
  }
  return true;
}

/* Return true if any element of $in returns true for $predicate.
 *
 * @param Iterable $in - Iterable to be examined.
 * @param Callable $predicate - Function to call with each element.
 * @return bool - TRUE if any element of $in satisfies $predicate, FALSE otherwise.
 */
function iterable_any(Iterable $in, Callable $predicate): bool {
  foreach ($in as $k => $v) {
    if ($predicate($v, $k)) {
      return true;
    }
  }
  return false;
}

/* Return true if no element of $in returns true for $predicate.
 *
 * @param Iterable $in - Iterable to be examined.
 * @param Callable $predicate - Function to call with each element.
 * @return bool - TRUE if no element of $in satisfies $predicate, FALSE otherwise.
 */
function iterable_none(Iterable $in, Callable $predicate): bool {
  foreach ($in as $k => $v) {
    if ($predicate($v, $k)) {
      return false;
    }
  }
  return true;
}

/* Iterable friendly version of array_map()
 *
 * @param Iterable ...$in - One or more iterables to map across
 * @param Callable $cb - Function to call each iterable element on
 *                       (function(mixed $val): mixed)
 *
 * @yield Elements of input iterables mapped through $cb
 */
function iterable_map(Iterable $in, ...$args) {
  if (count($args) < 1) {
    trigger_error("Missing callback arg", \E_USER_WARNING);
    yield from $in;
    return;
  }
  $cb = array_pop($args);
  if (!is_callable($cb)) {
    trigger_error("Final arg is not callable", \E_USER_WARNING);
    yield from $in;
    return;
  }
  foreach ($in as $k => $v) {
    yield $k => $cb($v);
  }
  foreach ($args as $i => $arg) {
    if (!is_iterable($arg)) {
      trigger_error(($i + 2)."th arg is not Iterable", \E_USER_WARNING);
      continue;
    }
    foreach ($arg as $k => $v) {
      yield $k => $cb($v);
    }
  }
}

/* Convenience wrapper for iterable_map() to call a given method on each element
 *
 * @param Iterable ...$in - One or more iterables to map across
 * @param string $method - Name of zero-args method to call on each element
 *
 * @yield Elements of input iterables mapped through $elem->$method()
 */
function iterable_map_method(...$args) {
  $method = array_pop($args);
  array_push($args, function($elem) use ($method) { return $elem->$method(); });
  yield from iterable_map(...$args);
}

const ITERABLE_FILTER_USE_VALUE = 0;
const ITERABLE_FILTER_USE_KEY   = \ARRAY_FILTER_USE_KEY;
const ITERABLE_FILTER_USE_BOTH  = \ARRAY_FILTER_USE_BOTH;
/* Iterable friendly version of array_filter()
 *
 * @param Callable $cb - Callback to invoke on each element
 * @param Iterable $in - Iterable to filter
 * @param int $type - Bitmask of elements to pass to callback
 *                    See ITERABLE_FILTER_USE_* constants int his namespace
 *
 * @yield - Filtered iterable elements
 */
function iterable_filter(Callable $cb, Iterable $in, int $type = ITERABLE_FILTER_USE_VALUE) {
  if ($type == ITERABLE_FILTER_USE_KEY) {
    foreach ($in as $k => $v) {
      if ($cb($k)) {
        yield $k => $v;
      }
    }
  } elseif ($type == ITERABLE_FILTER_USE_BOTH) {
    foreach ($in as $k => $v) {
      if ($cb($k, $v)) {
        yield $k => $v;
      }
    }
  } else {
    foreach ($in as $k => $v) {
      if ($cb($v)) {
        yield $k => $v;
      }
    }
  }
}

/* Convenience wrapper for iterable_filter() to use a given method to filter each element
 *
 * @param string $method - Name of zero-args method to call on each element
 * @param Iterable $in - Ierables to filter
 *
 * @yield Elements of input iterable filtered by $elem->$method()
 */
function iterable_filter_method(string $method, Iterable $in) {
  yield from iterable_filter(
    function($elem) use ($method) {
      return $elem->$method();
    },
    $in,
    ITERABLE_FILTER_USE_VALUE
  );
}

/* Multisort/collator for multiple PRESORTED iterables.
 *
 * Note that this method assumes the input iterables are already correctly
 * sorted such that the first element in a given iterable should always come
 * before later elements in the same iterable.
 *
 * How those individual iterables should be sorted is dependent on where the
 * data is sourced from, but a (dumb/inefficient) general purpose algorithm
 * might look something like:
 *
 * $sorted = (function(Iterable $unsorted, Callable $cmp) {
 *   $tmp = \iterable_to_array($unsorted);
 *   usort($tmp, $cmp);
 *   yield from $tmp;
 * })($unsorted, $cmp);
 *
 * In practice, any API providing data should have its own native sorting option,
 * and that should be used/preferred.
 *
 * @param Callable $cmp - Comparator function, takes two ares and returns -1, 0, 1
 *                        Similar to version_compare(), strcmp(), <=>, etc...
 * @param Iterable ...$in - One or more iterables to multisort/collate
 *
 * @yield - Sorted iterable elements
 */
function iterable_multisort(Callable $cmp, Iterable $first, Iterable ...$args) {
  array_unshift($args, $first);
  $args = array_values($args);

  // Normalize arrays into traversables
  $args = array_map(function ($arg) {
    return is_array($arg) ? (function(array $arg) { yield from $arg; })($arg) : $arg;
  }, $args);

  // Initial full sort of heads
  usort($args, function ($a, $b) use ($cmp) {
    return $cmp($a->current(), $b->current());
  });

  while ($args) {
    // Assume first iterable is in lowest
    // due to initial sort and/or update sort
    yield $args[0]->current();
    $args[0]->next();

    if (!$args[0]->valid()) {
      // Remove empty column
      array_shift($args);
      $args = array_values($args);
    }

    // Update sort, shift iterable down stack till it's in order
    for ($i = 0; $i < (count($args) - 1); ++$i) {
      if ($cmp($args[$i]->current(), $args[$i + 1]->current()) <= 0) {
        break;
      }
      $tmp = $args[$i+1];
      $args[$i+1] = $args[$i];
      $args[$i] = $tmp;
    }
  }
}

/* BC for Initial Commit */
const TRAVERSABLE_FILTER_USE_VALUE = ITERABLE_FILTER_USE_VALUE;
const TRAVERSABLE_FILTER_USE_KEY   = ITERABLE_FILTER_USE_KEY;
const TRAVERSABLE_FILTER_USE_BOTH  = ITERABLE_FILTER_USE_BOTH;
function traversable_map   (...$args) { yield from iterable_map   (...$args); }
function traversable_filter(...$args) { yield from iterable_filter(...$args); }
