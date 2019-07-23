<?php

namespace Qwantum\Image\Routing;

use Illuminate\Contracts\Routing\Registrar as RegistrarContract;

class Router
{
    /**
     * @var RegistrarContract
     */
    private $registrar;

    /**
     * @var string
     */
    protected $namespace = "\\Qwantum\\Image\\App\\Http\\Controllers";

    public function __construct(RegistrarContract $registrar)
    {
        $this->registrar = $registrar;
    }

    public function route()
    {
        $this->registrar->post('images/{folder}/upload', "{$this->namespace}\ImageController@upload")->name('images.upload');
    }
}
