<?php

namespace Piyush\Arena;

use jackmd\scorefactory\ScoreFactory;
use jackmd\scorefactory\ScoreFactoryException;
use Piyush\NPChuman;
use pocketmine\block\BlockFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class Scheduler extends Task
{
    public Arena $plugin;
    public bool $forceStart = false;
    public bool $teleportPlayers = false;
    public int $startTime = 40;
    public int $restartTime = 10;
    public int $crt = 10;
    public bool $minustime = false;
    public  bool $startMsg = false;
    public bool $addMsg = false;

    /**
     * Scheduler constructor.
     * @param Arena $plugin
     */
    public function __construct(Arena $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @throws ScoreFactoryException
     */
    public function onRun() : void
    {


            $this->updateName();
        if ($this->plugin->setup) return;

        if ($this->minustime){
            $this->crt--;
            if ($this->crt == 0){
                $this->removeGlassRed();
                $this->removeGlassBlue();
                $this->startMsg = false;
            }
            if ($this->crt == -3){
                $this->minustime = false;
            }
            if($this->crt >= 0 && $this->startMsg) {
                $this->plugin->broadcastMessage(TextFormat::RED . "Staring In \n" . $this->crt, Arena::MSG_TITLE);
            }
            if($this->crt >= 0 && $this->addMsg) {
                $this->plugin->broadcastMessage(TextFormat::RED  . $this->crt, Arena::MSG_TIP);
            }
        }

        switch ($this->plugin->phase) {
            case Arena::PHASE_LOBBY:

                if ($this->startTime == 0) {
                    $this->plugin->onStart();
                }
                if (count($this->plugin->players) == 8 || $this->forceStart){
                    $this->startTime--;
                }
                foreach ($this->plugin->players as $player){
                    if ($player->isOnline()){
                        $this->onLobbyScore($player);
                    }

}
                    if ($this->teleportPlayers && $this->startTime < 40) {
                        foreach ($this->plugin->players as $player) {
                            $player->teleport(Position::fromObject(Vector3::fromString($this->plugin->data["lobby"][0]), $this->plugin->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->data["lobby"][1])));
                        }
                    }
                break;
            case Arena::PHASE_GAME:
                if ($this->plugin->checkWinRed()) $this->plugin->restart();
                if ($this->plugin->checkWinBlue()) $this->plugin->restart();
                $this->plugin->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->data["world"])->setAutoSave(false);
                foreach ($this->plugin->players as $player) {

                    $this->onGameScore($player);
                }
                break;
            case Arena::PHASE_RESTART:
$this->plugin->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->data["world"])->setAutoSave(false);
                foreach ($this->plugin->players as $ignored){

                    if($this->restartTime >= 0) {
                        $this->plugin->broadcastMessage("§a> Restarting in $this->restartTime sec.", Arena::MSG_TIP);
                    }
                }
                switch ($this->restartTime) {
                    case 9:
                        if ($this->plugin->checkWinBlue()){
                            $this->plugin->plugin->getServer()->broadcastMessage($this->plugin->getPrefix() . "Blue Team Won The Game on " . $this->plugin->data["world"]);
                        }
                        if ($this->plugin->checkWinRed()){
                            $this->plugin->plugin->getServer()->broadcastMessage($this->plugin->getPrefix() . "Red Team Won The Game on " . $this->plugin->data["world"]);
                        }
                        break;
                    case 1:
                        foreach ($this->plugin->players as $player) {
                            $player->getEffects()->clear();
                            $this->plugin->leaveGame($player, false);
                            if ($player->isOnline()) {
                                ScoreFactory::removeObjective($player);
                            }
                        }
                    case -1:
                        $this->plugin->world = $this->plugin->reset->resetMap($this->plugin->data["world"]);
                        break;
                    case -6:
                        $this->plugin->loadArena(true);
                        $this->reloadTimer();
                        $this->plugin->phase = Arena::PHASE_LOBBY;
                        break;
                }
                $this->restartTime--;
                break;
        }


    }

    public function reloadTimer() {
        $this->startTime = 40;
        $this->restartTime = 10;
        $this->forceStart = false;
    }

    public function updateName(){
        $world = $this->plugin->plugin->getConfig()->get("NPCWorld");
        if (!is_null($world)) {
            $humans = $this->plugin->plugin->getServer()->getWorldManager()->getWorldByName($world)->getEntities();
if(!is_null($humans)){
            foreach ($humans as $human)
                if ($human instanceof NPChuman) {
                    $ran = $this->plugin->plugin->emptyArenaChooser->getRandomArena();
                        if (!is_null($ran)) {
                            $human->setNameTag(TextFormat::RED . "TheBridge \n". TextFormat::AQUA . $ran->data["world"] . "\n" . TextFormat::BLUE . count($ran->players) . "/8");
                            $human->setNameTagAlwaysVisible();
                        } else{
                            $human->setNameTag(TextFormat::RED . "THEBRIDGE\n" . TextFormat::AQUA . "ARENAS NOT FOUND");
                        }
                }
        }
}

    }

    /**
     * @throws ScoreFactoryException
     */
    public function onLobbyScore(Player $player)
    {

        if (count($this->plugin->players) == 8 || $this->forceStart){
            $msg =  TextFormat::BOLD ."Starting In: " . $this->startTime + 10;
        } else {
            $msg =  TextFormat::BOLD ." Waiting For Players  ";
        }
        ScoreFactory::setObjective($player, TextFormat::BOLD . TextFormat::RED . "THE BRIDGE");
        ScoreFactory::sendObjective($player);
        ScoreFactory::setScoreLine($player, 1,  TextFormat::BOLD . "   " . date("d/m/Y"));
        ScoreFactory::setScoreLine($player, 2, "");
        ScoreFactory::setScoreLine($player, 3,  TextFormat::BOLD ." Players:  " . count($this->plugin->players) ."/8");
        ScoreFactory::setScoreLine($player, 4,  TextFormat::BOLD . " Kit: Default");
        ScoreFactory::setScoreLine($player, 5, "");
        ScoreFactory::setScoreLine($player, 6,  TextFormat::BOLD ." MAP:  " . $this->plugin->data["world"]);
        ScoreFactory::setScoreLine($player, 7, $msg);
        ScoreFactory::sendLines($player);
    }

    /** @noinspection PhpDeprecationInspection */
    public function removeGlassRed(){
        $data = $this->plugin->data;
            $data = $data["spawnRed"];
//using $team will glitch one team cage
        $x = $data["2"];
        $y = $data["3"];
        $z = $data["4"];
        $level = $this->plugin->world;
        $minX = (int)min($x - 1, $x + 1);
        $maxX = (int)max($x - 1, $x + 1);
        $minZ = (int)min($z - 1, $z + 1);
        $maxZ = (int)max($z - 1, $z + 1);
        for ($x2 = $minX; $x2 <= $maxX;++$x2){
            for ($z2 = $minZ; $z2 <= $maxZ;++$z2) {
                $level->setBlock(new Vector3($x2, $y-1, $z2), (new BlockFactory())->get(0, 0), false);
            }
        }
    }
    /** @noinspection PhpDeprecationInspection */
    public function removeGlassBlue(){
        $data = $this->plugin->data;
        //using $team will glitch one team cage
            $data = $data["spawnBlue"];
        $x = $data["2"];
        $y = $data["3"];
        $z = $data["4"];
        $level = $this->plugin->world;
        $minX = (int)min($x - 1, $x + 1);
        $maxX = (int)max($x - 1, $x + 1);
        $minZ = (int)min($z - 1, $z + 1);
        $maxZ = (int)max($z - 1, $z + 1);
        for ($x2 = $minX; $x2 <= $maxX;++$x2){
            for ($z2 = $minZ; $z2 <= $maxZ;++$z2) {
                $level->setBlock(new Vector3($x2, $y-1, $z2), (new BlockFactory())->get(0, 0), false);
            }
        }
    }

    /**
     * @throws ScoreFactoryException
     */
    public function onGameScore(Player $player)
    {
        ScoreFactory::setObjective($player, "THE BRIDGE");
        ScoreFactory::sendObjective($player);
        ScoreFactory::setScoreLine($player, 1, TextFormat::WHITE .TextFormat::BOLD . date("d/m/Y"));
        ScoreFactory::setScoreLine($player, 2, "");
        ScoreFactory::setScoreLine($player, 3, TextFormat::WHITE .TextFormat::BOLD . "RED POINTS:  " .$this->plugin->redsp);
        ScoreFactory::setScoreLine($player, 4, TextFormat::WHITE . TextFormat::BOLD ."BLUE POINTS:  "  .$this->plugin->bluesp);
        ScoreFactory::setScoreLine($player, 5, "");
        ScoreFactory::setScoreLine($player, 6, TextFormat::WHITE . TextFormat::BOLD ."MAP:  " . $this->plugin->data["world"]);
        ScoreFactory::setScoreLine($player, 7 , TextFormat::BOLD . TextFormat::WHITE . "Kills:  " . $this->plugin->kills[$player->getName()]);
        ScoreFactory::setScoreLine($player, 8 , TextFormat::BOLD . TextFormat::WHITE . "Deaths:  " . $this->plugin->deaths[$player->getName()]);
        ScoreFactory::sendLines($player);
    }

    public function addGlassRed(){
        $data = $this->plugin->data;
        $data = $data["spawnRed"];
//using $team will glitch one team cage
        $x = $data["2"];
        $y = $data["3"];
        $z = $data["4"];
        $level = $this->plugin->world;
        $minX = (int)min($x - 1, $x + 1);
        $maxX = (int)max($x - 1, $x + 1);
        $minZ = (int)min($z - 1, $z + 1);
        $maxZ = (int)max($z - 1, $z + 1);
        for ($x2 = $minX; $x2 <= $maxX;++$x2){
            for ($z2 = $minZ; $z2 <= $maxZ;++$z2) {
                $level->setBlock(new Vector3($x2, $y-1, $z2), (new BlockFactory())->get(20, 14), false);
            }
        }
    }

    public function addGlassBlue(){
        $data = $this->plugin->data;
        $data = $data["spawnBlue"];
//using $team will glitch one team cage
        $x = $data["2"];
        $y = $data["3"];
        $z = $data["4"];
        $level = $this->plugin->world;
        $minX = (int)min($x - 1, $x + 1);
        $maxX = (int)max($x - 1, $x + 1);
        $minZ = (int)min($z - 1, $z + 1);
        $maxZ = (int)max($z - 1, $z + 1);
        for ($x2 = $minX; $x2 <= $maxX;++$x2){
            for ($z2 = $minZ; $z2 <= $maxZ;++$z2) {
                $level->setBlock(new Vector3($x2, $y-1, $z2), (new BlockFactory())->get(20, 11), false);
            }
        }
    }
}

