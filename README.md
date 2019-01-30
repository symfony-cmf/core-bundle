# Symfony CMF Core Bundle

[![Latest Stable Version](https://poser.pugx.org/symfony-cmf/core-bundle/v/stable)](https://packagist.org/packages/symfony-cmf/core-bundle)
[![Latest Unstable Version](https://poser.pugx.org/symfony-cmf/core-bundle/v/unstable)](https://packagist.org/packages/symfony-cmf/core-bundle)
[![License](https://poser.pugx.org/symfony-cmf/core-bundle/license)](https://packagist.org/packages/symfony-cmf/core-bundle)

[![Total Downloads](https://poser.pugx.org/symfony-cmf/core-bundle/downloads)](https://packagist.org/packages/symfony-cmf/core-bundle)
[![Monthly Downloads](https://poser.pugx.org/symfony-cmf/core-bundle/d/monthly)](https://packagist.org/packages/symfony-cmf/core-bundle)
[![Daily Downloads](https://poser.pugx.org/symfony-cmf/core-bundle/d/daily)](https://packagist.org/packages/symfony-cmf/core-bundle)

Branch | Travis | Coveralls | Scrutinizer |
------ | ------ | --------- | ----------- |
2.1   | [![Build Status][travis_stable_badge]][travis_stable_link]     | [![Coverage Status][coveralls_stable_badge]][coveralls_stable_link]     | [![Scrutinizer Status][scrutinizer_stable_badge]][scrutinizer_stable_link] |
dev-master | [![Build Status][travis_unstable_badge]][travis_unstable_link] | [![Coverage Status][coveralls_unstable_badge]][coveralls_unstable_link] | [![Scrutinizer Status][scrutinizer_unstable_badge]][scrutinizer_unstable_link] |


This package is part of the [Symfony Content Management Framework (CMF)](https://cmf.symfony.com/) and licensed
under the [MIT License](LICENSE).

The CoreBundle for the Symfony content management framework provides common functionality,
helpers and utilities for the other CMF bundles. The major features are a publish workflow,
a twig extension and php templating helper to walk PHPCR-ODM trees and support for optional translated content.


## Requirements

* PHP 7.1 / 7.2 / 7.3
* Symfony 3.4 / 4.0 / 4.1 / 4.2
* See also the `require` section of [composer.json](composer.json)

## Documentation

For the install guide and reference, see:

* [symfony-cmf/core-bundle Documentation](https://symfony.com/doc/master/cmf/bundles/core/index.html)

See also:

* [All Symfony CMF documentation](https://symfony.com/doc/master/cmf/index.html) - complete Symfony CMF reference
* [Symfony CMF Website](https://cmf.symfony.com/) - introduction, live demo, support and community links

## Support

For general support and questions, please use [StackOverflow](https://stackoverflow.com/questions/tagged/symfony-cmf).

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/blob/master/CONTRIBUTING.md)
guide.

Unit and/or functional tests exist for this package. See the
[Testing documentation](https://symfony.com/doc/master/cmf/components/testing.html)
for a guide to running the tests.

Thanks to
[everyone who has contributed](contributors) already.

## License

This package is available under the [MIT license](src/Resources/meta/LICENSE).

[travis_stable_badge]: https://travis-ci.org/symfony-cmf/core-bundle.svg?branch=2.1
[travis_stable_link]: https://travis-ci.org/symfony-cmf/core-bundle
[travis_unstable_badge]: https://travis-ci.org/symfony-cmf/core-bundle.svg?branch=dev-master
[travis_unstable_link]: https://travis-ci.org/symfony-cmf/core-bundle

[coveralls_stable_badge]: https://coveralls.io/repos/github/symfony-cmf/core-bundle/badge.svg?branch=2.1
[coveralls_stable_link]: https://coveralls.io/github/symfony-cmf/core-bundle?branch=2.1
[coveralls_unstable_badge]: https://coveralls.io/repos/github/symfony-cmf/core-bundle/badge.svg?branch=dev-master
[coveralls_unstable_link]: https://coveralls.io/github/symfony-cmf/core-bundle?branch=dev-master

[scrutinizer_stable_badge]: https://scrutinizer-ci.com/g/symfony-cmf/core-bundle/badges/quality-score.png?b=2.1
[scrutinizer_stable_link]: https://scrutinizer-ci.com/g/symfony-cmf/core-bundle/?branch=2.1
[scrutinizer_unstable_badge]: https://scrutinizer-ci.com/g/symfony-cmf/core-bundle/badges/quality-score.png?b=dev-master
[scrutinizer_unstable_link]: https://scrutinizer-ci.com/g/symfony-cmf/core-bundle/?branch=dev-master
