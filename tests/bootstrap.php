<?php
/**
 * ViewMemcached Test Case
 *
 * @author   chnvcode
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     https://github.com/chnvcode/cakephp-viewmemcached
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\DispatcherFactory;

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception("Cannot find the root of the application, unable to run tests");
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', $root);
define('ROOT_VM', dirname(__DIR__));

define('APP_DIR', 'test_app');
define('WEBROOT_DIR', 'webroot');
define('CONFIG', ROOT . DS . 'config' . DS);
define('APP', ROOT . DS . 'tests' . DS . APP_DIR . DS);
define('TEST_APP', ROOT_VM . DS . 'tests' . DS . APP_DIR . DS);
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TESTS', ROOT . DS . 'tests' . DS . 'Test' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require ROOT . '/vendor/cakephp/cakephp/src/basics.php';
require ROOT . '/vendor/autoload.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => 'src',
    'webroot' => 'webroot',
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'templates' => [TEST_APP . 'Template' . DS]
    ]
]);
Configure::write('Session', [
    'defaults' => 'php'
]);

define('TEST_CACHE_CONFIG', 'view_memcached_test');
Cache::config([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true
    ],
    'default' => [
        'engine' => 'File',
        'prefix' => 'default_',
        'serialize' => true
    ],
    TEST_CACHE_CONFIG => [
        'className' => 'ViewMemcached.ViewMemcached',
        'prefix' => '',
        'duration' => '+30 seconds',
        'compress' => false
    ],
]);

if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}
ConnectionManager::config('test', ['url' => getenv('db_dsn')]);

Plugin::load('ViewMemcached', ['path' => ROOT_VM]);
DispatcherFactory::add('ControllerFactory');
