<?php
namespace Wtf\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container;
use DI\get as barf;

class Router implements MiddlewareInterface {
    protected $_routes;
    protected $_request;

    protected $_attributes;
    protected $_route;

    public function __construct($routes) {
        $this->_routes = $routes;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->_request = $request;

        try {
            $selectedRoute = $this->getTheRoute();
        } catch (\Exception $e) {
            throw $e;
        }
        $container = require getcwd() . '/config/bootstrap.php';
        $response = $container->call($selectedRoute, [$this->_request]);
        return $response;
    }

    protected function getTheRoute() {
        $winningRoute = null;
        $routes = array_keys($this->_routes);
        $urlWithVarNames = preg_replace('/{([a-z]+):.+?}/', '{' . "$1" . '}', $routes);
        $urlWithRexex = preg_replace('/{[a-z]+:(.+?)}/', '(' . "$1" . ')', $routes);
        $urlWithRexex = str_replace('/', '\/', $urlWithRexex);
        $requestLen = count(explode('/', $this->_request->getRequestTarget()));
        $requestValues = null;
        $i = 0;
        /*echo "<PRE>" . print_r($routes, true) . "</PRE>";
        echo "<PRE>" . print_r($this->_request->getRequestTarget(), true) . "</PRE>";
        echo "<PRE>" . print_r($urlWithRexex, true) . "</PRE>";
        echo "<PRE>" . print_r($urlWithVarNames, true) . "</PRE>";
        die();*/
        $valid = false;
        foreach($urlWithRexex as $regex) {
            $routeLen = count(explode('/', $regex));
            if($routeLen != $requestLen) {
                $i++;
                continue;
            }
            $requestValues = null;
            $matchRoute = preg_match('/' . $regex . '/', $this->_request->getRequestTarget(), $requestValues);

            if(!$matchRoute) {
                $i++;
                continue;
            }
            $valid = true;
            break;
        }

        if(!$valid) {
            throw new \Exception("Route Not Found!", 404);
        }

        $numberOfVariables = preg_match_all('/{.*?}/', $urlWithVarNames[$i], $requestVariables);
        $fullmatch = array_shift($requestValues);
        $removeCurlyBraces = preg_replace('/({|})/', '', $requestVariables[0]);
        $requestAttributeArray = array_combine($removeCurlyBraces, $requestValues);

        foreach ($requestAttributeArray as $key => $value) {
            $this->_request = $this->_request->withAttribute($key, $value);
        }

        return $this->_routes[$routes[$i]];
    }
}