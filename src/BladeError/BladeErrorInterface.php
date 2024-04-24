<?php

namespace HelsingborgStad\BladeError;

use Throwable;

interface BladeErrorInterface
{
    public function __construct(Throwable $e);
    public function print(): void;
}
