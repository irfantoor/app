<?php

namespace IrfanTOOR\App;

use IrfanTOOR\App\Exception;
use IrfanTOOR\Collection;
use IrfanTOOR\Engine\Http\Environment;

class Session extends Collection
{
    protected $started;
    protected $client_id;
    protected $app;
    protected $path;
    protected $env = [];

    function __construct($init = [])
    {
        if (!is_array($init)) {
            throw new Exception("passed parameter init must be an array", 1);
        }

        $defaults = [
            'app'  => null,
            'path' => '/tmp/',
            'env'  => [],
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($init[$k])) {
                $init[$k] = $v;
            }
        }

        $this->app  = $init['app'];
        $this->path = $init['path'];
        $this->env  = $init['env'];

        $started = false;
    }

    function start()
    {
        if ($this->started)
            return;

        $this->started = true;

        if ($this->app) {
            $env = $app->Environment();
        } else {
            $env = new Environment($this->env);
        }

        $this->client_id = md5(
            __NAMESPACE__ .
            __CLASS__ .
            $env['SERVER_PROTOCOL'] .
            // $env['REQUEST_SCHEME'] .
            $env['SERVER_NAME'] .
            $env['SERVER_PORT'] .
            $env['HTTP_HOST'] .
            $env['HTTP_ACCEPT'] .
            $env['HTTP_ACCEPT_LANGUAGE'] .
            $env['HTTP_ACCEPT_CHARSET'].
            $env['HTTP_USER_AGENT'].
            $env['REMOTE_ADDR']
        );

        $this->file = $this->path . $this->client_id . '.json';

        if (file_exists($this->file)) {
            $s = json_decode(file_get_contents($this->file), 1);
        } else {
            $s = [
                'created_at' => $env['REQUEST_TIME'],
                'updated_at' => 0,
                'tokens' => [],
            ];
        }

        $s['updated_at'] = $env['REQUEST_TIME'];
        $this->set($s);
        register_shutdown_function([$this, 'save']);
    }

    function set($key, $value = null)
    {
        if (!$this->started)
            return;

        parent::set($key, $value);
    }

    function save()
    {
        if (!$this->started)
            return;

        $contents = json_encode($this->toArray());
        file_put_contents($this->file, $contents);
    }

    function close()
    {
        $this->data = [];
        $this->started = false;
    }

    function destroy()
    {
        $this->close();
        unlink($this->file);
    }
}
