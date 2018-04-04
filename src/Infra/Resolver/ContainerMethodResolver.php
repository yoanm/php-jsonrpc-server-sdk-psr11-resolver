<?php
namespace Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver;

use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;

/**
 * Class ContainerMethodResolver
 */
class ContainerMethodResolver implements MethodResolverInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var ServiceNameResolverInterface|null */
    private $serviceNameResolver = null;

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
    public function resolve(string $methodName)
    {
        $serviceName = null !== $this->serviceNameResolver
            ? $this->serviceNameResolver->resolve($methodName)
            : $methodName
        ;
        if (!$this->container->has($serviceName)) {
            return null;
        }

        return $this->container->get($serviceName);
    }

    /**
     * @param ServiceNameResolverInterface $serviceNameResolver
     *
     * @return ContainerMethodResolver
     */
    public function setServiceNameResolver(ServiceNameResolverInterface $serviceNameResolver) : ContainerMethodResolver
    {
        $this->serviceNameResolver = $serviceNameResolver;

        return $this;
    }
}
