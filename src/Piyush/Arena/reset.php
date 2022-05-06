<?php

namespace Piyush\Arena;


use Piyush\Arena\Arena;
use Piyush\Main;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\world\World;

class reset{


    public $plugin;

    public function __construct(Arena $plugin) {
        $this->plugin = $plugin;
    }

    public function getServer()
    {
        return $this->plugin->plugin->getServer();
    }

    public function backupMap($world, $src){
       $this->getServer()->getWorldManager()->getWorldByName($world)->save(true);
    }
    public function resetMap($world){
        $this->unloadMap($world);
        $this->loadMap($world);
        return $this->getServer()->getWorldManager()->getWorldByName($world);
    }

    public function loadMap($world){
        if(!$this->getServer()->getWorldManager()->isWorldLoaded($world)){
            $this->getServer()->getWorldManager()->loadWorld($world);
            return true;
        }
        return false;
    }

    public function unloadMap($world){
        if($this->getServer()->getWorldManager()->isWorldLoaded($world)){
            $this->getServer()->getWorldManager()->unloadWorld($this->getServer()->getWorldManager()->getWorldByName($world));
            return true;
        }
        return false;
    }
}