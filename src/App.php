<?php

namespace IrfanTOOR;

use Exception;

use IrfanTOOR\App\Events;
use IrfanTOOR\App\Exception as AppException;
use IrfanTOOR\Engine\Exception as EngineException;
use IrfanTOOR\App\Router;
use IrfanTOOR\Engine;
use IrfanTOOR\Engine\Http\Response;

use IrfanTOOR\Debug;

class App extends Engine
{
    protected $events;
    protected $router;

    function __construct($config = [])
    {
        $this->events = new Events;
        $this->router = new Router;

        parent::__construct($config);

    }

    function __call($method, $args)
    {
        try {
            $result = parent::__call($method, $args);
            return $result;
        } catch(EngineException $e) {
           throw new AppException("Method: $method, not defined", 1);
        }
    }

    function addRoute($method, $path, $handler)
    {
        $this->router->addRoute($method, $path, $handler);
    }

    function register($event_id, $callback, $level = 10)
    {
        $this->events->register($event_id, $callback, $level = 10);
    }

    function trigger($event_id)
    {
        $this->events->trigger($event_id);
    }

    function redirectTo($url, $status = 307)
    {
        $response = new Response(['status' => $status]);
        $response
            ->withHeader('Location', $url)
            ->write(sprintf('<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta http-equiv="refresh" content="0;url=%1$s" />
<title>Redirecting to %1$s</title>
</head>
<body>
Redirecting to <a href="%1$s">%1$s</a>.
</body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')))
            ->send();

        exit;
    }

    function process($request, $response, $args)
    {
        $path = $request->getUri()->getPath();

        // extract processed route's $type and $handler
        extract(
            $this->router->process($request->getMethod(), $path)
        );

        switch ($type) {
            case 'callable':
                ob_start();
                $result = $handler($request, $response, $args);
                $contents = ob_get_clean();
                if (is_object($result)) {
                    return $result;
                } else {
                    if (is_string($result)) {
                        $contents .= $result;
                    } else {
                        $contents .= print_r($result, 1);
                    }
                    $response->write($contents);
                    return $response;
                }

                break;

            case 'string':
                if (($pos = strpos($handler, '@')) !== FALSE) {
                    # e.g. process@App\Controller\Main
                    $method = substr($handler, 0, $pos);
                    $cname  = substr($handler, $pos + 1);
                } else {
                    # e.g. App\Controller\Blog
                    $method = 'defaultMethod';
                    $cname  = $handler;
                }
                break;

            default:
                return $response
                            ->withStatus(404)
                            ->write('no route defined!');
        }

        $controller = new $cname($this);

        if (!method_exists($controller, $method))
            $method  = 'defaultMethod';

        $middlewares = $controller->getMiddlewares();

        foreach ($middlewares as $k => $mw) {
            $response = $mw->preProcess($request, $response, $args);
        }

        # controller process
        $response = $controller->$method($request, $response, $args);

        # post process of middlewares
        foreach ($middlewares as $k=>$mw) {
            $response = $mw->postProcess($request, $response, $args);
        }

        return $response;
    }
}
