<?php declare(strict_types=1);

namespace SwoftTest\WebSocket\Server\Testing;

use ReflectionException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Exception\ContainerException;
use Swoft\WebSocket\Server\Contract\MessageHandlerInterface;
use Swoft\WebSocket\Server\Contract\MiddlewareInterface;
use Swoft\WebSocket\Server\Contract\RequestInterface;
use Swoft\WebSocket\Server\Contract\ResponseInterface;
use Swoft\WebSocket\Server\Message\Response;

/**
 * Class CoreMiddleware
 *
 * @Bean()
 */
class CoreMiddleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface        $request
     * @param MessageHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function process(RequestInterface $request, MessageHandlerInterface $handler): ResponseInterface
    {
        $resp = Response::new(100);

        return $resp->setData('[CORE]');
    }
}
