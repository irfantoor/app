<?php

namespace IrfanTOOR\App;

use IrfanTOOR\App\Exception;
use IrfanTOOR\Collection;
use IrfanTOOR\App\View;

class Controller
{
    protected $app;
    protected $middlewares = [];

    // protected $data;
    // protected $session;

    public function __construct($app)
    {
        $this->app = $app;
        // $this->data = new Collection($app->config('data'), []);
        // $this->session = new Session($app->ServerRequest());

        // $this->data->set('session', $this->session->toArray());

        // $this->set('app', $app);
        // $this->set('logged', $this->isLogged());
    }

    /**
     * Calling a non-existant method on Engine checks to see if there's an item
     * in the container returns it or returns a class of the same name.
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

    // public function set($id, $value = null)
    // {
    //     $this->data->set($id, $value);
    // }

    // public function get($id, $default = null)
    // {
    //     return $this->data->get($id, $default);
    // }

    // public function toArray()
    // {
    //     return $this->data->toArray();
    // }

    // function getApp()
    // {
    //     return $this->app;
    // }

    // function getSession()
    // {
    //     return $this->session;
    // }

    // function isLogged()
    // {
    //     return $this->getSession()->get('logged', false);
    // }

    public function addMiddleware($middleware)
    {
        # avoids multiple additions
        if (!isset($this->middlewares[$middleware])) {
            $mw = '\\' . $middleware;
            try {
                $this->middlewares[$middleware] = new $mw($this);
            } catch(Exception $e) {
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
