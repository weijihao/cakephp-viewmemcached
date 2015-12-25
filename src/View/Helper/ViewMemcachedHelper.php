<?php
/**
 * ViewMemcached plugin for CakePHP
 *
 * @author   chnvcode
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     https://github.com/chnvcode/cakephp-viewmemcached
 */
namespace ViewMemcached\View\Helper;

use Cake\Core\Configure;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Helper for ViewMemcached
 *
 */
class ViewMemcachedHelper extends Helper
{
    private $_enabled = true;
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'gzip' => true,
        'gzip_compress_level' => 6,
        'cache_config' => 'view_memcached'
    ];

    /**
     * Constructor
     *
     * @param View $view View
     * @param array $config Config
     *
     * @return void
     */
    public function __construct(View $view, $config = [])
    {
        parent::__construct($view, $config);

        if (Configure::read('Cache.disable') || !$this->request->is('get')) {
            $this->_enabled = false;
        }
    }

    /**
     * After layout callback
     *
     * @param Event $view Event
     * @param string $layoutFile rendered layout file name
     *
     * @return boolean true
     */
    public function afterLayout(Event $event, $layoutFile)
    {
        if (!$this->enabled()) {
			return true;
		}

        $content = $this->_View->Blocks->get('content');
        if ($this->config('gzip')) {
            $content = gzencode($content, $this->config('gzip_compress_level'));
        }

        $key = $this->request->here;
        $config = $this->config('cache_config');
        if (Cache::read($key, $config) === false) {
            Cache::write($key, $content, $config);
        }
        return true;
    }

    /**
     * Return Enabled Value
     *
     * @return boolean
     */
    public function enabled()
    {
        return $this->_enabled;
    }
}
