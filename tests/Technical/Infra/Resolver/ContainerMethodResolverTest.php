<?php
namespace Tests\Techincal\Infra\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;

/**
 * @covers \Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver
 */
class ContainerMethodResolverTest extends TestCase
{
    /** @var ContainerMethodResolver */
    private $resolver;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;
    /** @var ServiceNameResolverInterface|ObjectProphecy */
    private $serviceNameResolver;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->serviceNameResolver = $this->prophesize(ServiceNameResolverInterface::class);

        $this->resolver = new ContainerMethodResolver($this->container->reveal());
    }

    public function testShouldThrowAnExceptionIfMethodDoesNotExistAndNotShowRealServiceNameWhenResolverIsUsed()
    {
        $methodName = 'my-method-name';
        $serviceName = 'my-service-name';

        $this->serviceNameResolver->resolve($methodName)
            ->willReturn($serviceName)
            ->shouldBeCalled();

        $this->container->has($serviceName)
            ->willReturn(false)
            ->shouldBeCalled();

        $this->resolver->setServiceNameResolver($this->serviceNameResolver->reveal());

        $this->expectException(JsonRpcMethodNotFoundException::class);

        try {
            $this->resolver->resolve($methodName);
        } catch (JsonRpcMethodNotFoundException $e) {
            // Assert it's the method name and not the resolved service name
            $this->assertSame(
                $methodName,
                $e->getMethodName()
            );

            throw $e;
        }
    }
}
