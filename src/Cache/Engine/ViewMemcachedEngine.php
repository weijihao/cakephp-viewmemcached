<?php
/**
 * ViewMemcached plugin for CakePHP
 *
 * @author   chnvcode
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     https://github.com/chnvcode/cakephp-viewmemcached
 */
namespace ViewMemcached\Cache\Engine;

use Cake\Cache\Engine\MemcachedEngine;

/**
 * Storage engine for ViewMemcached
 *
 */
class ViewMemcachedEngine extends MemcachedEngine
{
    /**
     * Override the default process of key generating
     *
     * @param string $key the key passed over
     * @return bool|string string key or false
     */
    public function key($key)
    {
        if (empty($key)) {
            return false;
        }
        return strval($key);
    }
}
