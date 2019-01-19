<?php

namespace IrfanTOOR\App;

use Exception;
use IrfanTOOR\Collection;
use IrfanTOOR\App\Session;
use IrfanTOOR\App\View;

class Controller
{
    protected $app;
    protected $middlewares = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Calling a non-existant method on Controller checks to see if there's an item
     * in the App returns it.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (is_callable([$this->app, $method])) {
            $result = call_user_func_array([$this->app, $method], $args);
            return $result;
        } else {
            throw new Exception("Unknown Controller Method : $method");
        }
    }

    public function getApp()
    {
        return $this->app;
    }

    public function addMiddleware($middleware)
    {
        # avoids multiple additions
        if (!isset($this->middlewares[$middleware])) {
            $mw = '\\' . $middleware;
            try {
                $this->middlewares[$middleware] = new $mw($this);
            } catch(Exception $e) {
                throw new Exception("App::middleware -- " . $e->getMessage(), 1);
            }
        }
    }

    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    public function defaultMethod($request, $response, $args)
    {
        return $response;
    }

    public function show($response, $view, $data = [])
    {
        $v = new View();
        $contents = $v->renderToString($view, $data);
        return $response->write($contents);
    }
}
