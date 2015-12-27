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

        $options = ['cacheConfig' => TEST_CACHE_CONFIG];
        $this->View->loadHelper('ViewMemcached.ViewMemcached', $options);
        if ($this->View->ViewMemcached !== null) {
            $this->helper = $this->View->ViewMemcached;
        }
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->View, $this->helper);
        parent::tearDown();
    }

    /**
     * Test Options
     *
     * @return void
     */
    public function testConfig()
    {
        $result = $this->helper->config('gzipCompress');
        $this->assertTextEquals(true, $result);

        $result = $this->helper->config('gzipCompressLevel');
        $this->assertTextEquals(6, $result);

        $result = $this->helper->config('cacheConfig');
        $this->assertTextEquals(TEST_CACHE_CONFIG, $result);
    }

    /**
     * Test Constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        Cache::disable();
        $result = $this->_testHelperEnabled();
        $this->assertEquals(false, $result);
        Cache::enable();

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
        $options = $this->helper->config();
        $cacheConfig = $options['cacheConfig'];
        $compressLevel = $options['gzipCompressLevel'];
        $cacheKey = $this->helper->config('cacheKey');
        Cache::delete($cacheKey, $cacheConfig);

        $this->View->viewPath = 'Pages';
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

        $this->View->viewPath = 'Pages';
        $this->View->set('test', 'gzip off');
        $content = $this->View->render('home', 'default');
        $cache = Cache::read($cacheKey, $cacheConfig);
        $this->assertTrue($cache === $content);
    }

    /**
     * testForceUpdate method
     *
     * @return void
     */
    public function testForceUpdate()
    {
        $this->helper->config('gzipCompress', false);

        $cacheConfig = $this->helper->config('cacheConfig');
        $cacheKey = $this->helper->config('cacheKey');
        Cache::write($cacheKey, 'old view cache', $cacheConfig);

        $this->View->viewPath = 'Pages';
        $this->View->set(ViewMemcachedHelper::FORCE_UPDATE, true);
        $this->View->set('test', 'force update');
        $content = $this->View->render('home', 'default');
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
