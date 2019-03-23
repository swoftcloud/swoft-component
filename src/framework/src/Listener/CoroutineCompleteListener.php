<?php declare(strict_types=1);


namespace Swoft\Listener;


use Swoft\Bean\BeanEvent;
use Swoft\Co;
use Swoft\Context\Context;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Log\Logger;
use Swoft\SwoftEvent;

/**
 * Class CoroutineDestroyListener
 *
 * @since 2.0
 *
 * @Listener(SwoftEvent::COROUTINE_COMPLETE)
 */
class CoroutineCompleteListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
//        \sgo(function () {
            Context::getContextWaitGroup()->wait();

            /* @var Logger $logger */
            $logger = \bean('logger');

            // Add notice log
            if ($logger->isEnable()) {
                $logger->appendNoticeLog();
            }

            // Trigger destroy request bean
            \Swoft::trigger(BeanEvent::DESTROY_REQUEST, $this, Co::tid());

            // Trigger destroy coroutine
            \Swoft::trigger(SwoftEvent::COROUTINE_DESTROY);

            // Destroy context
            Context::destroy();

//        }, false);
    }
}