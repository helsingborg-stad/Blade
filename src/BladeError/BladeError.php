<?php

namespace HelsingborgStad\BladeError;

use Throwable;
use Exception;
use HelsingborgStad\BladeService\BladeService;

/**
 * Class BladeServiceInstance
 *
 * This class provides functionality for managing Blade views.
 */
class BladeError implements BladeErrorInterface
{
  private Throwable $thrownError;

  public function __construct(private BladeService $blade){}

  public function print(): void 
  {
    echo $this->blade->makeView(
      'error',
      [
        'message' => $this->getMessage(),
        'line' => $this->getLine(),
        'source' => $this->getSource(),
        'code' => $this->getCodeSnippet(),
        'stacktrace' => $this->getStackTrace(),
        'viewPaths' => implode(PHP_EOL, $this->blade->getViewPaths()),
        'cachePath' => $this->blade->getCachePath()
      ],
      [],
      __DIR__ . "/../../views/"
    )->render();
  }

  public function setThrowable(Throwable $e): void
  {
    $this->thrownError = $e;
  }

  public function getThrowable(): Throwable
  {
    return $this->thrownError;
  }

  private function getCodeSnippet(): array
  {
    $fileContents = $this->getFileContentsAsArray(); 

    if(!empty($fileContents)) {
      $lineNumber = $this->normalizeLineNumber($this->getLine());
      $codeSnippet = [
        'before' => trim($fileContents[$lineNumber - 1]),
        'current' => trim($fileContents[$lineNumber]),
        'after' => trim($fileContents[$lineNumber + 1])
      ];
      return $codeSnippet;
    }

    if($this->getThrowable()->getFile()) {
      return file($this->getThrowable()->getFile());
    }
    throw new Exception('No file found in error object.');
  }

  private function getFileContentsAsArray(): array
  {
    if($this->getThrowable()->getFile()) {
      return file($this->getThrowable()->getFile());
    }
    throw new Exception('No file found in error object.');
  }

  private function normalizeLineNumber($lineNumber): int
  {
    if($lineNumber >= 1) {
      return $lineNumber - 1;
    }
    throw new Exception('Line number must be greater or equal than 1.');
  }

  private function getSource(): string
  {
    if($this->getThrowable()->getFile()) {
      return $this->getThrowable()->getFile();
    }
    throw new Exception('No file found in error object.');
  }

  private function getLine(): int
  {
    if($this->getThrowable()->getLine()) {
      return $this->getThrowable()->getLine();
    }
    throw new Exception('No line found in error object.');
  }

  private function getMessage(): string
  {
    if($this->getThrowable()->getMessage()) {
      return $this->getThrowable()->getMessage();
    }
    throw new Exception('No message found in error object.');
  }

  private function getStackTrace(): string
  {
    if($this->getThrowable()->getTraceAsString()) {
      return $this->getThrowable()->getTraceAsString();
    }
    throw new Exception('No stack trace found in error object.');
  }
}
