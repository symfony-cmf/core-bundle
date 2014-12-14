<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;

/**
 * A copy of the decorator pass available in Symfony 2.5, to support 2.3 and 2.4.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Wouter J <wouter@wouterj.nl>
 */
class DecoratorCompatibilityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getTaggedServiceIds('cmf_core.decorator') as $id => $tags) {
            $inner = $tags[0]['decorates'];

            if (isset($tags[0]['decoration_inner_name'])) {
                $renamedId = $tags[0]['decoration_inner_name'];
            } elseif (isset($tags[0]['decoration-inner-name'])) {
                $renamedId = $tags[0]['decoration-inner-name'];
            } else {
                $renamedId = $id.'.inner';
            }

            // we create a new alias/service for the service we are replacing
            // to be able to reference it in the new one
            if ($container->hasAlias($inner)) {
                $alias = $container->getAlias($inner);
                $public = $alias->isPublic();
                $container->setAlias($renamedId, new Alias((string) $alias, false));
            } else {
                $definition = $container->getDefinition($inner);
                $public = $definition->isPublic();
                $definition->setPublic(false);
                $container->setDefinition($renamedId, $definition);
            }

            $container->setAlias($inner, new Alias($id, $public));
        }
    }
}
