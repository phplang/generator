<?php declare(strict_types=1);

namespace PhpLang\Generator;

function traversable_map(\Traversable $in, ...$args) {
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
    if (!($arg instanceof \Traversable)) {
      trigger_error(($i + 2)."th arg is not Traversable", \E_USER_WARNING);
      continue;
    }
    foreach ($arg as $k => $v) {
      yield $k => $cb($v);
    }
  }
}

const TRAVERSABLE_FILTER_USE_VALUE = 0;
const TRAVERSABLE_FILTER_USE_KEY   = \ARRAY_FILTER_USE_KEY;
const TRAVERSABLE_FILTER_USE_BOTH  = \ARRAY_FILTER_USE_BOTH;
function traversable_filter(Callable $cb, \Traversable $in, int $type = TRAVERSABLE_FILTER_USE_VALUE) {
  if ($type == TRAVERSABLE_FILTER_USE_KEY) {
    foreach ($in as $k => $v) {
      if ($cb($k)) {
        yield $k => $v;
      }
    }
  } elseif ($type == TRAVERSABLE_FILTER_USE_BOTH) {
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
