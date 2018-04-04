<?php
namespace Tests\Functional\App\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Yoanm\JsonRpcServerPsr11Resolver\App\Resolver\PrefixerServiceNameResolver;

/**
 * @covers \Yoanm\JsonRpcServerPsr11Resolver\App\Resolver\PrefixerServiceNameResolver
 */
class PrefixerServiceNameResolverTest extends TestCase
{
    public function testShouldPrependPrefixBeforeServiceName()
    {
        $prefix = 'my-prefix';
        $serviceName = 'my-service-name';
        $resolver = new PrefixerServiceNameResolver($prefix);

        $this->assertSame(
            $prefix.$serviceName,
            $resolver->resolve($serviceName)
        );
    }
}
