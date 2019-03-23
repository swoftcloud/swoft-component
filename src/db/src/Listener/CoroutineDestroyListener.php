<?php declare(strict_types=1);


namespace Swoft\Db\Listener;


use Swoft\Bean\BeanFactory;
use Swoft\Db\ConnectionManager;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\SwoftEvent;

/**
 * Class CoroutineDestroyListener
 *
 * @since 2.0
 *
 * @Listener(SwoftEvent::COROUTINE_DESTROY)
 */
class CoroutineDestroyListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     *
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function handle(EventInterface $event): void
    {
        /* @var ConnectionManager $cm  Destroy connections*/
//        $cm = BeanFactory::getBean(ConnectionManager::class);
//        $cm->destroy();
    }
}