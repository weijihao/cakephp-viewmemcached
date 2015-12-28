<?php
/**
 * ViewMemcached plugin for CakePHP
 *
 * @author   chnvcode
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     https://github.com/chnvcode/cakephp-viewmemcached
 */
namespace ViewMemcached\View\Helper;

use Cake\Cache\Cache;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\Routing\Router;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Helper for ViewMemcached
 *
 */
class ViewMemcachedHelper extends Helper
{

    /**
     * Constant for 'viewMemcachedNocache'
     *
     * @var string
     */
    const NOCACHE = 'viewMemcachedNocache';

    /**
     * Constant for 'viewMemcachedDelete'
     *
     * @var string
     */
    const DELETE = 'viewMemcachedDelete';

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'disable' => false,
        'cacheKey' => null,
        'cacheConfig' => 'view_memcached',
        'gzipCompress' => true,
        'gzipCompressLevel' => 6
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

        $this->config('cacheKey', $this->request->here);
    }

    /**
     * Callback for Helper::beforeRender
     *
     * @param Event $view Event
     * @param string $layoutFile layout file name
     *
     * @return bool true
     */
    public function beforeLayout(Event $event, $layoutFile)
    {
        if (!Cache::enabled()) {
            $this->config('disable', true);
        } elseif (!$this->request->is('get')) {
            $this->config('disable', true);
        } elseif ($this->request->session()->check('Flash')) {
            $this->config('disable', true);
        } elseif ($this->_View->get(ViewMemcachedHelper::NOCACHE)) {
            $this->config('disable', true);
        }

        if ($this->_View->get(ViewMemcachedHelper::DELETE) === true) {
            Cache::delete($this->config('cacheKey'), $this->config('cacheConfig'));
        }
        return true;
    }

    /**
     * Callback for Helper::afterLayout
     *
     * @param Event $view Event
     * @param string $layoutFile rendered layout file name
     *
     * @return bool true
     */
    public function afterLayout(Event $event, $layoutFile)
    {
        if ($this->config('disable')) {
            return true;
        }

        try {
            $content = $this->_View->Blocks->get('content');
            if ($this->config('gzipCompress') === true) {
                $content = gzencode($content, intval($this->config('gzipCompressLevel')));
            }

            $cacheKey = $this->config('cacheKey');
            $cacheConfig = $this->config('cacheConfig');
            if (Cache::read($cacheKey, $cacheConfig) === false) {
                Cache::write($cacheKey, $content, $cacheConfig);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        return true;
    }

    /**
     * Return caching status
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->config('disable') === false;
    }
}
