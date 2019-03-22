<?php declare(strict_types=1);


namespace Swoft\Http\Server\Listener;


use Swoft\Context\Context;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\HttpServerEvent;
use Swoft\Log\Logger;
use Swoft\Event\Listener\ListenerPriority;

/**
 * Class AfterRequestListener
 *
 * @since 2.0
 *
 * @Listener(event=HttpServerEvent::AFTER_REQUEST, priority=ListenerPriority::MIN)
 */
class AfterRequestListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     *
     * @throws \Exception
     */
    public function handle(EventInterface $event): void
    {
        /**
         * @var Response $response
         */
        $response = $event->getParam(0);
        $response->send();

        /* @var Logger $logger */
        $logger = \bean('logger');

        // Add notice log
        if ($logger->isEnable()) {
            $logger->appendNoticeLog();
        }

        // Destroy context
        Context::destroy();
    }
}
