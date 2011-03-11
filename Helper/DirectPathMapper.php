<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Helper;

/**
 * The direct mapper just exposes the paths within phpcr, minus a base path
 * that is used to organize the tree in phpcr.
 *
 * @author David Buchmann <david@liip.ch>
 */
class DirectPathMapper implements PathMapperInterface
{
    /**
     * @var string
     * path to the root of the navigation tree this mapper has to map
     */
    protected $basepath;

    /**
     * @var int
     * length of base path, to cut phpcr path to url
     */
    protected $basepathlen;

    /**
     * @param string $basepath phpcr path to the root of the navigation tree
     */
    public function __construct($basepath)
    {
        $this->basepath = $basepath;
        $this->basepathlen = strlen($basepath);
    }

    /**
     * map the web url to the id used to retrieve content from storage
     *
     * @param string $url the request url (without the prefix that might be used to get into this menu context)
     * @return mixed storage identifier (i.e. absolute node path within phpcr)
     */
    public function getStorageId($url)
    {
        return $this->basepath . $url;
    }

    /**
     * map the storage id to a web url
     * i.e. translate path to node in phpcr into url for that page
     *
     * @param mixed $storageId id of the storage backend (i.e. path to node in phpcr)
     * @return string $url the url (without the prefix that might be used to get into this menu context)
     */
    public function getUrl($storageId)
    {
        $path = substr($storageId, $this->basepathlen);
        if ($path == '') {
            //we are at root, but path is without trailing slash, so it would be empty
            $path = '/';
        }
    }
}
