<?php
/**
 * ViewMemcached Test Case
 *
 * @author   chnvcode
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     https://github.com/chnvcode/cakephp-viewmemcached
 */
namespace ViewMemcached\Test\TestCase\View\Helper;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use ViewMemcached\View\Helper\ViewMemcachedHelper;

/**
 * ViewMemcached\View\Helper\ViewMemcachedHelper Test Case
 */
class ViewMemcachedHelperTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Configure::write('Cache.disable', false);
        $request = new Request();
        $request->env('REQUEST_METHOD', 'GET');
        $request->here = '/pages/test';
        $response = new Response();
        $this->View = new View($request, $response);
        $this->View->loadHelper('ViewMemcached.ViewMemcached');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->View);
        parent::tearDown();
    }

    /**
     * Test Options
     *
     * @return void
     */
    public function testDefaultOptions()
    {
        $result = $this->View->ViewMemcached->config('gzip');
        $this->assertTextEquals(true, $result);

        $result = $this->View->ViewMemcached->config('gzip_compress_level');
        $this->assertTextEquals(6, $result);

        $result = $this->View->ViewMemcached->config('cache_config');
        $this->assertTextEquals('view_memcached', $result);
    }

    /**
     * Test Constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        Configure::write('Cache.disable', true);
        $result = $this->_testHelperEnabled();
        $this->assertEquals(false, $result);
        Configure::write('Cache.disable', false);

        $methods = ['POST', 'HEAD', 'PUT', 'DELETE', 'PATCH'];
        foreach ($methods as $method) {
            $result = $this->_testHelperEnabled($method);
            $this->assertEquals(false, $result);
        }
    }

    /**
     * testRender method
     *
     * @return void
     */
    public function testRender()
    {
        $this->View->viewPath = 'Pages';
        $this->View->set('test', 'render test');
        $content = $this->View->render('home', 'default');
        $this->assertTextEquals('Rendered with default layout: render test', $content);
    }

    /**
     * testGzipOn method
     *
     * @return void
     */
    public function testGzipOn()
    {
        $options = $this->View->ViewMemcached->config();
        $cacheConfig = $options['cache_config'];
        $compressLevel = $options['gzip_compress_level'];
        $cacheKey = $this->View->request->here;
        Cache::delete($cacheKey, $cacheConfig);

        $this->View->viewPath = 'Pages';
        $this->View->set('test', 'gzip on');
        $content = $this->View->render('home', 'default');
        $this->assertTextEquals('Rendered with default layout: gzip on', $content);

        $compressedContent = gzencode($content, $compressLevel);
        $cache = Cache::read($cacheKey, $cacheConfig);
        $this->assertTrue($cache === $compressedContent);
    }

    /**
     * testGzipOff method
     *
     * @return void
     */
    public function testGzipOff()
    {
        $this->View->ViewMemcached->config('gzip', false);
        $cacheConfig = $this->View->ViewMemcached->config('cache_config');
        $cacheKey = $this->View->request->here;
        Cache::delete($cacheKey, $cacheConfig);

        $this->View->viewPath = 'Pages';
        $this->View->set('test', 'gzip off');
        $content = $this->View->render('home', 'default');
        $this->assertTextEquals('Rendered with default layout: gzip off', $content);

        $cache = Cache::read($cacheKey, $cacheConfig);
        $this->assertTrue($cache === $content);
    }

    private function _testHelperEnabled($method = 'GET')
    {
        $request = new Request();
        $request->env('REQUEST_METHOD', $method);
        $View = new View($request);
        $helper = new ViewMemcachedHelper($View);
        return $helper->enabled();
    }
}
