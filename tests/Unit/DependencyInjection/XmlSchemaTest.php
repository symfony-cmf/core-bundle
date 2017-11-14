<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\DependencyInjection;

use Symfony\Cmf\Component\Testing\Unit\XmlSchemaTestCase;

class XmlSchemaTest extends XmlSchemaTestCase
{
    public function testSchema()
    {
        $xmlFiles = array_map(function ($file) {
            return __DIR__.'/../../Fixtures/config/'.$file;
        }, [
            'config1.xml',
            'config2.xml',
            'config3.xml',
        ]);

        $this->assertSchemaAcceptsXml($xmlFiles, __DIR__.'/../../../src/Resources/config/schema/core-1.0.xsd');
    }
}
