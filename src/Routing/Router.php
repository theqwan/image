<?php

namespace Qwantum\Image\Routing;

class Router
{
    /**
     * @var \Illuminate\Routing\RouteRegistrar
     */
    private $registrar;

    /**
     * @var string
     */
    protected $namespace = "\\Qwantum\\Image\\App\\Http\\Controllers";

    public function __construct()
    {
        $this->registrar = app('router');
    }

    public function route()
    {
        $this->registrar->post('images/{folder}/upload', "{$this->namespace}\ImageController@upload")->name('images.upload');
    }
}
