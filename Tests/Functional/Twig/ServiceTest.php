<?php

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
