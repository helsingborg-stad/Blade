<?php

namespace HelsingborgStad\Services\BladeService;

interface BladeService
{
    public function render($template, $data = []);
}