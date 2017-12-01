<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Form;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class CheckboxUrlLabelFormTypeTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array('\Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\DataFixture\LoadRouteData'));
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
                    '%c%' => array('name' => 'hello', 'parameters' => array('name' => 'world'), 'referenceType' => UrlGeneratorInterface::ABSOLUTE_URL),
                ),
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
                1 == $count ? 'once' : $count.' times',
                1 == $nodeList->length ? 'once' : $nodeList->length.' times',
                // strip away <root> and </root>
                substr($dom->saveHTML(), 6, -8)
            ));
        }
    }
}
