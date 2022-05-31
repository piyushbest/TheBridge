<?php

namespace Piyush;

use Piyush\Arena\Arena;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class commands extends Command{

    /** @var Main $main */
     Protected $main;

    public function __construct(Main $main)
    {
        $this->main = $main;
        parent::__construct("thebridge", "Commands For TheBridge", null, ["tb"]);
    }

    /**
     * @throws \JsonException
     * @throws \jackmd\scorefactory\ScoreFactoryException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender->hasPermission("tb.cmd")){
            $sender->sendMessage("You Dont Have Permission To Use This Command");
            return;
        } elseif (!isset($args[0])){
            $sender->sendMessage("use /tb help for information");
            return;
        }
        switch ($args[0]){
            case "help":

                $sender->sendMessage(TextFormat::RED . "TheBridge Help \n" . TextFormat::AQUA ."/tb set : setup an arena \n" .  TextFormat::AQUA ."/tb create : create an arena \n" .   TextFormat::AQUA . "/tb npc : spawn npc \n" .  TextFormat::AQUA ."/tb join {arenaName} \n" . TextFormat::AQUA . "/tb leave : to leave arena \n");
                break;
            case "create":
                if(!$sender->hasPermission("tb.create")) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage(" §7/tb create <arenaName>");
                    break;
                }
                if(isset($this->main->arena[$args[1]])) {
                    $sender->sendMessage( "Arena $args[1] already exists");
                    break;
                }
                $this->main->arena[$args[1]] = new Arena($this->main, []);
                $sender->sendMessage("Arena Created");
                break;
            case "npc":
                if(!$sender->hasPermission("tb.npc")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if(!$sender instanceof Player) {
                    $sender->sendMessage("§c> This command can be used only in-game!");
                    break;
                }
                    foreach ($sender->getWorld()->getEntities() as $en){
                    if($en instanceof NPChuman){
                        $en->flagForDespawn();
                    }
               }
(new NPC($this->main))->npcmaker($sender);//Dont know why i don't create entity on same file
$sender->sendMessage("§cCreated npc on your location");
                break;
            case "set":
                if(!$sender->hasPermission("tb.set")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if(!$sender instanceof Player) {
                    $sender->sendMessage("§c> This command can be used only in-game!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/swd set <arenaName|all> OR §7/swd set <arenaName1,arenaName2,...>");
                    break;
                }
                if(isset($this->main->eventListener->setters[$sender->getName()])) {
                    $sender->sendMessage("§c> You are already in setup mode!");
                    break;
                }

                if(!isset($this->main->arena[$args[1]])) {
                    $sender->sendMessage("Arena [$args[1]] not found.");
                    break;
                }

                $sender->sendMessage("§a> You've joined setup mode.\n".
                    "§7- use §lhelp §r§7to display available commands\n"  .
                    "§7- or §ldone §r§7to leave setup mode");

                $this->main->eventListener->setters[$sender->getName()] = $this->main->arena[$args[1]];
                break;
                case "leave";


                    $arena = null;
                    foreach ($this->main->arena as $arenas) {
                        if($arenas->onGame($sender)) {
                            $arena = $arenas;
                        }
                    }

                    if(is_null($arena)) {
                        $sender->sendMessage(TextFormat::RED ."THEBRIDGE >".TextFormat::AQUA. " Join The Game First");
                        break;
                    }

                    $arena->leaveGame($sender);
                break;
            case "join":
                if(!$sender->hasPermission("tb.join")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }

                if(!$sender instanceof Player) {
                    $sender->sendMessage("§c> This command can be used only in-game!");
                    break;
                }

                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /tb join <arenaName>");
                    break;
                }

                if(!isset($this->main->arena[$args[1]])) {
                    $sender->sendMessage("Arena [$args[1]] not found.");
                    break;
                }

                $this->main->arena[$args[1]]->joinGame($sender);
                break;
            case "start":
                if(!$sender->hasPermission("tb.start")){
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /tb start <arenaName>");
                    break;
                }$arena = null;
                    if(!isset($this->main->arena[$args[1]])) {
                        $sender->sendMessage("§c> Arena $args[1] was not found!");
                        break;
                   }
                    $arena = $this->main->arena[$args[1]];

                if($arena == null && $sender instanceof Player) {
                    foreach ($this->main->arena as $arenas) {
                        if($arenas->onGame($sender)) {
                            $arena = $arenas;
                        }
                    }
                }
                $sender->sendMessage(" Arena starts in 25 sec!");
                $arena->scheduler->forceStart = true;
                $arena->scheduler->startTime = 15;


                break;
            default:
                $sender->sendMessage("/tb help for command list");
        }


    }
}

