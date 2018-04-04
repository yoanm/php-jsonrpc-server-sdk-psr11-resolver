<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServerPsr11Resolver\App\Resolver\PrefixerServiceNameResolver;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var string[] */
    private $expectedServiceMethodList = [];
    /** @var ObjectProphecy[] */
    private $prophesizedMethodList = [];
    /** @var JsonRpcMethodInterface|ObjectProphecy|null */
    private $lastMethod;
    /** @var JsonRpcMethodNotFoundException|null */
    private $lastException;

    /** @var Prophet */
    private $prophet;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;
    /** @var string|null */
    private $serviceNamePrefix = null;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->prophet = new Prophet();
        $this->container = $this->prophet->prophesize(ContainerInterface::class);
        // By default return false
        $this->container->has(Argument::cetera())->willReturn(false);
    }

    /**
     * @Given there is a service method named :methodName
     */
    public function givenThereIsAServiceMethodNamed($methodName)
    {
        $this->expectedServiceMethodList[] = $methodName;
    }

    /**
     * @Given there is a service name resolver with prefix :prefix
     */
    public function givenThereIsAServiceNameResolverWithPrefix($prefix)
    {
        $this->serviceNamePrefix = $prefix;
    }

    /**
     * @When I ask for :methodName method
     */
    public function whenIAskForMethod($methodName)
    {
        $this->lastException = $this->lastMethod = null;
        try {
            $this->lastMethod = $this->getMethodResolver()->resolve($methodName);
        } catch (JsonRpcMethodNotFoundException $exception) {
            $this->lastException = $exception;
        }
    }

    /**
     * @Then I should have :methodName method
     */
    public function thenIShouldHaveMethod($methodName)
    {
        Assert::assertSame(
            $this->prophesizedMethodList[$methodName]->reveal(),
            $this->lastMethod
        );
    }
    /**
     * @Then I should have a JSON-RPC exception with code :errorCode
     */
    public function thenIShouldHaveAJsonRpcExceptionWithCode($errorCode)
    {
        Assert::assertInstanceOf(JsonRpcMethodNotFoundException::class, $this->lastException);
        Assert::assertSame((int)$errorCode, $this->lastException->getErrorCode());
    }

    /**
     * @return ContainerMethodResolver
     */
    private function getMethodResolver() : ContainerMethodResolver
    {
        $resolver = new ContainerMethodResolver($this->container->reveal());

        // Append service name prefixer if a prefix has been defined
        if ($this->serviceNamePrefix) {
            $resolver->setServiceNameResolver(
                new PrefixerServiceNameResolver(
                    $this->serviceNamePrefix
                )
            );
        }

        // Configure expected service methods
        foreach($this->expectedServiceMethodList as $serviceName) {
            $method = $this->prophet->prophesize(JsonRpcMethodInterface::class);
            $realMethodName = $this->guessRealMethodName($serviceName);
            $this->prophesizedMethodList[$realMethodName] = $method;

            $this->container->has($serviceName)->willReturn(true);
            $this->container->get($serviceName)->willReturn($method->reveal());
        }

        return $resolver;
    }

    private function guessRealMethodName($methodName)
    {
        if ($this->serviceNamePrefix) {
            return preg_replace('/^'.preg_quote($this->serviceNamePrefix).'/', '', $methodName);
        }

        return $methodName;
    }
}
