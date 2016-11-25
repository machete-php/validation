<?php

namespace League\JsonGuard\Test\RuleSets;

use League\JsonGuard\Exceptions\ConstraintNotFoundException;
use League\JsonGuard\RuleSets\DraftFour;

class DraftFourRuleSetTest extends \PHPUnit_Framework_TestCase
{
    public function testHas()
    {
        $ruleSet = new DraftFour();
        $this->assertTrue($ruleSet->has('allOf'));
        $this->assertFalse($ruleSet->has('nonExistent'));
    }

    public function testGetConstraintWhenConstraintDoesNotExist()
    {
        $this->setExpectedException(ConstraintNotFoundException::class);
        $ruleSet = new DraftFour();
        $ruleSet->getConstraint('nonExistent');
    }
}
