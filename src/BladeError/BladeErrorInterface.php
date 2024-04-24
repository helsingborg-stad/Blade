<?php

namespace HelsingborgStad\BladeError;

use Throwable;
use HelsingborgStad\BladeService\BladeService;

interface BladeErrorInterface
{
    public function __construct(BladeService $blade);
    public function setThrowable(Throwable $e);
    public function getThrowable(): Throwable;
    public function print(): void;
}
