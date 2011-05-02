<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * Base class for functional tests in the cmf that base on PHPCR
 *
 * Works only for Jackalope with phpcr atm. Should be refactored
 * to use Session->import once this is implemented
 *
 * Connection information is read from jackalope.options... parameters
 *
 * Fixtures are loaded from PHPCR XML dumps.
 * To acquire some fixtures, create the necessary content in your repository, 
 * then locate jack.jar inside phpcr-odm to dump the repository. You should
 * specify the base xpath to the name of the root node of your content. 
 * Without that, you will dump all kinds of basic definitions and get a file 
 * of 40+ M.
 *
 * java -jar /path/to/jack.jar exportsystem dump.xml repository-base-xpath=/cms
 *
 * The document view is more readable than the system view. However, types
 * are not stored and you can get problems, i.e. with node references.
 *
 * java -jar /path/to/jack.jar exportdocument dump.xml repository-base-xpath=/cms
 *
 * @author David Buchmann <david@liip.ch>
 */
class CmfTestCase extends BaseWebTestCase
{
    protected $importexport;

    /**
     * setup this test
     *
     * @param string $fixturesPath the path to the directory with the fixture xmls for testing. defaults to __DIR__/../Fixtures/
     */
    public function __construct($fixturesPath = null)
    {
        parent::__construct();

        if (is_null($fixturesPath)) {
            $fixturesPath = __DIR__.'/../Fixtures/';
        }
        $this->importexport = new \jackrabbit_importexport($fixturesPath);
    }

    /**
     * load a fixture (system view or document view) into the repository, overwriting its current content
     *
     * @param string $filename the path to the xml that should be loaded.
     */
    public function loadFixture($filename)
    {
        //TODO: improve importexport to have an other way to pass options
        $container = $this->getContainer();
        $GLOBALS['jcr.url'] = $container->getParameter('jackalope.options.url');
        $GLOBALS['jcr.workspace'] = $container->getParameter('jackalope.options.workspace');
        $GLOBALS['jcr.user'] = $container->getParameter('jackalope.options.user');
        $GLOBALS['jcr.pass'] = $container->getParameter('jackalope.options.pass');

        $this->importexport->import($filename);
    }

    /**
     * check if jackrabbit is running at the address configured for doctrine-phpcr
     *
     * if not, marks the test as skipped
     * if there is a reply but one that does not look like jackrabbit, test fails
     */
    public function assertJackrabbitRunning()
    {
        static $available;
        static $url;
        if (null === $available) {
            $container = $this->getContainer();
            $url = $container->getParameter('jackalope.options.url');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);

            curl_close($ch);

            $available = (Boolean) $res;
            if ($available) {
                $this->assertContains('Available Workspace Resources', $res, "This seems to be not jackrabbit but something else at $url");
            }
        }

        if (! $available) {
            $this->markTestSkipped("Jackrabbit is not listening at $url");
        }

    }
}
