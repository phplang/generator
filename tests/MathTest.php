<?php

require __DIR__ . '/../vendor/autoload.php';
use PhpLang\Generator as G;

class MathTest extends PHPUnit_Framework_TestCase {
  public function testRange() {
    $tests = [
      [ 1, 10, 1 ],
      [ 10, 1, 1 ],
      [ 1, 10, 2 ],
      [ 10, 1, 2 ],
    ];
    foreach ($tests as $test) {
      $this->assertEquals(
        \range($test[0], $test[1], $test[2]),
        iterator_to_array(
          G\range($test[0], $test[1], $test[2])
        )
      );
    }
    $this->assertEquals(
      [1,2,3,4,5],
      iterator_to_array(
        G\range(1, 5)
      )
    );
    $this->assertEquals(
      [1,3,5,7,9],
      iterator_to_array(
        G\range(1, 10, 2)
      )
    );
    $this->assertEquals(
      [5,4,3,2,1],
      iterator_to_array(
        G\range(5, 1)
      )
    );
  }
}
