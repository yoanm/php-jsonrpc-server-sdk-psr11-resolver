<?php
namespace Yoanm\JsonRpcServerPsr11Resolver\App\Resolver;

use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;

/**
 * Class PrefixerServiceNameResolver
 */
class PrefixerServiceNameResolver implements ServiceNameResolverInterface
{
    /** @var string */
    private $prefix = '';

    /**
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : string
    {
        return $this->prefix.$methodName;
    }
}
