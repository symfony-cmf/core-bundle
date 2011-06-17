<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Helper;

use Symfony\Cmf\Bundle\CoreBundle\Helper\DirectPathMapper;
use Symfony\Bundle\DoctrinePHPCRBundle\JackalopeLoader;

/**
 * Helper class to get paths for and save files to filesystem from repository
 */
class RepositoryFileHelper
{
    /**
     * @var JackalopeLoader
     */
    protected $session;

    /**
     * @var string Content repository path prefix (e.g. /cms/content)
     */
    protected $pathPrefix;

    /**
     * @var string  (e.g. /var/www/foo/images)  : no trailing slash
     */
    protected $fileBasePath;

    /**
     * @var string  (e.g. images_source)  : no trailing slash
     */
    protected $webRelativePath;

    /**
     * @var Symfony\Cmf\Bundle\CoreBundle\Helper\DirectPathMapper
     */
    protected $pathMapper;


    /**
     * @param JackalopeLoader $loader
     * @param string $pathPrefix Content repository path prefix (e.g. /cms/content)
     * @param string $fileBasePath
     * @param string $webRelativePath a path relative to the web directory
     */
    public function __construct(JackalopeLoader $loader, $pathPrefix, $fileBasePath, $webRelativePath)
    {
        $this->session = $loader->getSession();
        $this->fileBasePath = $fileBasePath;
        $this->pathMapper = new DirectPathMapper($pathPrefix);
        $this->webRelativePath = '/' . $webRelativePath;
    }

    /**
     * Gets a relative filesystem path based on the repository path, AND
     * creates the file on the filesystem if it's in the repository
     * and not yet on the filesystem.
     * The repository path points to a nt-resource node, whose title
     * should be the filename, and which has a child+property
     * jcr:content/jcr:data where the file data is stored.
     *
     * @param string $path path to the nt-resource node.
     * @return string with a path to the file, relative to the web directory
     */
    public function getFilePath($path)
    {
        $repositoryPath = $path . '/jcr:content/jcr:data';
        if (!$this->session->propertyExists($repositoryPath)) {
            //TODO: notfound exception is not appropriate ... how to best do this?
            //throw new NotFoundHttpException('no picture found at ' . $repositoryPath);
            return 'notfound';
        }

        $relativePath = $this->pathMapper->getUrl($path);
        $fullPath = $this->fileBasePath . $relativePath;
        if (!file_exists($fullPath)) {
            try {
                $this->saveData($repositoryPath, $fullPath);
            } catch (Imagine\Exception\Exception $e) {
                //TODO: notfound exception is not appropriate ... how to best do this?
                //throw new NotFoundHttpException('image save to filesystem failed: ' . $e->getMessage());
                return 'notfound';
            }
        }

        return $this->webRelativePath . $relativePath;
    }

    protected function saveData($repositoryPath, $fileSystemPath)
    {
        $data = $this->session->getProperty($repositoryPath)->getString();
        $dirname = dirname($fileSystemPath);
        mkdir($dirname, 0755, true);
        file_put_contents($fileSystemPath, $data);
    }

}
