<?php

namespace IrfanTOOR\App;

use Exception;
use IrfanTOOR\Collection;
use IrfanTOOR\Engine\Http\Environment;
use IrfanTOOR\Datastore;

class Session extends Collection
{
    protected $started;
    protected $client_id;
    protected $ds;
    protected $env;

    function __construct($init = [])
    {
        if (!is_array($init)) {
            throw new Exception("passed parameter init must be an array", 1);
        }

        $defaults = [
            'path' => '/tmp/',
            'env'  => null,
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($init[$k])) {
                $init[$k] = $v;
            }
        }

        $this->ds = new Datastore(['path' => $init['path']]);
        $this->env = ($init['env']) ?: (new Environment())->toArray();

        $started = false;
    }

    function start()
    {
        if ($this->started)
            return;

        $this->started = true;

        $env = $this->env;

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

        if ($this->ds->hasKey($this->client_id)) {
            $s = json_decode($this->ds->getContents($this->client_id), 1);
        } else {
            $s = [
                'sid' => $this->client_id,
                'created_at' => time(),
                'updated_at' => 0,
                'tokens' => [],
            ];
        }

        $s['updated_at'] = $env['REQUEST_TIME'];
        $this->setMultiple($s);
        register_shutdown_function([$this, 'save']);
    }

    function getToken($token)
    {
        if (!$this->started)
            return null;

        return $this->get('tokens.' . $token);
    }

    function setToken($token, $value)
    {
        if (!$this->started)
            return;

        $this->set('tokens.' . $token, $value);
    }

    function removeToken($token)
    {
        if (!$this->started)
            return false;

        return $this->remove('tokens.' . $token);
    }

    function save()
    {
        if (!$this->started)
            return;

        $contents = json_encode($this->toArray());
        $this->ds->setComposite(
            [
                'key' => $this->client_id,
                'contents' => $contents
            ]
        );
    }

    function close()
    {
        $this->data = [];
        $this->started = false;
    }

    function destroy()
    {
        $this->close();
        $this->ds->removeContents($this->client_id);
    }
}
