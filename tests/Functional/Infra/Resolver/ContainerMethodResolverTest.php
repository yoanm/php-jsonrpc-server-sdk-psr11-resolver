<?php
namespace Tests\Functional\Infra\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
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

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->resolver = new ContainerMethodResolver(
            $this->container->reveal()
        );
    }

    public function testShouldLoadServiceFromContainerBasedOnMethodNameOnlyIfNoMappingDefined()
    {
        $methodName = 'my-method-name';

        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->container->has($methodName)
            ->willReturn(true)
            ->shouldBeCalled();

        $this->container->get($methodName)
            ->willReturn($method->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $method->reveal(),
            $this->resolver->resolve($methodName)
        );
    }


    public function testShouldResolveServiceNameAndLoadItFromContainer()
    {
        $methodName = 'my-method-name';
        $serviceName = 'my-service-name';

        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->resolver->addJsonRpcMethodMapping($methodName, $serviceName);

        $this->container->has($serviceName)
            ->willReturn(true)
            ->shouldBeCalled();

        $this->container->get($serviceName)
            ->willReturn($method->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $method->reveal(),
            $this->resolver->resolve($methodName)
        );
    }

    public function testShouldReturnNullIfMethodDoesNotExist()
    {
        $methodName = 'my-method-name';

        $this->container->has($methodName)
            ->willReturn(false)
            ->shouldBeCalled();

        $this->assertNull(
            $this->resolver->resolve($methodName)
        );
    }
}
