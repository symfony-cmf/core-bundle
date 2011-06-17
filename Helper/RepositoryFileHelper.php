<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Helper;

use Symfony\Cmf\Bundle\CoreBundle\Helper\DirectPathMapper;
use Symfony\Bundle\DoctrinePHPCRBundle\JackalopeLoader;
use PHPCR\NodeInterface;

/**
 * Helper class to get paths for and save files to filesystem from repository
 */
class RepositoryFileHelper implements FileMapperInterface
{
    /**
     * @var JackalopeLoader
     */
    protected $session;

    /**
     * @var string  (e.g. /var/www/foo/images)  : absolute path, no trailing slash
     */
    protected $fileBasePath;

    /**
     * @var string  (e.g. images_source)  : path relative to the web directory, no trailing slash
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
     * @return string with a path to the file, relative to the web directory.
     */
    public function getUrl(NodeInterface $node)
    {

        $hasData = false;
        if ($node->hasNode('jcr:content')) {
            $contentNode = $node->getNode('jcr:content');
            if ($contentNode->hasProperty('jcr:data')) {
                $hasData = true;
            }
        }
        if (!$hasData) {
            //TODO: notfound exception is not appropriate ... how to best do this?
            //throw new NotFoundHttpException('no picture found at ' . $node->getPath());
            return 'notfound';
        }

        $path = $node->getPath();
        $relativePath = $this->pathMapper->getUrl($path);
        $fullPath = $this->fileBasePath . $relativePath . $this->getExtension($contentNode);

        if (!file_exists($fullPath)) {
            try {
                $this->saveData($contentNode, $fullPath);
            } catch (Imagine\Exception\Exception $e) {
                //TODO: notfound exception is not appropriate ... how to best do this?
                //throw new NotFoundHttpException('image save to filesystem failed: ' . $e->getMessage());
                return 'notfound';
            }
        }

        return $this->webRelativePath . $relativePath;
    }

    protected function saveData($contentNode, $fileSystemPath)
    {
        $data = $contentNode->getProperty('jcr:data')->getString();
        $dirname = dirname($fileSystemPath);
        mkdir($dirname, 0755, true);
        file_put_contents($fileSystemPath, $data);
    }

    /* TODO: should this be provided as a service? If so, then probably the parent node of the content node should be passed in,
     *       to allow flexibility in implementation.
     *       Possible extension scenarios:
     *          * get extension from the content node's parent's path name
     *          * get extension from some property in the content node
     */
    protected function getExtension($contentNode)
    {
        if ($contentNode->hasProperty('jcr:mimeType')) {
            return $this->getExtensionFromMimeType($contentNode->getPropertyValue('jcr:mimeType'));
        }
        return '';
    }

    /**
     * A very incomplete list of mime types mapped to their most likely extensions.
     */
    protected function getExtensionFromMimeType($imageMimeType)
    {
        if(empty($imageMimetype)) return '';
        switch($imagetype)
        {
            case 'image/bmp': return '.bmp';
            case 'image/cis-cod': return '.cod';
            case 'image/gif': return '.gif';
            case 'image/ief': return '.ief';
            case 'image/jpeg': return '.jpg';
            case 'image/pipeg': return '.jfif';
            case 'image/tiff': return '.tif';
            case 'image/x-cmu-raster': return '.ras';
            case 'image/x-cmx': return '.cmx';
            case 'image/x-icon': return '.ico';
            case 'image/x-portable-anymap': return '.pnm';
            case 'image/x-portable-bitmap': return '.pbm';
            case 'image/x-portable-graymap': return '.pgm';
            case 'image/x-portable-pixmap': return '.ppm';
            case 'image/x-rgb': return '.rgb';
            case 'image/x-xbitmap': return '.xbm';
            case 'image/x-xpixmap': return '.xpm';
            case 'image/x-xwindowdump': return '.xwd';
            case 'image/png': return '.png';
            case 'image/x-jps': return '.jps';
            case 'image/x-freehand': return '.fh';
            default: return '';
        }
    }

}
