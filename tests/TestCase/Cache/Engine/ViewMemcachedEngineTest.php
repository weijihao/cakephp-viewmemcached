<?php
/**
 * ViewMemcached Test Case
 *
 * @author   chnvcode
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     https://github.com/chnvcode/cakephp-viewmemcached
 */
namespace ViewMemcached\Test\TestCase\Cache\Engine;

use Cake\TestSuite\TestCase;
use ViewMemcached\Cache\Engine\ViewMemcachedEngine;

/**
 * ViewMemcached\Cache\Engine\ViewMemcachedEngine Test Case
 */
class ViewMemcachedEngineTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->engine = new ViewMemcachedEngine();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->engine);
        parent::tearDown();
    }

    /**
     * Test Cache Key Generating
     *
     * @return void
     */
    public function testKey()
    {
        $uri = $expected = '/index.html?key=value';
        $result = $this->engine->key($uri);
        $this->assertTextEquals($expected, $result);
    }
}
