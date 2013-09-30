<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Form;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

use Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\Content;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Document\Generic;

/**
 * Fixtures class for test data
 */
class LoadRouteData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $root = $manager->find(null, '/');

        $test = new Generic;
        $test->setNodename('test');
        $test->setParent($root);
        $manager->persist($test);

        $content = new Generic;
        $content->setNodename('content');
        $content->setParent($test);
        $manager->persist($content);

        $aContent = new Content();
        $aContent->id = '/test/content/a';
        $manager->persist($aContent);

        $bContent = new Content();
        $bContent->id = '/test/content/b';
        $manager->persist($bContent);

        $cms = new Generic;
        $cms->setNodename('cms');
        $cms->setParent($test);
        $manager->persist($cms);

        $routes = new Generic;
        $routes->setNodename('routes');
        $routes->setParent($cms);
        $manager->persist($routes);

        $aRoute = new Route();
        $aRoute->setName('a');
        $aRoute->setParent($routes);
        $aRoute->setContent($aContent);
        $manager->persist($aRoute);
        $bRoute = new Route();
        $bRoute->setName('b');
        $bRoute->setParent($routes);
        $bRoute->setContent($bContent);
        $manager->persist($bRoute);
        $manager->flush();
    }
}

class CheckboxUrlLabelFormTypeTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array('\Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Form\LoadRouteData'));
    }

    public function testFormTwigTemplate()
    {
        $this->getContainer()->get('twig')->initRuntime();
        $renderer = $this->getContainer()->get('twig')->getExtension('form')->renderer;

        $view = $this->getContainer()->get('form.factory')->createNamedBuilder('name', 'form')
            ->add('terms', 'cmf_core_checkbox_url_label', array(
                'label' => '%a% and %b% and %c%',
                'routes' => array(
                    '%a%' => array('parameters' => array('content_id' => '/test/content/a')),
                    '%b%' => array('parameters' => array('content_id' => '/test/content/b')),
                    '%c%' => array('name' => 'hello', 'parameters' => array('name' => 'world'), 'referenceType' => true),
                )
            ))
            ->getForm()
            ->createView();

        $template = $renderer->searchAndRenderBlock($view, 'widget', array());
        $this->assertMatchesXpath($template, '//label[@class="checkbox"][contains(.,"/a and /b and http://localhost/hello/world")]');
    }

    protected function assertMatchesXpath($html, $expression, $count = 1)
    {
        $dom = new \DomDocument('UTF-8');
        try {
            // Wrap in <root> node so we can load HTML with multiple tags at
            // the top level
            $dom->loadXml('<root>'.$html.'</root>');
        } catch (\Exception $e) {
            $this->fail(sprintf(
                "Failed loading HTML:\n\n%s\n\nError: %s",
                $html,
                $e->getMessage()
            ));
        }
        $xpath = new \DOMXPath($dom);
        $nodeList = $xpath->evaluate('/root'.$expression);

        if ($nodeList->length != $count) {
            $dom->formatOutput = true;
            $this->fail(sprintf(
                "Failed asserting that \n\n%s\n\nmatches exactly %s. Matches %s in \n\n%s",
                $expression,
                $count == 1 ? 'once' : $count.' times',
                $nodeList->length == 1 ? 'once' : $nodeList->length.' times',
                // strip away <root> and </root>
                substr($dom->saveHTML(), 6, -8)
            ));
        }
    }
}
