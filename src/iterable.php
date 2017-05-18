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

/* BC for Initial Commit */
const TRAVERSABLE_FILTER_USE_VALUE = ITERABLE_FILTER_USE_VALUE;
const TRAVERSABLE_FILTER_USE_KEY   = ITERABLE_FILTER_USE_KEY;
const TRAVERSABLE_FILTER_USE_BOTH  = ITERABLE_FILTER_USE_BOTH;
function traversable_map   (...$args) { yield from iterable_map   (...$args); }
function traversable_filter(...$args) { yield from iterable_filter(...$args); }
