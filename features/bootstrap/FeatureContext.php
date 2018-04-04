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
use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var string[] */
    private $serviceNameResolverMapping = [];
    /** @var ObjectProphecy[] */
    private $methodList = [];
    /** @var JsonRpcMethodInterface|ObjectProphecy|null */
    private $lastMethod;
    /** @var JsonRpcMethodNotFoundException|null */
    private $lastException;

    /** @var Prophet */
    private $prophet;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

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
     * @Given there is a :serviceId service for :methodName JSON-RPC method
     */
    public function givenThereIsAServiceMethodNamed($serviceId, $methodeName)
    {
        $this->createJsonRpcMethod($methodeName);
        $this->bindJsonRpcMethodToContainerServiceId($methodeName, $serviceId);
    }

    /**
     * @Given ServiceNameResolver will resolve :methodName JSON-RPC method to :serviceId service
     */
    public function givenServiceNameResolverWillResolveMethodNameToServiceId($methodeName, $serviceId)
    {
        $this->addServiceNameResolverMapping($methodeName, $serviceId);
    }

    /**
     * @When I ask for :methodName JSON-RPC method
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
     * @Then I should have :methodName JSON-RPC method
     * @Then I should have a null JSON-RPC method
     */
    public function thenIShouldHaveMethod($methodName = null)
    {
        Assert::assertSame(
            null === $methodName ? $methodName : $this->methodList[$methodName]->reveal(),
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

        $this->prophesizeServiceNameResolverIfDefined($resolver);

        return $resolver;
    }

    /**
     * @param string $methodName
     */
    private function createJsonRpcMethod(string $methodName)
    {
        $this->methodList[$methodName] = $this->prophet->prophesize(JsonRpcMethodInterface::class);
    }

    /**
     * @param $serviceId
     * @param $methodName
     */
    private function bindJsonRpcMethodToContainerServiceId(string $methodName, string $serviceId)
    {
        $this->container->has($serviceId)->willReturn(true);
        $this->container->get($serviceId)->willReturn($this->methodList[$methodName]);
    }

    /**
     * @param string $methodeName
     * @param string $serviceId
     */
    private function addServiceNameResolverMapping(string $methodeName, string $serviceId)
    {
        $this->serviceNameResolverMapping[$methodeName] = $serviceId;
    }

    /**
     * @return ObjectProphecy
     */
    private function prophesizeServiceNameResolverIfDefined(ContainerMethodResolver $resolver)
    {
        // Append service name resolver if some mapping have been defined
        if (count($this->serviceNameResolverMapping)) {
            /** @var ServiceNameResolverInterface|ObjectProphecy $serviceNameResolver */
            $serviceNameResolver = $this->prophet->prophesize(ServiceNameResolverInterface::class);
            // Prophesize method calls based on given mapping
            foreach ($this->serviceNameResolverMapping as $methodName => $serviceId) {
                $serviceNameResolver->resolve($methodName)
                    ->willReturn($serviceId);
            }

            $resolver->setServiceNameResolver($serviceNameResolver->reveal());
        }
    }
}
