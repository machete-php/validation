<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Validator;

class AdditionalPropertiesTest extends \PHPUnit_Framework_TestCase
{
    function test_it_returns_property_name_in_message()
    {
        $data = ['prop-name' => 'Property value in test'];
        $data = json_decode(json_encode($data));

        $schema = ['additionalProperties' => false];
        $schema = json_decode(json_encode($schema));

        $validator = new Validator($data, $schema);

        $this->assertTrue($validator->fails());

        $validationErrors = $validator->errors();

        $this->assertCount(1, $validationErrors);
        $this->assertContains('prop-name', $validationErrors[0]->getMessage());

    }
}
