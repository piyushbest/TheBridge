<?php

namespace Piyush;


use JsonException;
use pocketmine\player\Player;

class NPC{


    public NPChuman $human;
    public Main $plugin;
    public array $world;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }


    /**
     * @throws JsonException
     */
    public function npcmaker(Player $player): void
    {
        $human = new NPChuman($player->getLocation(), $player->getSkin());
        $human->setNameTagVisible(true);
        $human->setNameTagAlwaysVisible(true);
        $this->human = $human;
        $this->world["NPCWorld"] = $human->getWorld()->getFolderName();
        $this->world["NPCId"] = $human->getId();
        $this->plugin->getConfig()->setAll($this->world);
        $this->plugin->getConfig()->save();
        $human->spawnToAll();
        $player->getWorld()->save(true);
    }
}
