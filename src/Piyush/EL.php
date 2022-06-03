<?php

namespace Piyush;

use jackmd\scorefactory\ScoreFactoryException;
use Piyush\Arena\Arena;
use Piyush\Arena\Vector3;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class EL implements Listener
{
    /** @var Main $plugin */
    public Main $plugin;
    /** @var Arena[]|Arena[][] $setters */
    public array $setters = [];
    /** @var int[] $setupData */
    private array $setupData = [];
    public Main $main;

    public function __construct(Main $main)
    {
        $this->main = $main;
        $this->plugin = $main;
    }

    public function onPlace(BlockPlaceEvent $e)
    {
        $p = $e->getPlayer();
        $block = $e->getBlock();
        foreach ($this->plugin->arena as $arena) {
if($arena->onGame($p)) {

    $data1 = $arena->data["spawnRed"];
    $x1 = $data1["2"];
    $y1 = $data1["3"];
    $z1 = $data1["4"];
    $minX = (int)min($x1 - 1, $x1 + 1);
    $maxX = (int)max($x1 - 1, $x1 + 1);
    $minZ = (int)min($z1 - 1, $z1 + 1);
    $maxZ = (int)max($z1 - 1, $z1 + 1);
    $y2 = (int)min($y1 - 3, $y1 + 2);
    $y3 = (int)max($y1 + 2, $y1 - 3);
    for ($x2 = $minX; $x2 <= $maxX; ++$x2) {
        for ($z2 = $minZ; $z2 <= $maxZ; ++$z2) {
            if ($block->getPosition()->getX() == $x2 && $block->getPosition()->getY() == $y1 >= $y2 && $block->getPosition()->getY() == $y1 <= $y3 && $block->getPosition()->getZ() == $z2) {
                $e->cancel();
            }
        }
    }
    $data2 = $arena->data["spawnBlue"];
    $x = $data2["2"];
    $y = $data2["3"];
    $z = $data2["4"];
    $minX = (int)min($x - 1, $x + 1);
    $maxX = (int)max($x - 1, $x + 1);
    $minZ = (int)min($z - 1, $z + 1);
    $maxZ = (int)max($z - 1, $z + 1);
    $y4 = (int)min($y - 3, $y + 2);
    $y5 = (int)max($y + 2, $y - 3);
    for ($x2 = $minX; $x2 <= $maxX; ++$x2) {
        for ($z2 = $minZ; $z2 <= $maxZ; ++$z2) {
            if ($block->getPosition()->getX() == $x2 && $block->getPosition()->getY() == $y1 >= $y4 && $block->getPosition()->getY() == $y1 <= $y5 && $block->getPosition()->getZ() == $z2) {
                $e->cancel();
            }
        }
    }
    $block2 = VanillaBlocks::RED_GLAZED_TERRACOTTA();
    $y9 = $block2->getPosition()->getY();
    if ($block->getPosition()->getY() == $y9 && $block->getPosition()->getY() == $y9 <= 10){
        $e->cancel();
    }
    $block3 = VanillaBlocks::BLUE_GLAZED_TERRACOTTA();
    $y10 = $block3->getPosition()->getY();
    if ($block->getPosition()->getY() == $y10 && $block->getPosition()->getY() == $y10 <= 10){
        $e->cancel();
    }
}
}
        }

    public function onBreak(BlockBreakEvent $event){
       $player = $event->getPlayer();
       $block = $event->getBlock();
        foreach ($this->main->arena as $arena){
             if ($arena->onGame($player)){
                 if ($block->getId() != 35){
                     $event->cancel();
                 }
             }
        }
    }

    public function onPickUp(EntityItemPickupEvent $event){
        $player = $event->getEntity();
        $item = $event->getItem();
        foreach ($this->main->arena as $arena) {
            if ($arena->onGame($player) ) {
                if ($item->getId() != 35){
                    $event->cancel();
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event){
        $entity= $event->getEntity();
        $ran = $this->plugin->emptyArenaChooser->getRandomArena();
        if ($event->getEntity() instanceof NPChuman && $event instanceof EntityDamageByEntityEvent){
            $event->cancel();
            $damager = $event->getDamager();
            if ($damager instanceof Player) {
                if (!empty($ran->data["world"])) {
                    $ran->joinGame($event->getDamager());
                } else {
                    $damager->sendMessage(TextFormat::RED . "THEBRIDGE >".TextFormat::AQUA . "Arenas Not Available");
                }
            }
        }
        $arena = null;
        foreach ($this->plugin->arena as $arena) {
            if ($event->getCause() === $event::CAUSE_VOID) {
                $event->setBaseDamage(20.0); // hack: easy check for last damage
            }

            if (!$entity instanceof Player) return;

            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();

                if ($damager instanceof Player) {
                    $arena->lastDamage[$entity->getName()] = [microtime(true), $damager->getName()];
                }
            }

            if ($event instanceof EntityDamageByEntityEvent) {
                if ($event->getEntity() instanceof Player) {
                    $damager = $event->getDamager();
                    if ($damager instanceof Player) {
                        if (in_array($entity->getName(), $arena->reds)) {
                            if (in_array($damager->getName(), $arena->reds)) {
                                $event->cancel();
                            }
                        }
                        if (in_array($entity->getName(), $arena->blues)) {
                            if (in_array($damager->getName(), $arena->blues)) {
                                $event->cancel();
                            }
                        }
                    }
                }
            }
            if (!$arena->onGame($entity)) {
                return;
            }


            if ($arena->phase == 0) {
                $event->cancel();
                if ($event->getCause() === $event::CAUSE_VOID) {
                    if (isset($arena->data["lobby"]) && $arena->data["lobby"] != null) {
                        $entity->teleport(Position::fromObject(Vector3::fromString($arena->data["lobby"][0]), $this->plugin->getServer()->getWorldManager()->getWorldByName($arena->data["lobby"][1])));
                    }
                }
            }
            if (isset($arena->preventfalldamage[$entity->getName()])) {
                if ((microtime(true) - $arena->preventfalldamage[$entity->getName()]) <= 2) {
                    $event->cancel();
                }
                if ((microtime(true) - $arena->preventfalldamage[$entity->getName()]) > 2) {
                    unset($arena->preventfalldamage[$entity->getName()]);
                }
            }
                if ($arena->phase == 1 && $event->getCause() == EntityDamageEvent::CAUSE_FALL && ($arena->scheduler->crt > -3)) {
                    $event->cancel();
                }
            if ($event->getCause() === $event::CAUSE_VOID) {
                $event->setBaseDamage(20.0);
            }

            if ($entity->getHealth() - $event->getFinalDamage() <= 0 ) {
                $event->cancel();
                $arena->deaths[$entity->getName()]++;
                switch ($event->getCause()) {
                    case $event::CAUSE_CONTACT:
                    case $event::CAUSE_ENTITY_ATTACK:
                        if ($event instanceof EntityDamageByEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                $arena->kills[$damager->getName()]++;
                                $arena->broadcastMessage($arena->getPrefix() . "{$entity->getName()} has been killed by {$damager->getName()}");
                                if (in_array($entity->getName(), $arena->reds)){
                                    $arena->tpRed($entity);
                                }
                                if (in_array($entity->getName(), $arena->blues)){
                                    $arena->tpBlue($entity);
                                }
                                break;
                            }
                        }
                        break;
                    case $event::CAUSE_FALL:

                        $arena->broadcastMessage($arena->getPrefix() . "{$entity->getName()} killed by fall damage");
                        if (in_array($entity->getName(), $arena->reds)){
                            $arena->tpRed($entity);
                        }
                        if (in_array($entity->getName(), $arena->blues)){
                            $arena->tpBlue($entity);
                        }
                        break;
                    case $event::CAUSE_VOID:

                        $lastDmg = $arena->lastDamage[$entity->getName()] ?? null;
                        if ($lastDmg !== null) {
                            $damager = $arena->players[$lastDmg[1]] ?? null;
                            if ($damager instanceof Player && microtime(true) - $lastDmg[0] < 5) {
                                $arena->broadcastMessage($arena->getPrefix(). "{$entity->getName()} pushed to the void by {$damager->getName()}");
                                $arena->kills[$damager->getName()]++;
                                if (in_array($entity->getName(), $arena->reds)){
                                    $arena->tpRed($entity);
                                }
                                if (in_array($entity->getName(), $arena->blues)){
                                    $arena->tpBlue($entity);
                                }
                                break;
                            }
                        }
                        if (in_array($entity->getName(), $arena->reds)){
                            $arena->tpRed($entity);
                        }
                        if (in_array($entity->getName(), $arena->blues)){
                            $arena->tpBlue($entity);
                        }
                        $arena->broadcastMessage($arena->getPrefix() . "{$entity->getName()} killed by void");
                        break;
                }
            }
        }
    }

    /**
     * @throws ScoreFactoryException
     */
    public function onLeave(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $arenas = $this->main->arena;
        foreach ($arenas as $arena)
        if ($arena->onGame($player)){
            $arena->leaveGame($player);
        }
    }

    /**
     * @throws ScoreFactoryException
     */
    public function onLevelChange(EntityTeleportEvent $event){
        $e = $event->getEntity();
        $arenas = $this->main->arena;
        foreach ($arenas as $arena)
            if ($e instanceof Player && $arena->onGame($e) && $arena->phase == 1)
                 {
                $a = $event->getFrom()->getWorld();
                $b = $event->getTo()->getWorld();
                if ($b->getFolderName() != $arena->world->getFolderName()){
                    $arena->leaveGame($e);
                    $e->sendMessage(TextFormat::RED . "THEBRIDGE >". TextFormat::AQUA."You Left The Game");
                }
            }
        }
//VixikHD is Author of this code
    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        if(!isset($this->setters[$player->getName()])) {
            return;
        }

        $event->cancel();
        $args = explode(" ", $event->getMessage());

        /** @var Arena|Arena[] $arena */
        $arena = $this->setters[$player->getName()];
        /** @var Arena[] $arenas */
        $arenas = is_array($this->setters[$player->getName()]) ? $this->setters[$player->getName()] : [$this->setters[$player->getName()]];

        switch ($args[0]) {
            case "help":
                if(!isset($args[1]) || $args[1] == "1") {
                    $player->sendMessage(
                        "level : Set arena level\n".
                        "spawn : Set arena spawns\n".
                        "dcpos : Sets position to leave arena \n"."enable : Enable the arena \n" . "lobby : Sets arena lobby\n");
                }
                break;
            case "level":
                if(is_array($arena)) {
                    $player->sendMessage("Level must be different for each arena.");
                    break;
                }
                if(!isset($args[1])) {
                    $player->sendMessage("Usage: §7level <levelName>");
                    break;
                }
                if(!$this->main->getServer()->getWorldManager()->isWorldGenerated($args[1])) {
                    $player->sendMessage("Level $args[1] does not found!");
                    break;
                }
                $player->sendMessage("Arena level updated to $args[1]!");

                foreach ($arenas as $arena)
                    $arena->data["world"] = $args[1];
$this->plugin->getServer()->getWorldManager()->getWorldByName($args[1])->setAutoSave(false);
                break;
            case "spawn":
                if(is_array($arena)) {
                    $player->sendMessage("§c> Spawns are different for each arena.");
                    break;
                }

                if(!isset($args[1])) {
                    $player->sendMessage("Use : spawn red or spawn blue");
                    break;
                }

                if(($args[1]) == "red") {
                    foreach ($arenas as $arena) {
                        $arena->data["spawnRed"] = [(new Vector3((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY(), (int)$player->getPosition()->getZ()))->__toString(), $player->getWorld()->getFolderName(), $player->getPosition()->getFloorX(),$player->getPosition()->getFloorY(), $player->getPosition()->getFloorZ()];
                    }
                    $player->sendMessage("Red Spawn Set on " . (int)$player->getPosition()->getX() ." " . (int)$player->getPosition()->getY() . " " . (int)$player->getPosition()->getZ());
                }

                if(($args[1]) == "blue") {
                    foreach ($arenas as $arena) {
                        $arena->data["spawnBlue"] = [(new Vector3((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY(), (int)$player->getPosition()->getZ()))->__toString(), $player->getWorld()->getFolderName(), $player->getPosition()->getFloorX(),$player->getPosition()->getFloorY(), $player->getPosition()->getFloorZ()];
                    }
                    $player->sendMessage("Blue Spawn Set on " . (int)$player->getPosition()->getX() ." " . (int)$player->getPosition()->getY() . " " . (int)$player->getPosition()->getZ());
                }
                break;
            case "dcpos":
                foreach ($arenas as $arena) {
                    $arena->data["dcpos"] = [(new Vector3((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY(), (int)$player->getPosition()->getZ()))->__toString(), $player->getWorld()->getFolderName()];
                }

                $player->sendMessage("§a> Leave position updated.");
                break;
            case "enable":
                if(is_array($arena)) {
                    $player->sendMessage("You cannot enable arena in mode multi-setup mode.");
                    break;
                }

                if(!$arena->setup) {
                    $player->sendMessage("Arena is already enabled!");
                    break;
                }

                if(!$arena->enable()) {
                    $player->sendMessage(" You Have To Set All To Enable");
                    break;
                }

                foreach ($arenas as $arena){
                   $arena->reset->backupMap($arena->data["world"], $this->main->getDataFolder());
                }

                $player->sendMessage("§a> Arena enabled!");
                break;
            case "done":
                $player->sendMessage("§a> You have successfully left setup mode!");
                unset($this->setters[$player->getName()]);
                if(isset($this->setupData[$player->getName()])) {
                    unset($this->setupData[$player->getName()]);
                }
                break;
            case "savelevel":
                foreach ($arenas as $arena){
                    if ($arena->data["world"] == null) {
                       $player->sendMessage("Set Level First");
                        break;
                    }
$arena->reset->backupMap($arena->data["world"], $this->main->getDataFolder());
                        $player->sendMessage("Level Saved");
                }
                break;
            case "lobby":
                foreach ($arenas as $arena)
                    $arena->data["lobby"] = [(new Vector3((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY(), (int)$player->getPosition()->getZ()))->__toString(), $player->getWorld()->getFolderName()];
                $player->sendMessage("Game lobby updated!");
                break;
            default:
                $player->sendMessage("§6> You are in setup mode.\n".
                    "type help to display available commands\n"  .
                    "type done to leave setup mode");
                break;
        }
    }
}
