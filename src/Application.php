<?php
namespace FantasyStudio\WeChat;

use FantasyStudio\WeChat\Foundation\Foundation;

class Application
{
    use Foundation;

    public $boot = [
        "WCard" => "\FantasyStudio\WeChat\Card\Card",
        "OAuth" => "\FantasyStudio\WeChat\Auth\Auth",
        "Js" => "\FantasyStudio\WeChat\Js\Js",
        "MemberCard" => "\FantasyStudio\WeChat\Card\MemberCard",
    ];

    public $config;
    public $cache;

    public function __construct(array $config, $obj="")
    {

        foreach ($this->boot as $name => $origin_name) {
            class_alias($origin_name, $name);
        }

        if (!array_key_exists("cache_driver", $config)) {
            $this->setCacheDriver("file");
        }else{
            $this->setCacheDriver($config["cache_driver"], $obj);
        }

        $this->config = $config;
    }

    public function __call($name, $arguments = "")
    {
        if (!class_exists($name)) {
            throw new \InvalidArgumentException("class {$name} not found");
        }

        $class_name = ucwords($name);

        return new $class_name($this->config, $this->cache_driver);
    }
}