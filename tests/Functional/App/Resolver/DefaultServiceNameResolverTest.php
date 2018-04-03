<?php
namespace Tests\Functional\App\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Yoanm\JsonRpcServerPsr11Resolver\App\Resolver\DefaultServiceNameResolver;

/**
 * @covers \Yoanm\JsonRpcServerPsr11Resolver\App\Resolver\DefaultServiceNameResolver
 */
class DefaultServiceNameResolverTest extends TestCase
{
    public function testShouldDoNothingByDefault()
    {
        $serviceName = 'my-service-name';
        $resolver = new DefaultServiceNameResolver();

        $this->assertSame(
            $serviceName,
            $resolver->resolve($serviceName)
        );
    }


    public function testShouldPrependPrefixBeforeServiceName()
    {
        $prefix = 'my-prefix';
        $serviceName = 'my-service-name';
        $resolver = new DefaultServiceNameResolver($prefix);

        $this->assertSame(
            $prefix.$serviceName,
            $resolver->resolve($serviceName)
        );
    }
}
