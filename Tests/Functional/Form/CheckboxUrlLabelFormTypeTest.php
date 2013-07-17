<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Form;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

use Symfony\Cmf\Component\Routing\RouteAwareInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\Content;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Document\Generic;


class LoadRouteData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $root = $manager->find(null, '/');

        $content = new Generic;
        $content->setNodename('content');
        $content->setParent($root);
        $manager->persist($content);

        $aContent = new Content();
        $aContent->id = '/content/a';
        $manager->persist($aContent);

        $bContent = new Content();
        $bContent->id = '/content/b';
        $manager->persist($bContent);

        $cms = new Generic;
        $cms->setNodename('cms');
        $cms->setParent($root);
        $manager->persist($cms);

        $routes = new Generic;
        $routes->setNodename('routes');
        $routes->setParent($cms);
        $manager->persist($routes);

        $aRoute = new Route();
        $aRoute->setName('a');
        $aRoute->setParent($routes);
        $aRoute->setRouteContent($aContent);
        $manager->persist($aRoute);
        $bRoute = new Route();
        $bRoute->setName('b');
        $bRoute->setParent($routes);
        $bRoute->setRouteContent($bContent);
        $manager->persist($bRoute);
        $manager->flush();
    }
}

class CheckboxUrlLabelFormTypeTest extends BaseTestCase
{
    public function setUp()
    {
        $this->getDbManager('phpcr')->loadFixtures(array('\Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Form\LoadRouteData'));
    }

    public function testFormTwigTemplate()
    {
        $this->getContainer()->get('twig')->initRuntime();
        $renderer = $this->getContainer()->get('twig')->getExtension('form')->renderer;

        $view = $this->getContainer()->get('form.factory')->createNamedBuilder('name', 'form')
            ->add('terms', 'cmf_core_checkbox_url_label', array(
                'label' => '%a% and %b%',
                'content_ids' => array('%a%' => '/content/a', '%b%' => '/content/b')
            ))
            ->getForm()
            ->createView();

        $template = $renderer->renderBlock($view, 'form', array());
        $this->assertMatchesXpath($template, '//label[@class="checkbox"][contains(.,"/a and /b")]');
    }

    public function tearDown()
    {
        $this->getDbManager('phpcr')->purge();
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