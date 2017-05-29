<?php declare(strict_types=1);

namespace PhpLang\Generator;

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

function iterable_map_method(...$args) {
  $method = array_pop($args);
  array_push($args, function($elem) use ($method) { return $elem->$method(); });
  yield from iterable_map(...$args);
}

const ITERABLE_FILTER_USE_VALUE = 0;
const ITERABLE_FILTER_USE_KEY   = \ARRAY_FILTER_USE_KEY;
const ITERABLE_FILTER_USE_BOTH  = \ARRAY_FILTER_USE_BOTH;
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

function iterable_filter_method(string $method, Iterable $in) {
  yield from iterable_filter(
    function($elem) use ($method) {
      return $elem->$method();
    },
    $in,
    ITERABLE_FILTER_USE_VALUE
  );
}

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
