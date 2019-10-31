<?php

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\App;
use IrfanTOOR\App\Events;
use IrfanTOOR\App\Router;
use IrfanTOOR\Debug;
use IrfanTOOR\Engine;


class App extends Engine
{
    const NAME        = "Irfan's App";
    const DESCRIPTION = "Small framework to make your web apps";
    const VERSION     = "0.3.1"; //@@VERSION

    protected $events;
    protected $router;
    protected $session;

    function __construct($config = [])
    {
        $defaults = [
            # default factories
            'Response' => 'IrfanTOOR\\App\\Response',
            'Session'  => 'IrfanTOOR\\App\\Session',
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($config['default']['classes'][$k])) {
                $config['default']['classes'][$k] = $v;
            }
        }

        parent::__construct($config);

        $session_class = $this->config('default.classes.Session');

        # Factory functions for Cookie and Uploaded file
        $this->container->set('Session', function() use ($session_class){
            $cname = $this->config('default.classes.Session');
            return new $cname([
                'path' => ROOT . 'storage/sessions/',
                'env'  => $this->getEnvironment()->toArray(),
            ]);
        });

        $this->events = new Events;
        $this->router = new Router;

        # start session, only if enabled
        if ($this->config('session.enable')) {
            $session = $this->getSession();
            $session->start();
        }
    }

    function getVersion()
    {
        return self::VERSION;
    }

    function addRoute($method, $path, $handler)
    {
        $this->router->addRoute($method, $path, $handler);
    }

    function registerEvent($event_id, $callback, $level = 10)
    {
        $this->events->register($event_id, $callback, $level = 10);
    }

    function triggerEvent($event_id)
    {
        $this->events->trigger($event_id);
    }

    function redirectTo($url, $status = null)
    {
        $status = $status ?: 307;
        $response = $this->getResponse();
        $response = $response
            ->withStatus($status)
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
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        return $response;
    }

    public function getBasePath()
    {
        if (defined('ROOT'))
            return ROOT;

        foreach (get_included_files() as $file) {
            if (($pos = strrpos($file, '/vendor/autoload.php')) !== false) {
                $path = substr($file, 0, $pos + 1);
                break;
            }
        }

        define('ROOT', $path);
        return $path;
    }

    public function process($request, $response, $args)
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
        foreach ($middlewares as $k => $mw) {
            $response = $mw->postProcess($request, $response, $args);
        }

        return $response;
    }

    function finalize($request, $response, $args)
    {
        $this->triggerEvent('finalize');

        return parent::finalize($request, $response, $args);
    }
}
