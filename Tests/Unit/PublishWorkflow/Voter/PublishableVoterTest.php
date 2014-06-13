<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow\Voter;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter\PublishableVoter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PublishableVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublishableVoter
     */
    private $voter;

    /**
     * @var TokenInterface
     */
    private $token;

    public function setUp()
    {
        $this->voter = new PublishableVoter();
        $this->token = new AnonymousToken('', '');
    }

    public function providePublishWorkflowChecker()
    {
        return array(
            array(
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
            ),
            array(
                'expected' => VoterInterface::ACCESS_DENIED,
                'isPublishable' => false,
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
            ),
            array(
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => array(
                    PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE,
                    PublishWorkflowChecker::VIEW_ATTRIBUTE,
                ),
            ),
            array(
                'expected' => VoterInterface::ACCESS_DENIED,
                'isPublishable' => false,
                'attributes' => PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE,
            ),
            array(
                'expected' => VoterInterface::ACCESS_ABSTAIN,
                'isPublishable' => true,
                'attributes' => 'other',
            ),
            array(
                'expected' => VoterInterface::ACCESS_ABSTAIN,
                'isPublishable' => true,
                'attributes' => array(PublishWorkflowChecker::VIEW_ATTRIBUTE, 'other'),
            ),
        );
    }

    /**
     * @dataProvider providePublishWorkflowChecker
     *
     * use for voters!
     */
    public function testPublishWorkflowChecker($expected, $isPublishable, $attributes)
    {
        $attributes = (array) $attributes;
        $doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface');
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue($isPublishable))
        ;

        $this->assertEquals($expected, $this->voter->vote($this->token, $doc, $attributes));
    }

    public function testUnsupportedClass()
    {
        $result = $this->voter->vote(
            $this->token,
            $this,
            array(PublishWorkflowChecker::VIEW_ATTRIBUTE)
        );
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
