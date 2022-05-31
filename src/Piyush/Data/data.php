<?php

namespace Piyush\Data;

use Piyush\Arena\Arena;
use Piyush\Main;
use pocketmine\utils\Config;
use pocketmine\world\World;

class data {
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;
        $this->init();
        $this->loadArenas();
    }


    public function loadArenas() {
        foreach (glob($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . "*.yml") as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            $this->main->arena[basename($arenaFile, ".yml")] = new Arena($this->main, $config->getAll(\false));
        }
    }

    public function init() {
    if(!is_dir($this->getDataFolder())) {
        @mkdir($this->getDataFolder());
    }
    if(!is_dir($this->getDataFolder() . "arenas")) {
        @mkdir($this->getDataFolder() . "arenas");
    }
    if(!is_dir($this->getDataFolder() . "saves")) {
        @mkdir($this->getDataFolder() . "saves");
    }
}

    /**
     * @throws \JsonException
     */
    public function saveArenas() {
        foreach ($this->main->arena as $fileName => $arena) {
            if($arena->world instanceof World) {
                foreach ($arena->players as $player) {
                    $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                }
            }
            $config = new Config($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $fileName . ".yml", Config::YAML);
            $config->setAll($arena->data);
            $config->save();
        }
    }
    private function getDataFolder(): string {
        return $this->main->getDataFolder();
    }

}



