<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Twig;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class ServiceTest extends BaseTestCase
{
    public function testContainer()
    {
        $twig = $this->getContainer()->get('twig');
        $ext = $twig->getExtension('cmf');
        $this->assertNotEmpty($ext);
    }
}
