<?php
namespace Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver;

use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;

/**
 * Class ContainerMethodResolver
 */
class ContainerMethodResolver implements JsonRpcMethodResolverInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var string[] */
    private $methodMappingList = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : ?JsonRpcMethodInterface
    {
        $serviceName = $this->resolveMethodNameToServiceId($methodName);

        return $this->container->has($serviceName)
            ? $this->container->get($serviceName)
            : null
        ;
    }

    /**
     * @param string $methodName
     * @param string $containerServiceId
     */
    public function addJsonRpcMethodMapping(string $methodName, string $containerServiceId) : void
    {
        $this->methodMappingList[$methodName] = $containerServiceId;
    }

    /**
     * @param string $methodName
     *
     * @return string Method's identifier (returns original method name if no mapping defined)
     */
    protected function resolveMethodNameToServiceId(string $methodName) : string
    {
        return isset($this->methodMappingList[$methodName])
            ? $this->methodMappingList[$methodName]
            : $methodName
        ;
    }
}
