<?php

namespace Wtf\Routing;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request Handler class
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Actual middleware pipeline
     *
     * @var array
     */
    private $_middleware = [];

    /**
     * Call this middleware if no other middleware handle the request.
     *
     * @var RequestHandlerInterface|null
     */
    private $_fallbackHandler = null;

    /**
     * RequestHandler constructor.
     * @param RequestHandlerInterface|null $fallback
     */
    public function __construct(RequestHandlerInterface $fallback = null)
    {
        if($fallback !== null) {
            $this->_fallbackHandler = $fallback;
        }
    }

    /**
     * Add a middleware to the pipeline.
     *
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->_middleware[] = $middleware;
    }

    /**
     * Add an array of middleware to the pipeline.
     *
     * @param array $middleware
     */
    public function addMiddlewareArray(array $middleware) {
        $this->_middleware = array_merge($this->_middleware, $middleware);
    }

    /**
     * Clear the middleware pipeline.
     *
     */
    public function clear()
    {
        $this->_middleware = [];
    }

    /**
     * Process the next middleware in the pipeline. Passes the request, and itself into the middleware to be handled.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if(0 === count($this->_middleware)) {
            $this->processFallback($request);
        }
        $nextMiddleware = array_shift($this->_middleware);
        return $nextMiddleware->process($request, $this);
    }

    /**
     * Calls the fallback middleware handler, or throws an exception.
     *
     * @param $request
     * @throws Exception
     */
    protected function processFallback($request)
    {
        if(!$this->_fallbackHandler !== null) {
            $this->_fallbackHandler->handle($request);
        }
        throw new Exception("Middleware depleted, no response generated");
    }
}