<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var string[] */
    private $methodMappingList = [];
    /** @var ObjectProphecy[] */
    private $methodList = [];
    /** @var JsonRpcMethodInterface|ObjectProphecy|null */
    private $lastMethod;

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
        $this->createMethodMapping($methodeName, $serviceId);
    }

    /**
     * @When I ask for :methodName JSON-RPC method
     */
    public function whenIAskForMethod($methodName)
    {
        $this->lastMethod = null;
        $this->lastMethod = $this->getMethodResolver()->resolve($methodName);
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
     * @return ContainerMethodResolver
     */
    private function getMethodResolver() : ContainerMethodResolver
    {
        $resolver = new ContainerMethodResolver($this->container->reveal());

        $this->setMethodsMapping($resolver);

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
    private function createMethodMapping(string $methodName, string $serviceId)
    {
        $this->methodMappingList[$methodName] = $serviceId;
        $this->container->has($serviceId)->willReturn(true);
        $this->container->get($serviceId)->willReturn($this->methodList[$methodName]);
    }

    /**
     * @return ObjectProphecy
     */
    private function setMethodsMapping(ContainerMethodResolver $resolver)
    {
        // Prophesize method calls based on given mapping
        foreach ($this->methodMappingList as $methodName => $serviceId) {
            $resolver->addJsonRpcMethodMapping($methodName, $serviceId);
        }
    }
}
