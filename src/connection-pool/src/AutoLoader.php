<?php declare(strict_types=1);


namespace Swoft\Connection\Pool;


use Swoft\SwoftComponent;

class AutoLoader extends SwoftComponent
{

    /**
     * @return array
     */
    public function metadata(): array
    {
        return [];
    }


    /**
     * Get namespace and dirs
     *
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }
}