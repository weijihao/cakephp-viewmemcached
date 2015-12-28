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

        Cache::enable();
        $request = new Request();
        $request->env('REQUEST_METHOD', 'GET');
        $request->here = '/pages/home';
        $response = new Response();
        $this->View = new View($request, $response);
        $this->View->loadHelper('ViewMemcached.ViewMemcached', [
            'cacheConfig' => TEST_CACHE_CONFIG
        ]);
        $this->View->viewPath = 'Pages';
        $this->View->set('test', 'test value');
        $this->helper = $this->View->ViewMemcached;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->View);
        unset($this->helper);
        parent::tearDown();
    }

    /**
     * testConfig method
     *
     * @return void
     */
    public function testConfig()
    {
        $result = $this->helper->config('disable');
        $this->assertTextEquals(false, $result);

        $result = $this->helper->config('cacheKey');
        $this->assertTextEquals($this->View->request->here, $result);

        $result = $this->helper->config('cacheConfig');
        $this->assertTextEquals(TEST_CACHE_CONFIG, $result);

        $result = $this->helper->config('gzipCompress');
        $this->assertTextEquals(true, $result);

        $result = $this->helper->config('gzipCompressLevel');
        $this->assertTextEquals(6, $result);
    }

    /**
     * testCacheDisabled method
     *
     * @return void
     */
    public function testCacheDisabled()
    {
        Cache::disable();
        $result = $this->_testEnabled();
        $this->assertEquals(false, $result);
        Cache::enable();
    }

    /**
     * testRequestMethods method
     *
     * @return void
     */
    public function testRequestMethods()
    {
        $result = $this->_testEnabled();
        $this->assertEquals(true, $result);

        $methods = ['POST', 'HEAD', 'PUT', 'DELETE', 'PATCH'];
        foreach ($methods as $method) {
            $result = $this->_testEnabled($method);
            $this->assertEquals(false, $result);
        }
    }

    /**
     * testFlash method
     *
     * @return void
     */
    public function testFlash()
    {
        $this->View->request->session()->write('Flash.test');
        $this->View->render('home', 'default');
        $result = $this->helper->enabled();
        $this->assertEquals(false, $result);
        $this->View->request->session()->delete('Flash');
    }

    /**
     * testRender method
     *
     * @return void
     */
    public function testRender()
    {
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
        $options = $this->helper->config();
        $cacheConfig = $options['cacheConfig'];
        $compressLevel = $options['gzipCompressLevel'];
        $cacheKey = $this->helper->config('cacheKey');
        Cache::delete($cacheKey, $cacheConfig);

        $this->View->set('test', 'gzip on');
        $content = $this->View->render('home', 'default');
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
        $this->helper->config('gzipCompress', false);
        $cacheConfig = $this->helper->config('cacheConfig');
        $cacheKey = $this->helper->config('cacheKey');
        Cache::delete($cacheKey, $cacheConfig);

        $this->View->set('test', 'gzip off');
        $content = $this->View->render('home', 'default');
        $cache = Cache::read($cacheKey, $cacheConfig);
        $this->assertTrue($cache === $content);
    }

    /**
     * testViewVarDelete method
     *
     * @return void
     */
    public function testViewVarDelete()
    {
        $this->helper->config('gzipCompress', false);

        $cacheConfig = $this->helper->config('cacheConfig');
        $cacheKey = $this->helper->config('cacheKey');
        Cache::write($cacheKey, 'old cache', $cacheConfig);

        $this->View->set(ViewMemcachedHelper::DELETE, true);
        $this->View->set('test', 'delete');
        $content = $this->View->render('home', 'default');
        $cache = Cache::read($cacheKey, $cacheConfig);
        $this->assertTrue($cache === $content);
    }

    /**
     * testViewVarNocache method
     *
     * @return void
     */
    public function testViewVarNocache()
    {
        $this->helper->config('gzipCompress', false);

        $cacheConfig = $this->helper->config('cacheConfig');
        $cacheKey = $this->helper->config('cacheKey');
        Cache::delete($cacheKey, $cacheConfig);

        $this->View->set(ViewMemcachedHelper::NOCACHE, true);
        $this->View->set('test', 'nocache');
        $content = $this->View->render('home', 'default');
        $cache = Cache::read($cacheKey, $cacheConfig);
        $this->assertTrue($cache === false);
    }

    private function _testEnabled($method = 'GET')
    {
        $request = new Request();
        $request->env('REQUEST_METHOD', $method);
        $request->here = '/pages/home';
        $response = new Response();
        $View = new View($request, $response);
        $View->loadHelper('ViewMemcached.ViewMemcached', [
            'cacheConfig' => TEST_CACHE_CONFIG
        ]);
        $View->viewPath = 'Pages';
        $View->set('test', 'value');
        $View->render('home', 'default');
        return $View->ViewMemcached->enabled();
    }
}
