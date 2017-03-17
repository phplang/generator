<?php declare(strict_types=1);

namespace PhpLang\Generator;

/**
 * Read from a stream resource in blocks/packets
 *
 * @param resource<file> $stream - An already opened stream, or a URI
 * @param int $blocksize - Maximum number of bytes to read per iteration
 *
 * @yield string Data chunks from $stram
 */
function stream_get_contents($stream, int $blocksize = 8192) {
  if (!is_resource($stream)) {
    trigger_error("Expected open stream", E_USER_WARNING);
    return;
  }

  while (($data = fread($stream, $blocksize)) != '') {
    yield $data;
  }
}

/**
 * Read a file line by line
 *
 * @param string|resource<file> $stream - An already opened stream, or a URI
 * @param int $flags - bitmask of builtin FILE_* constants
 *
 * @yield string Lines from $stream
 */
const FILE_USE_INCLUDE_PATH = \FILE_USE_INCLUDE_PATH;
const FILE_IGNORE_NEW_LINES = \FILE_IGNORE_NEW_LINES;
const FILE_SKIP_EMPTY_LINES = \FILE_SKIP_EMPTY_LINES;
function file($stream, int $flags = 0) {
  if (is_string($stream)) {
    $stream = fopen($stream, 'rt', ($flags & FILE_USE_INCLUDE_PATH) != 0);
  }
  if (!is_resource($stream)) {
    trigger_error("Expected open stream or filename", \E_USER_WARNING);
    return;
  }

  while (($line = fgets($stream)) !== false) {
    if ($flags & FILE_IGNORE_NEW_LINES) {
      $line = rtrim($line);
    }
    if (($flags & FILE_SKIP_EMPTY_LINES) && (strlen($line) === 0)) {
      continue;
    }
    yield $line;
  }
}

/**
 * Iterate through a directory, yielding directory names
 *
 * @param string $dir - Directory name
 *
 * @yield string - Directory entry
 */
function scandir(string $dir) {
  $dir = opendir($dir);
  if (!$dir) return;
  while (($ent = readdir($dir)) !== false) {
    yield $ent;
  }
  closedir($dir);
}
