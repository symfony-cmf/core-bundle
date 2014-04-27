<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Slugifier;

/**
 * Slugifier service which uses a callback
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CallbackSlugifier implements SlugifierInterface
{
    protected $callback;

    /**
     * @see http://php.net/manual/en/language.types.callable.php
     *
     * @param mixed $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {inheritDoc}
     */
    public function slugify($string)
    {
        return call_user_func($this->callback, $string);
    }
}
