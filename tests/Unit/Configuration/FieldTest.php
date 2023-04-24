<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Configuration;

use App\Features\Shared\Configuration\Field;
use App\Features\Shared\Configuration\Option;
use Tests\AbstractUnitTest;

class FieldTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function it_should_provide_currency(): void
    {
        $currency = 'USD';
        $field = new Field();
        $field->setCurrency($currency);

        $this->assertEquals($currency, $field->getCurrency());
    }

    /**
     * @test
     */
    public function it_should_provide_id(): void
    {
        $field = new Field();
        $id = 'someId';
        $field->setId($id);

        $this->assertEquals($id, $field->getId());
    }

    /**
     * @test
     */
    public function it_should_provide_label(): void
    {
        $field = new Field();
        $label = 'someLabel';
        $field->setLabel($label);

        $this->assertEquals($label, $field->getLabel());
    }

    /**
     * @test
     */
    public function it_should_provide_name(): void
    {
        $name = 'someName';
        $field = new Field();
        $field->setName($name);

        $this->assertEquals($name, $field->getName());
    }

    /**
     * @test
     */
    public function it_should_provide_options(): void
    {
        $option = $this->createMock(Option::class);
        $options = [$option];
        $field = new Field();
        $field->setOptions($options);

        $this->assertEquals($options, $field->getOptions());
    }

    /**
     * @test
     */
    public function it_should_provide_required(): void
    {
        $field = new Field();

        $field->setRequired(true);
        $this->assertEquals(true, $field->isRequired());

        $field->setRequired(false);
        $this->assertEquals(false, $field->isRequired());
    }

    /**
     * @test
     */
    public function it_should_provide_type(): void
    {
        $type = 'someType';
        $field = new Field();
        $field->setType($type);

        $this->assertEquals($type, $field->getType());
    }
}
