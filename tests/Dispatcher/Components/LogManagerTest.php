<?php namespace Comodojo\Dispatcher\Tests\Components;

use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Components\LogManager;

class LogManagerTest extends \PHPUnit_Framework_TestCase {

    protected static $local_config = array(
        "log" => array(
            "name" => "test",
            "providers" => array(
                "test" => array(
                    "type" => "StreamHandler",
                    "stream" => "log/log_manager_test.log",
                    "level" => "debug"
                )
            )
        )
    );

    public function testLocal() {

        $config = array_merge(self::$local_config, array(
            "base-path" => realpath(dirname(__FILE__)."/../../")
        ));

        $configuration = new Configuration( DefaultConfiguration::get() );

        $configuration->merge($config);

        $file = $configuration->get("base-path")."/log/log_manager_test.log";

        @unlink($file);

        $manager = LogManager::create($configuration);

        $manager->debug('this is a test');

        $this->assertFileExists($file);

        $content = file_get_contents($file);

        $this->assertStringEndsWith("test.DEBUG: this is a test [] []\n", $content);

    }

}
