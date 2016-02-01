<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Slugifier;

use Symfony\Cmf\Bundle\CoreBundle\Slugifier\CallbackSlugifier;

/**
 * @group legacy
 */
class CallbackSlugifierTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->slugifier = new CallbackSlugifier(
            __CLASS__.'::slugify'
        );
    }

    public function testSlugify()
    {
        $res = $this->slugifier->slugify('this is slugified');
        $this->assertEquals('this-is-slugified', $res);
    }

    public static function slugify($val)
    {
        return str_replace(' ', '-', $val);
    }
}
