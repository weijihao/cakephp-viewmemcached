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
use Cake\View\Helper;
use Cake\View\View;

/**
 * Helper for ViewMemcached
 *
 */
class ViewMemcachedHelper extends Helper
{

    /**
     * Constant for 'viewmemcached_force_update'
     *
     * @var string
     */
    const FORCE_UPDATE = 'view_memcached_force_update';

    /**
     * Variable for view caching
     *
     * @var bool
     */
    protected $_enabled = true;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
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

        if (!Cache::enabled() || !$this->request->is('get')) {
            $this->_enabled = false;
        }
        $this->config('cacheKey', $this->request->here);
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
        if ($this->_View->get(ViewMemcachedHelper::FORCE_UPDATE) === true) {
            Cache::delete($this->config('cacheKey'), $this->config('cacheConfig'));
        }

        if (!$this->_enabled) {
            return true;
        }

        try {
            $content = $this->_View->Blocks->get('content');
            if ($this->config('gzipCompress') === true) {
                $content = gzencode($content, intval($this->config('gzipCompressLevel')));
            }
            Cache::write($this->config('cacheKey'), $content, $this->config('cacheConfig'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        return true;
    }

    /**
     * Return value of property $_enabled
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->_enabled;
    }
}
