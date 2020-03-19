<?php
/**
 * Created by PhpStorm.
 * User: matthew
 * Date: 27/03/18
 * Time: 9:13 AM
 */

namespace Wtf\Routing\Tests;

use Wtf\Routing\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouterTest extends TestCase
{

    /**
     * @param $routes
     *
     * @dataProvider providerRoutes
     */
    public function testConstructor($routes) {
        $testRouter = new Router($routes);
        $reflector = new \ReflectionClass('Wtf\Routing\Router');
        $property = $reflector->getProperty('_routes');
        $property->setAccessible(true);
        $result = $property->getValue($testRouter);
        $this->assertEquals($routes, $result);
    }

    /**
     * @param $routes
     *
     * @dataProvider providerRoutes
     */
    public function testBadProcess($routes) {
        $testRouter = new Router($routes);
        $testHandler = $this->createMock('Psr\Http\Server\RequestHandlerInterface');
        $testRequest = $this->createMock('Psr\Http\Message\ServerRequestInterface');

        $testRequest->expects($this->once())
            ->method('getRequestTarget')
            ->willReturn('/badRequest');

        try {
            $testRouter->process($testRequest, $testHandler);
            $this->fail('Invalid route should throw exception');
        } catch (\Exception $ex) {
            $this->assertEquals('Route not found', $ex->getMessage());
            $this->addToAssertionCount(1);
        }
    }

    public function providerRoutes() {
        return array (
            array(array("/" => "Test\\Controller\\testAction",
                "/index" => "Index\\Controller\\indexAction",
                "/test" => "Wtf\\Routing\\Tests\\testAction")),
        );
    }
}
