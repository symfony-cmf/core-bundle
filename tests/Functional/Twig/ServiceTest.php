<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Twig;

use Symfony\Cmf\Bundle\CoreBundle\Twig\Extension\CmfExtension;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class ServiceTest extends BaseTestCase
{
    public function testContainer()
    {
        /** @var \Twig\Environment $twig */
        $twig = $this->getContainer()->get('test.service_container')->get('twig');
        $ext = $twig->getExtension(method_exists($twig, 'getRuntime') ? CmfExtension::class : 'cmf');
        $this->assertNotEmpty($ext);
    }
}
