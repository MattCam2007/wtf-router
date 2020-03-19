<?php
/**
 * Created by PhpStorm.
 * User: matthew
 * Date: 19/03/18
 * Time: 12:22 PM
 */

namespace Wtf\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    private $_middleware = [];
    private $_fallbackHandler = null;

    public function __construct(RequestHandlerInterface $fallback = null)
    {
        if($fallback !== null) {
            $this->_fallbackHandler = $fallback;
        }
    }

    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->_middleware[] = $middleware;
    }

    public function addMiddlewareArray(array $middleware) {
        $this->_middleware = array_merge($this->_middleware, $middleware);
    }

    public function clear()
    {
        $this->_middleware = [];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if(0 === count($this->_middleware)) {
            $this->processFallback($request);
        }
        $nextMiddleware = array_shift($this->_middleware);
        return $nextMiddleware->process($request, $this);
    }

    protected function processFallback($request)
    {
        if(!$this->_fallbackHandler !== null) {
            $this->_fallbackHandler->handle($request);
        }
        throw new \Exception("Middleware depleted, no response generated");
    }
}