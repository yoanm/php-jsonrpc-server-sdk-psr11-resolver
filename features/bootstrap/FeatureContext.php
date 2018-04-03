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
use Yoanm\JsonRpcServerPsr11Resolver\App\Resolver\DefaultServiceNameResolver;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var ContainerMethodResolver */
    private $methodResolver;

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

        $this->methodResolver = new ContainerMethodResolver(
            $this->container->reveal(),
            new DefaultServiceNameResolver()
        );
    }

    /**
     * @Given there is a method named :methodName
     */
    public function givenThereIsAMethodNamed($methodName)
    {
        $this->prophesizedMethodList[$methodName] = $this->prophesizeMethod($methodName);
    }

    /**
     * @When I ask for :methodName method
     */
    public function whenIAskForMethod($methodName)
    {
        $this->lastException = $this->lastMethod = null;
        try {
            $this->lastMethod = $this->methodResolver->resolve($methodName);
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
     * @param string $methodName
     *
     * @return ObjectProphecy
     */
    private function prophesizeMethod(string $methodName)
    {
        $method = $this->prophet->prophesize(JsonRpcMethodInterface::class);

        $this->container->has($methodName)->willReturn(true);
        $this->container->get($methodName)->willReturn($method->reveal());

        return $method;
    }
}
