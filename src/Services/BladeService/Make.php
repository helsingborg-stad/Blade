<?php

namespace HelsingborgStad\Services\BladeService;

use Illuminate\Contracts\View\View;

interface Make
{
    /**
     * Render a view with the given data.
     *
     * @param string $view The name of the view to render.
     * @param array $data The data to pass to the view.
     * @param array $mergeData The data to merge with the view data.
     * @return View The rendered view.
     */
    public function make(string $view, array $data = [], array $mergeData = []): View;
}
