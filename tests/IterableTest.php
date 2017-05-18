<?php

require __DIR__ . '/../vendor/autoload.php';
use PhpLang\Generator as G;

class IterableTest extends PHPUnit_Framework_TestCase {
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
}
