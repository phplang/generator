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

  public function testStreamGetBlocks() {
    $data = fopen('data:text/plain,abcdefghijklmno', 'rb');
    $expect = [ 'ab', 'cd', 'ef', 'gh', 'ij', 'kl', 'mn', 'o' ];
    foreach (G\stream_get_blocks($data, 2) as $block) {
        $this->assertSame($block, array_shift($expect));
    }
    $this->assertEmpty($expect);
  }

  public function testStreamGetChars() {
    $data = fopen('data:text/plain,abcdefg', 'rb');
    $expect = [ 'a', 'b', 'c', 'd', 'e', 'f', 'g' ];
    foreach (G\stream_get_chars($data) as $char) {
        $this->assertSame($char, array_shift($expect));
    }
    $this->assertEmpty($expect);
  }
}
