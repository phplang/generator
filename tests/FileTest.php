<?php

use PHPUnit\Framework\TestCase;
use PhpLang\Generator as G;

class FileTest extends TestCase {
  public function testStreamGetContents() {
    $data = fopen('data:text/plain,abcdef', 'rb');
    $blocks = 0;
    foreach (G\stream_get_contents($data, 3) as $block) {
      ++$blocks;
      $this->assertEquals(3, strlen($block));
    }
    $this->assertEquals(2, $blocks);
  }

  public function testFile() {
    $modes = [
      0,
      G\FILE_IGNORE_NEW_LINES,
      G\FILE_SKIP_EMPTY_LINES,
      G\FILE_IGNORE_NEW_LINES | G\FILE_SKIP_EMPTY_LINES,
    ];
    foreach ($modes as $mode) {
      $php =                    \file(__FILE__, $mode);
      $gen = iterator_to_array(G\file(__FILE__, $mode));
      $this->assertEquals($php, $gen);
    }
  }

  public function testScanDir() {
    $php =  \scandir(__DIR__);
    $gen = iterator_to_array(G\scandir(__DIR__));
    sort($php);
    sort($gen);
    $this->assertEquals($php, $gen);
  }
}
