<?php
namespace Yoanm\JsonRpcServerPsr11Resolver\Domain\Model;

/**
 * Interface ServiceNameResolverInterface
 */
interface ServiceNameResolverInterface
{
    /**
     * @param string $methodName
     *
     * @return string The corresponding service name
     */
    public function resolve(string $methodName) : string;
}
