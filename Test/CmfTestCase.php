<?php

namespace Symfony\CMF\Bundle\CoreBundle\Test;


/*
 * TODO: this is ugly.
 *
 * best would be to implement Session->import in Jackalope
 * otherwise at least make importexport autoloader compatible?
 */
require_once(__DIR__ . '/../../../../../../vendor/phpcr-odm/lib/vendor/jackalope/api-test/suite/inc/importexport.php');

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * Base class for functional tests in the cmf that base on PHPCR
 *
 * Works only for Jackalope with phpcr atm. Should be refactored
 * to use Session->import once this is implemented
 *
 * Connection information is read from jackalope.options... parameters
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
        $this->importexport = new \jackalope_importexport($fixturesPath);

        $_SERVER['KERNEL_DIR'] = __DIR__.'/../../../../../../app'; //TODO: improve this. seems to be needed by liiptestbundle
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
