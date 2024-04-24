<?php

namespace HelsingborgStad\BladeError;

use Throwable;
use Exception;
use HelsingborgStad\BladeService\BladeService as BladeErrorRenderer;

/**
 * Class BladeServiceInstance
 *
 * This class provides functionality for managing Blade views.
 */
class BladeError implements BladeErrorInterface
{
  public function __construct(private Throwable $e, private array $viewPaths = [], private array $viewData = []){}

  public function print(): void 
  {
    $errorTemplateViewPath = __DIR__ . "/../../views/";
    $renderer = new BladeErrorRenderer([$errorTemplateViewPath]);
    echo $renderer->makeView('error', [
      'message' => $this->getMessage(),
      'line' => $this->getLine(),
      'source' => $this->getSource(),
      'code' => $this->getCodeSnippet(),
      'stacktrace' => $this->getStackTrace(),
      'viewPaths' => implode(PHP_EOL, $this->viewPaths ?? []),
      'viewData' => $this->viewData ?? []
    ])->render();
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

    if($this->e->getFile()) {
      return file($this->e->getFile());
    }
    throw new Exception('No file found in error object.');
  }

  private function getFileContentsAsArray(): array
  {
    if($this->e->getFile()) {
      return file($this->e->getFile());
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
    if($this->e->getFile()) {
      return $this->e->getFile();
    }
    throw new Exception('No file found in error object.');
  }

  private function getLine(): int
  {
    if($this->e->getLine()) {
      return $this->e->getLine();
    }
    throw new Exception('No line found in error object.');
  }

  private function getMessage(): string
  {
    if($this->e->getMessage()) {
      return $this->e->getMessage();
    }
    throw new Exception('No message found in error object.');
  }

  private function getStackTrace(): string
  {
    if($this->e->getTraceAsString()) {
      return $this->e->getTraceAsString();
    }
    throw new Exception('No stack trace found in error object.');
  }
}
