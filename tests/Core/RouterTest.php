<?php
namespace Tests\Core;

use App\Core\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    public function testGetRouteRegistration(): void
    {
        $this->router->get('/test', 'TestController@index');

        // Use reflection to verify route was added
        $ref = new \ReflectionClass($this->router);
        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        $routes = $prop->getValue($this->router);

        $this->assertCount(1, $routes);
        $this->assertEquals('GET', $routes[0]['method']);
        $this->assertEquals('/test', $routes[0]['path']);
        $this->assertEquals('App\\Controllers\\TestController', $routes[0]['controller']);
        $this->assertEquals('index', $routes[0]['action']);
    }

    public function testPostRouteRegistration(): void
    {
        $this->router->post('/submit', 'FormController@store');

        $ref = new \ReflectionClass($this->router);
        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        $routes = $prop->getValue($this->router);

        $this->assertCount(1, $routes);
        $this->assertEquals('POST', $routes[0]['method']);
        $this->assertEquals('App\\Controllers\\FormController', $routes[0]['controller']);
        $this->assertEquals('store', $routes[0]['action']);
    }

    public function testMatchPathExact(): void
    {
        $ref = new \ReflectionClass($this->router);
        $method = $ref->getMethod('matchPath');
        $method->setAccessible(true);

        $params = [];
        $result = $method->invokeArgs($this->router, ['/login', '/login', &$params]);

        $this->assertTrue($result);
        $this->assertEmpty($params);
    }

    public function testMatchPathWithParameter(): void
    {
        $ref = new \ReflectionClass($this->router);
        $method = $ref->getMethod('matchPath');
        $method->setAccessible(true);

        $params = [];
        $result = $method->invokeArgs($this->router, ['/user/{id}', '/user/42', &$params]);

        $this->assertTrue($result);
        $this->assertEquals(['42'], $params);
    }

    public function testMatchPathNoMatch(): void
    {
        $ref = new \ReflectionClass($this->router);
        $method = $ref->getMethod('matchPath');
        $method->setAccessible(true);

        $params = [];
        $result = $method->invokeArgs($this->router, ['/login', '/register', &$params]);

        $this->assertFalse($result);
    }

    public function testDispatch404(): void
    {
        $this->router->get('/existing', 'HomeController@index');

        ob_start();
        $this->router->dispatch('/nonexistent', 'GET');
        $output = ob_get_clean();

        $this->assertStringContainsString('404', $output);
        $this->assertEquals(404, http_response_code());
    }
}
