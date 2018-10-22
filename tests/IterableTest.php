<?php

use PHPUnit\Framework\TestCase;
use PhpLang\Generator as G;

class IterableTest extends TestCase {
  public function testAll() {
    $isOdd = function($v) { return $v & 1; };
    $lt10 = function($v) { return $v < 10; };
    $this->assertTrue(G\iterable_all([1,3,5], $isOdd));
    $this->assertFalse(G\iterable_all([1,3,6], $isOdd));
    $this->assertTrue(G\iterable_all(range(1,9), $lt10));
    $this->assertFalse(G\iterable_all(range(1,10), $lt10));
  }

  public function testAny() {
    $isOdd = function($v) { return $v & 1; };
    $lt10 = function($v) { return $v < 10; };
    $this->assertTrue(G\iterable_any([1,3,5], $isOdd));
    $this->assertTrue(G\iterable_any([1,3,6], $isOdd));
    $this->assertFalse(G\iterable_any([2,4,6], $isOdd));
    $this->assertTrue(G\iterable_any(range(1,9), $lt10));
    $this->assertTrue(G\iterable_any(range(1,10), $lt10));
    $this->assertFalse(G\iterable_any(range(10,20), $lt10));
  }

  public function testNone() {
    $isOdd = function($v) { return $v & 1; };
    $lt10 = function($v) { return $v < 10; };
    $this->assertFalse(G\iterable_none([1,3,5], $isOdd));
    $this->assertFalse(G\iterable_none([1,3,6], $isOdd));
    $this->assertTrue(G\iterable_none([2,4,6], $isOdd));
    $this->assertFalse(G\iterable_none(range(1,9), $lt10));
    $this->assertFalse(G\iterable_none(range(1,10), $lt10));
    $this->assertTrue(G\iterable_none(range(10,20), $lt10));
  }

  public function testFilter() {
    $this->assertEquals(
      [0=>1,2=>3,4=>5,6=>7,8=>9],
      iterator_to_array(
        G\iterable_filter(
          function ($v) { return ($v % 2) === 1; },
          G\range(1, 10)
        )
      )
    );

    $this->assertEquals(
      [2,4,6,8,10],
      iterator_to_array(
        G\iterable_filter(
          function ($v) { return ($v % 2) === 0; },
          G\range(1, 10),
          G\ITERABLE_FILTER_USE_VALUE
        ),
        false
      )
    );

    $this->assertEquals(
      [1,3,5,7,9],
      iterator_to_array(
        G\iterable_filter(
          function ($k) { return ($k % 2) === 0; },
          G\range(1, 10),
          G\ITERABLE_FILTER_USE_KEY
        ),
        false
      )
    );

    $this->assertEquals(
      [1,3,5,7,9],
      iterator_to_array(
        G\iterable_filter(
          function ($k, $v) { return ($v % 2) === 1; },
          G\range(1, 10),
          G\ITERABLE_FILTER_USE_BOTH
        ),
        false
      )
    );
  }

  public function testFilterMethod() {
    $data = [
      new class { function keep() { return true;  } function val() { return 'foo'; } },
      new class { function keep() { return false; } function val() { return 'bar'; } },
    ];
    $this->assertEquals(
      [ 'foo' ],
      iterator_to_array(
        G\iterable_map_method(
          G\iterable_filter_method(
            'keep', $data,
            G\ITERABLE_FILTER_USE_VALUE
          ),
          'val'
        ),
        false
      )
    );
  }

  public function testMap() {
    $this->assertEquals(
      [2, 4, 6, 202, 204, 206],
      iterator_to_array(
        G\iterable_map(
          G\range(  1,   3),
          G\range(101, 103),
          function ($v) { return $v * 2; }
        ),
        false
      )
    );
  }

  public function testMapMethod() {
    $data = [
      new class { function val() { return  2; } },
      new class { function val() { return 42; } },
    ];
    $this->assertEquals(
      [ 2, 42 ],
      iterator_to_array(
        G\iterable_map_method($data, 'val'),
        false
      )
    );
  }

  public function testMultiSort() {
    $iterables = [
      [ 10, 20, 30, 40, 50, 60, 70, 80, 90, 100 ],
      (function() { yield from [ 15, 35, 55, 75, 95 ]; })(),
      G\range(47, 53),
    ];
    $this->assertEquals(
      [ 10, 15, 20, 30, 35, 40, 47, 48, 49, 50, 50,
        51, 52, 53, 55, 60, 70, 75, 80, 90, 95, 100 ],
      iterator_to_array(
        G\iterable_multisort(
          function($a, $b) { return $a <=> $b; },
          ...$iterables
        ),
        false
      )
    );
  }
}
