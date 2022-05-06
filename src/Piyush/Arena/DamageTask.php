<?php

namespace Piyush\Arena;


use pocketmine\scheduler\Task;

class DamageTask extends Task
{
public Arena $plugin;
    public array $player = [];

    public function __construct(Arena $plugin, $player)
{
    $this->plugin = $plugin;
    $this->player[] = $player;
}

    /**
     * @inheritDoc
     */
    public function onRun(): void
    {
        foreach ($this->player as $player)
       if ((microtime(true) - $this->plugin->preventfalldamage[$player]) >= 2){
        unset($this->plugin->preventfalldamage[$player]);
    }
    }
}