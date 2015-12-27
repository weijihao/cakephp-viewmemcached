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
    'gzip' => true,
    'gzip_compress_level' => 6,
    'cache_config' => 'view_memcached'
]
```

### Load the helper conditionally

```
public function beforeRender(Event $event)
{
    parent::beforeRender($event);

    if ($this->request->action === 'index') {
        $this->viewBuilder()->helpers(['ViewMemcached.ViewMemcached' => [
                'cache_config' => 'view_memcache_short'
            ]
        ]);
    }

    if ($this->request->action === 'view') {
        $this->viewBuilder()->helpers(['ViewMemcached.ViewMemcached' => [
                'cache_config' => 'view_memcache_long'
            ]
        ]);
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
