<?php

namespace IrfanTOOR\App;

class Middleware
{
	protected $controller;

    function __construct($controller)
    {
        $this->controller = $controller;
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
        if (is_callable([$this->controller, $method])) {
            return call_user_func_array([$this->controller, $method], $args);
        } else {
            throw new Exception("Method : $method, is not defined");
        }
    }

    function preProcess($request, $response, $args)
    {
        # default filters/actions if any

        return $response;
    }

    function postProcess($request, $response, $args)
    {
        # default filters/actions if any

    	return $response;
    }
}
