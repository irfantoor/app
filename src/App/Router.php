<?php

namespace IrfanTOOR\App;

use InvalidArgumentException;
use Exception;
use IrfanTOOR\Collection;
use IrfanTOOR\Engine\Http\Request;

/**
 * Router
 */
class Router extends Collection
{
    protected $methods = [];

    public function __construct($init = [])
    {
        $defaults = [
            'methods' => [
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE',
                'PURGE',
                'OPTIONS',
                'TRACE',
                'CONNECT',
            ],
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($init[$k])) {
                $init[$k] = $v;
            }
        }

        $this->setAllowedMethods($init['methods']);
    }


    private function isValidMethodName($method)
    {
        if (!is_string($method))
            return false;

        if  ($method !== strtoupper($method))
            return false;

        return true;
    }

    private function isAllowedMethod($method)
    {
        return in_array($method, $this->methods);
    }


    /**
     * Sets a single allowed method
     *
     * @param string allowed method
     */
    public function setAllowedMethod($method)
    {
        if (!$this->isValidMethodName($method))
            return;

        if (!$this->has($method)) {
            $this->methods[] = $method;
            $this->set($method, []);
        }
    }

    /**
     * Sets the allowed methods
     *
     * @param array Array of methods
     */
    public function setAllowedMethods($methods)
    {
        foreach ($methods as $method) {
            $this->setAllowedMethod($method);
        }
    }

    public function getAllowedMethods()
    {
        return $this->methods;
    }

    public function addRoute($methods, $patern, $handler)
    {
        if (!is_array($methods))
            $methods = [$methods];

        foreach ($methods as $method) {
            # if method is ANY, add route to all allowed methods
            if ('ANY' == strtoupper($method)) {
                foreach($this->methods as $m) {
                    $this->addRoute($m, $patern, $handler);
                }
            } else {
                # todo -- find some sane way of calling
                # $method = Request::validate('method', $method);

                if (!$this->isAllowedMethod($method))
                    throw new Exception("$method, is not an allowed method", 1);

                if (!is_string($patern))
                    throw new Exception("patern must be a string", 1);                

                if (!(is_string($handler) || is_callable($handler)))
                    throw new Exception("handler can either be a closure or a string", 1);

                $def = $this->get($method);
                $def[] = [
                    'patern'  => $patern,
                    'handler' => $handler,
                ];

                $this->set($method, $def);
            }
        }
    }

    public function process($method, $path = '')
    {
        # todo -- use a sane way to call
        # $method = Request::validate('method', $method);

        $path = ltrim(rtrim($path, '/'), '/') ?: '/';
        $found = false;
        $routes = $this->get($method, []);

        foreach($routes as $route) {
            extract($route);
            preg_match('|(' . $patern . ')|', $path, $m);
            $matches_regex = (isset($m[1]) && $m[1] == $path) ? true : false;

            if (!$matches_regex)
                continue;

            ### If its a callback function
            if (is_callable($handler)) {
                # $response = $callable($this->request, $this->response);
                return ([
                    'type'    => 'callable',
                    'handler' => $handler
                ]);
            } else {
                # method@Controller
                return ([
                    'type'    => 'string',
                    'handler' => $handler
                ]);
            }
        }

        return ([
            'type'    => null,
            'handler' => null
        ]);
    }
}
