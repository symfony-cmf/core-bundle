<?php

namespace Symfony\Cmf\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\RequestAwarePass;
use Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\AddPublishedVotersPass;

class CmfCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RequestAwarePass());
        $container->addCompilerPass(new AddPublishedVotersPass());
    }
}
