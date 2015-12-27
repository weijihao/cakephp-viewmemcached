# ViewMemcached plugin for CakePHP 3.x

[![Build Status](https://travis-ci.org/chnvcode/cakephp-viewmemcached.svg?branch=master)](https://travis-ci.org/chnvcode/cakephp-viewmemcached)

Speed up CakePHP applications by view caching with Memcached and Nginx.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require chnvcode/cakephp-viewmemcached
```

## Cache Configuration

Add a new cache adapter `Cache.view_memcached` by editing the file `config/app.php`:

```
'view_memcached' => [
    'className' => 'ViewMemcached.ViewMemcached',
    'prefix' => 'example.com',
    'duration' => '+12 hours',
    'options' => [\Memcached::OPT_COMPRESSION => false]
]
```

## How to Use

Load the plugin helper from `AppController` (or any other controller you want):

### Load the helper with default options

```
public function beforeRender(Event $event)
{
    parent::beforeRender($event);
    $this->viewBuilder()->helpers(['ViewMemcached.ViewMemcached']);
}
```

Default options:

```
[
    'cacheConfig' => 'view_memcached',
    'gzipCompress' => true,
    'gzipCompressLevel' => 6
]
```

### Load the helper conditionally

```
public function beforeRender(Event $event)
{
    parent::beforeRender($event);

    if ($this->request->action === 'index') {
        $this->viewBuilder()->helpers(['ViewMemcached.ViewMemcached' => [
                'cacheConfig' => 'view_memcache_short'
            ]
        ]);
    }

    if ($this->request->action === 'view') {
        $this->viewBuilder()->helpers(['ViewMemcached.ViewMemcached' => [
                'cacheConfig' => 'view_memcache_long'
            ]
        ]);
    }
}
```
### Refresh a view cache

To refresh a view cache manually, simply set the view variable `ViewMemcachedHelper::FORCE_UPDATE` to `true` from a controller action.
This will delete the old cache and generate a new one when the next GET request is made.

Sample code:

```
<?php
namespace App\Controller;

use Cake\Event\Event;
use ViewMemcached\View\Helper\ViewMemcachedHelper;

class ArticlesController extends AppController
{
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->viewBuilder()->helpers(['ViewMemcached.ViewMemcached']);
    }    

    public function view($slug)
    { 
        if ($this->request->is('post')) {
            // do something

            $this->set(ViewMemcachedHelper::FORCE_UPDATE, true);
        }
        ...
    }
}
```

## Nginx Sample Configuration

```
upstream upstream_backend {
    server 127.0.0.1:8080;
}

upstream upstream_memcached {
    server 127.0.0.1:11211;
}

server {
    listen 80;
    server_name example.com;
    root /www/example.com/webroot/;

    location / {
        set $memcached_key $server_name$request_uri;
        add_header X-Memcached-Key  $memcached_key;
        gzip off;
        add_header Content-Encoding gzip;
        memcached_pass upstream_memcached;
        default_type text/html;
        error_page 404 405 502 504 = @fallback;
    }

    location @fallback {
        proxy_pass http://upstream_backend;
    }
}
```
