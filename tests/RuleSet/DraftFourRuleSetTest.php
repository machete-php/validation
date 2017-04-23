<?php

namespace League\JsonGuard\Test\RuleSet;

use League\JsonGuard\Exception\ConstraintNotFoundException;
use League\JsonGuard\RuleSet\DraftFour;

class DraftFourRuleSetTest extends \PHPUnit_Framework_TestCase
{
    function test_has()
    {
        $ruleSet = new DraftFour();
        $this->assertTrue($ruleSet->has('allOf'));
        $this->assertFalse($ruleSet->has('nonExistent'));
    }

    function test_get_constraint_when_constraint_does_not_exist_throws()
    {
        $this->setExpectedException(ConstraintNotFoundException::class);
        $ruleSet = new DraftFour();
        $ruleSet->get('nonExistent');
    }
}
