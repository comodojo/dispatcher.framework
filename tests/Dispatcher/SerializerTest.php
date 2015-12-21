<?php

use Comodojo\Dispatcher\Serialization;
use Comodojo\Dispatcher\Deserialization;

class SerializerTest extends PHPUnit_Framework_TestCase {

    protected $array = array("this"=>"is","a"=>"test");

    public function setUp() {

        $this->serializer = new Serialization();

        $this->deserializer = new Deserialization();

    }

    public function testJson() {

        $serialized_array = $this->serializer->toJson($this->array);

        $deserialized_array = $this->deserializer->fromJson($serialized_array);

        $this->assertEquals($this->array, $deserialized_array);

    }

    public function testXml() {

        $serialized_array = $this->serializer->toXml($this->array);

        $deserialized_array = $this->deserializer->fromXml($serialized_array);

        $this->assertEquals($this->array, $deserialized_array['content']);

    }

    public function testYaml() {

        $serialized_array = $this->serializer->toYaml($this->array);

        $deserialized_array = $this->deserializer->fromYaml($serialized_array);

        $this->assertEquals($this->array, $deserialized_array);

    }

    public function testExport() {

        $serialized_array = $this->serializer->toExport($this->array);

        $deserialized_array = $this->deserializer->fromExport($serialized_array);

        $this->assertEquals($this->array, $deserialized_array);

    }



}
