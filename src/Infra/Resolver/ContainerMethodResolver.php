<?php
namespace Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver;

use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;

/**
 * Class ContainerMethodResolver
 */
class ContainerMethodResolver implements MethodResolverInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var ServiceNameResolverInterface */
    private $serviceNameResolver;

    public function __construct(
        ContainerInterface $container,
        ServiceNameResolverInterface $serviceNameResolver
    ) {
        $this->container = $container;
        $this->serviceNameResolver = $serviceNameResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : JsonRpcMethodInterface
    {
        $serviceName = $this->serviceNameResolver->resolve($methodName);
        if (!$this->container->has($serviceName)) {
            throw new JsonRpcMethodNotFoundException($methodName);
        }

        return $this->container->get($serviceName);
    }
}
