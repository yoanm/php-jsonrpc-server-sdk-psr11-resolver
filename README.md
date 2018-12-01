# PSR-11 method resolver for [`jsonrpc-server-sdk`](https://github.com/yoanm/php-jsonrpc-server-sdk)
[![License](https://img.shields.io/github/license/yoanm/php-jsonrpc-server-sdk-psr11-resolver.svg)](https://github.com/yoanm/php-jsonrpc-server-sdk-psr11-resolver) [![Code size](https://img.shields.io/github/languages/code-size/yoanm/php-jsonrpc-server-sdk-psr11-resolver.svg)](https://github.com/yoanm/php-jsonrpc-server-sdk-psr11-resolver) [![Dependencies](https://img.shields.io/librariesio/github/yoanm/php-jsonrpc-server-sdk-psr11-resolver.svg)](https://libraries.io/packagist/yoanm%2Fjsonrpc-server-sdk-psr11-resolver)

[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/yoanm/php-jsonrpc-server-sdk-psr11-resolver.svg?label=Scrutinizer&logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk-psr11-resolver/build-status/master) [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/yoanm/php-jsonrpc-server-sdk-psr11-resolver/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk-psr11-resolver/?branch=master) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/yoanm/php-jsonrpc-server-sdk-psr11-resolver/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk-psr11-resolver/?branch=master)

[![Travis Build Status](https://img.shields.io/travis/yoanm/php-jsonrpc-server-sdk-psr11-resolver/master.svg?label=Travis&logo=travis)](https://travis-ci.org/yoanm/php-jsonrpc-server-sdk-psr11-resolver) [![Travis PHP versions](https://img.shields.io/travis/php-v/yoanm/php-jsonrpc-server-sdk-psr11-resolver.svg?logo=travis)](https://travis-ci.org/yoanm/php-jsonrpc-server-sdk-psr11-resolver)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/jsonrpc-server-sdk-psr11-resolver.svg)](https://packagist.org/packages/yoanm/jsonrpc-server-sdk-psr11-resolver) [![Packagist PHP version](https://img.shields.io/packagist/php-v/yoanm/jsonrpc-server-sdk-psr11-resolver.svg)](https://packagist.org/packages/yoanm/jsonrpc-server-sdk-psr11-resolver)

PSR-11 compliant method resolver for [`jsonrpc-server-sdk`](https://github.com/yoanm/php-jsonrpc-server-sdk)

Resolver will simply load the method from container if it exists.

Advantage of loading the method only when ask is that all underlying autoloading will be done only when required and only for the given method. Instead of instanciating all methods (and so their dependencies, dependencies of dependencies, etc) and store them inside a simple array uselessly on each request.

## How to use

 - Inject the container
 ```php
 use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;
 
 $resolver = new ContainerMethodResolver($psr11ContainerInterface);
 ```
 - Inject your mapping
 
 ```php
 $resolver->addJsonRpcMethodMapping('jsonrpc.method_name.a', 'service.method.a');
 $resolver->addJsonRpcMethodMapping('jsonrpc.method_name.b', 'service.method.b');
 ...
 ```
 - And use it
 ```php
 // Will return the service stored inside the container under the name "service.method.a"
 $resolver->resolve('jsonrpc.method_name.a');
 
 // Will return null
 $resolver->resolve('unknown');
 
 // You can directly ask for a service id, no need to map it
 // Will return the service stored inside the container under the name "service.method.c" (if exists)
 $resolver->resolve('service.method.c');
 ```

## Contributing
See [contributing note](./CONTRIBUTING.md)
