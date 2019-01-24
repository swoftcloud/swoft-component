<?php declare(strict_types=1);


namespace Swoft\Aop;

use Swoft\Aop\Point\JoinPoint;
use Swoft\Aop\Point\ProceedingJoinPoint;
use Swoft\Bean\BeanFactory;

/**
 * Class Handler
 *
 * @since 2.0
 */
class Handler
{
    /**
     * Method target
     *
     * @var AopTrait
     */
    private $target;

    /**
     * Method name
     *
     * @var string
     */
    private $methodName = '';

    /**
     * Method args
     *
     * @var array
     */
    private $args = [];

    /**
     * All aspect to do
     *
     * @var array
     */
    private $aspects = [];

    /**
     * Current aspect
     *
     * @var array
     */
    private $aspect;

    /**
     * Handler constructor.
     *
     * @param AopTrait $target
     * @param string   $methodName
     * @param array    $args
     * @param array    $aspects
     */
    public function __construct($target, string $methodName, array $args, array $aspects)
    {
        $this->target     = $target;
        $this->methodName = $methodName;
        $this->args       = $args;
        $this->aspect     = array_shift($aspects);
        $this->aspects    = $aspects;
    }

    /**
     * Invoke aspect
     *
     * @return mixed
     * @throws \Throwable
     */
    public function invokeAspect()
    {
        $around = $this->aspect['around'] ?? [];
        $after  = $this->aspect['after'] ?? [];
        $afRetn = $this->aspect['afterReturning'] ?? [];

        $result = null;
        if (!empty($around)) {
            // Invoke around advice
            $result = $this->invokeAdvice($around);
        } else {
            // Invoke target and before advice
            $result = $this->invokeTarget();
        }

        // Invoke after advice
        if (!empty($after)) {
            $this->invokeAdvice($after);
        }

        // Invoke afterReturning advice
        if (!empty($afRetn)) {
            $result = $this->invokeAdvice($afRetn);
        }

        return $result;
    }

    /**
     * Invoke target and before advcie
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Throwable
     */
    public function invokeTarget(array $params = [])
    {
        $before = $this->aspect['before'] ?? [];
        $afThw  = $this->aspect['afterThrowing'] ?? [];

        // Invoke before advice
        if (!empty($before)) {
            $this->invokeAdvice($before);
        }

        // Invoke next aspect
        if (!empty($this->aspects)) {
            return $this->nextHandler()->invokeAspect();
        }

        $result = null;
        $args   = empty($params) ? $this->args : $params;
        try {
            // Invoke target
            $result = $this->target->__invokeTarget($this->methodName, $args);
        } catch (\Throwable $e) {
            if (!empty($afThw)) {
                // Invoke afterThrowing advice
                return $this->invokeAdvice($afThw);
            }
            throw $e;
        }

        return $result;
    }

    /**
     * Invoke advice
     *
     * @param array      $aspectAry
     * @param \Throwable $catch
     * @param mixed      $return
     *
     * @return mixed
     */
    private function invokeAdvice(array $aspectAry, \Throwable $catch = null, $return = null)
    {
        list($aspectClass, $aspectMethod) = $aspectAry;

        // Reflection data from cache
        $rftAry = BeanFactory::getReflectionClass($aspectClass);
        $params = $rftAry['methods'][$aspectMethod]['params'] ?? [];

        $aspectArgs = [];
        foreach ($params as $param) {
            /* @var \ReflectionType $reflectType */
            list(, $reflectType) = $param;
            if ($reflectType === null) {
                $aspectArgs[] = null;
                continue;
            }

            // JoinPoint object
            $type = $reflectType->getName();
            if ($type === JoinPoint::class) {
                $aspectArgs[] = $this->getJoinPoint($catch, $return);
                continue;
            }

            // ProceedingJoinPoint object
            if ($type === ProceedingJoinPoint::class) {
                $aspectArgs[] = $this->getProceedingJoinPoint($catch, $return);
                continue;
            }

            $aspectArgs[] = null;
        }

        $aspect = bean($aspectClass);
        return $aspect->$aspectMethod(...$aspectArgs);
    }

    /**
     * New proceeding join point
     *
     * @param \Throwable|null $catch
     * @param mixed           $return
     *
     * @return ProceedingJoinPoint
     */
    private function getProceedingJoinPoint(\Throwable $catch = null, $return = null)
    {
        $proceedingJoinPoint = new ProceedingJoinPoint($this->target, $this->methodName, $this->args);
        $proceedingJoinPoint->setHandler($this);

        if (!empty($catch)) {
            $proceedingJoinPoint->setCatch($catch);
        }

        if (!empty($return)) {
            $proceedingJoinPoint->setReturn($return);
        }

        return $proceedingJoinPoint;
    }

    /**
     * New join point
     *
     * @param \Throwable|null $catch
     * @param mixed           $return
     *
     * @return JoinPoint
     */
    private function getJoinPoint(\Throwable $catch = null, $return = null): JoinPoint
    {
        $joinPoint = new JoinPoint($this->target, $this->methodName, $this->args);
        if (!empty($catch)) {
            $joinPoint->setCatch($catch);
        }

        if (!empty($return)) {
            $joinPoint->setReturn($return);
        }

        return $joinPoint;
    }

    /**
     * Next aspect handler
     *
     * @return Handler
     */
    private function nextHandler(): Handler
    {
        $aspect = clone  $this;

        // Next apsect data
        $aspect->aspect  = array_shift($this->aspects);
        $aspect->aspects = $this->aspects;

        return $aspect;
    }
}