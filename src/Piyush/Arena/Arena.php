<?php
namespace Piyush\Arena;

use jackmd\scorefactory\ScoreFactory;
use jackmd\scorefactory\ScoreFactoryException;
use Piyush\Arena\Vector3;
use Piyush\Main;
use pocketmine\block\BlockLegacyIds;
use pocketmine\color\Color;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use jojoe77777\FormAPI\SimpleForm;



class Arena implements Listener
{
    public const PHASE_LOBBY = 0;
    public const MSG_MESSAGE = 0;
    public const MSG_TIP = 1;
    public const MSG_POPUP = 2;
    public const MSG_TITLE = 3;
    public const PHASE_GAME = 1;
    public const PHASE_RESTART = 2;
    /** @var reset $Reset */
    public $reset;

    public $plugin;
    /** @var Player[] $redss */
    public array $redss = [];
    /** @var Player[] $bluess */
    public array $bluess = [];


    public $spectators = [];

    /**
     * @var Scheduler
     */
    public $scheduler;
    /** @var World $world */
    public $world = null;
    /** @var array $data */
    public $data = [];
    /** @var Player[] $players */
    public $players = [];
    public $reds = [];
    public $teamBlock = [];
    public $blues = [];
    public $phase = 0;
    public $kills = [];
public $lastDamage = [];

    public $gameStarted = false;
    /**
     * @var bool
     */
    public $setup;
    public $bluesp = 0;
    public $redsp = 0;
    public int $set = 0;
    public string $team;
    public array $playerss = [];
    public array $preventfalldamage = [];
    public int $preventfalldamageT = 10;
    public $deaths = [];

    public function __construct(Main $plugin, array $arenaData)
    {
        $this->plugin = $plugin;
        $this->data = $arenaData;
        $this->setup = !$this->enable(\false);
        $this->reset = new reset($this);
        $this->plugin->getScheduler()->scheduleRepeatingTask($this->scheduler = new Scheduler($this), 20);
        if ($this->setup) {
            if (empty($this->data)) {
                $this->makeBasicData();
            }
        } else {
            $this->loadArena();
        }
    }

    public function enable(bool $loadArena = true): bool
    {
        if (empty($this->data)) {
            return false;
        }
        if ($this->data["world"] == null) {
            return false;
        }
        if (!$this->plugin->getServer()->getWorldManager()->isWorldGenerated($this->data["world"])) {
            return false;
        }
        if (!isset($this->data["lobby"])) {
            return false;
        }
        if (!is_array($this->data["spawnRed"])) {
            return false;
        }
        if (!is_array($this->data["spawnBlue"])) {
            return false;
        }
        $this->data["enabled"] = true;
        $this->setup = false;
        if ($loadArena) $this->loadArena();
        return true;
    }


    public function loadArena(bool $restart = false)
    {


        if (!$this->data["enabled"]) {
            $this->plugin->getLogger()->error("Can not load arena: Arena is not enabled!");
            return;
        }


        if (!$restart) {
            $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);

            if (!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($this->data["world"])) {
                $this->reset->resetMap($this->data["world"]);
            }

            $this->world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["world"]);
        } else {
         if($this->world == null){
$this->setup = true;
$this->data["world"] = null;
return;
}
        }

        if (!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($this->data["world"])) {
            $this->reset->resetMap($this->data["world"]);
        }

        if (!$this->world instanceof World) {
            $this->world = $this->reset->resetMap($this->data["world"]);
        }

        if (!$this->world instanceof World) {
            $this->plugin->getLogger()->error("Disabling arena {$this->data["world"]}: level not found!");
            $this->data["world"] = null;
            return;
        }


        if (is_null($this->world)) {
            $this->setup = true;
        }
        $this->phase = 0;
        $this->bluesp = 0;
        $this->redsp = 0;
        $this->blues = [];
        $this->players = [];
        $this->reds = [];
        $this->kills = [];
        $this->redss = [];
        $this->bluess = [];
        $this->playerss = [];
        $this->preventfalldamage = [];
        $this->deaths = [];
        $this->lastDamage = [];
        $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["world"])->setAutoSave(false);
    }

    public function joinGame(Player $player)
    {

        if(!$this->phase == 0){
            $player->sendMessage($this->getPrefix() ." Arena Is Started/Restarting");
            return false;
        }
        if (count($this->players) == 8){
            $player->sendMessage($this->getPrefix() . " Lobby is Full");
            return false;
        }



foreach($this->plugin->arena as $arenas){
        if ($arenas->onGame($player)) {
            $player->sendMessage($this->getPrefix() . "Your already ingame please leave the game first");
            return false;
        }
}

        array_push($this->playerss, $player->getName());

        $this->players[] = $player;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $this->broadcastMessage(TextFormat::RED ."THEBRIDGE >" .TextFormat::AQUA."{$player->getName()} Join The Game ".count($this->players)."/8", self::MSG_MESSAGE);
        $player->setGamemode(GameMode::ADVENTURE());
        $player->setHealth(20);
        $player->getHungerManager()->setFood(20);
        $inv = $player->getInventory();
        $this->kills[$player->getName()] = 0;
        $this->deaths[$player->getName()] = 0;
        $player->teleport(Position::fromObject(Vector3::fromString($this->data["lobby"][0]), $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["lobby"][1])));
       if(count($this->reds) < 4){
           $this->redss[] = $player;
           $this->reds[] = $player->getName();
           $inv->setItem(4, (new ItemFactory)->get(ItemIds::WOOL, 14, 1)->setCustomName("§r§e Change Team\n§7[Use]"));
       }
        elseif(count($this->blues) < 4){
            $this->blues[] = $player->getName();
            $this->bluess[] = $player;
            $inv->setItem(4, (new ItemFactory)->get(ItemIds::WOOL, 11, 1)->setCustomName("§r§e Change Team\n§7[Use]"));
        }
       $inv->setItem(8, (New ItemFactory())->get(ItemIds::BED)->setCustomName("§r§e Leave The Game]\n§7[Use]"));

    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
            $pos = $player->getPosition();
            $block = $player->getWorld()->getBlock($player->getPosition()->subtract(0, 1, 0));
            foreach ($this->players as $players) {
                foreach ($this->redss as $redPlayer){
                    foreach ($this->bluess as $bluePlayer){
                        if ($this->onGame($players) && $this->phase == Arena::PHASE_GAME) {
                            if ($block->getId() == 234) {
                                if (in_array($player->getName(), $this->reds)) {
                                    $this->tpRed($player);
                                    $player->sendMessage($this->getPrefix() . "Cannot jump on own Goal");
                                    return false;
                                }
                                if (in_array($player->getName(), $this->blues)) {
                                    $this->broadcastMessage(TextFormat::BLUE . $player->getName() . TextFormat::BLUE . "\nGoal", Arena::MSG_TITLE);

                                    
                                    $this->tpRed($player, true);
                                    $this->tpBlue($player, true);
                                    $this->bluesp++;
                                    return false;
                                }
                            }
                            if ($block->getId() == 231) {
                                if (in_array($player->getName(), $this->blues)) {
                                    $this->tpBlue($player);
                                    $player->sendMessage($this->getPrefix() . "Cannot jump on own Goal");
                                    return false;
                                }
                                if (in_array($player->getName(), $this->reds)) {
                                    $this->broadcastMessage(TextFormat::RED . $player->getName() . TextFormat::RED . "\nGoal", Arena::MSG_TITLE);
                                    $this->tpBlue($player, true);
                                    $this->tpRed($player, true);
                                    
                                    $this->redsp++;
                                    return false;
                                }
                            }
}
}
                        }
            }
        }

    /**
     * @throws ScoreFactoryException
     */
    public function leaveGame(Player $player, bool $sendMessage = true)
    {

        switch ($this->phase) {
            case Arena::PHASE_LOBBY:
                $index = "";
                foreach ($this->players as $i => $p) {
                    if ($p->getId() == $player->getId()) {
                        $index = $i;
                    }
                }
                if ($index !== "" && isset($this->players[$index])) {
                    unset($this->players[$index]);
                    unset($this->kills[array_search($index, $this->kills)]);
                    unset($this->deaths[array_search($index, $this->kills)]);
                    if (in_array($player->getName(), $this->playerss)) {
                        unset($this->playerss[$index]);

                    }
                    if (in_array($player->getName(), $this->reds)) {
                        unset($this->reds[$index]);
                        unset($this->redss[$index]);
                    }
                    if (in_array($player->getName(), $this->blues)) {
                        unset($this->blues[$index]);
                        unset($this->bluess[$index]);
                    }
                }
                break;
            default:
                 if (in_array($player->getName(), $this->reds)) {
                        unset($this->redss[$player->getName()]);
                    }
                    if (in_array($player->getName(), $this->blues)) {
                        unset($this->bluess[$player->getName()]);
                    }
                unset($this->players[$player->getName()]);
                unset($this->kills[array_search($player->getName(), $this->kills)]);
                unset($this->deaths[array_search($player->getName(), $this->kills)]);
                if (in_array($player->getName(), $this->playerss)) {
                    unset($this->playerss[array_search($player->getName(), $this->playerss)]);

                }
                if (in_array($player->getName(), $this->reds)) {
                    unset($this->reds[array_search($player->getName(), $this->reds)]);
                }
                if (in_array($player->getName(), $this->blues)) {
                    unset($this->blues[array_search($player->getName(), $this->blues)]);
                }
                break;
        }

        if ($player->isOnline()) {
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getCursorInventory()->clearAll();

            $player->setGamemode($this->plugin->getServer()->getGamemode());
            $player->setHealth(20);
            $player->getHungerManager()->setFood(20);
            $player->teleport(Position::fromObject(Vector3::fromString($this->data["dcpos"][0]), $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["dcpos"][1])));
            ScoreFactory::removeScoreLines($player);
            ScoreFactory::removeObjective($player);
            $this->broadcastMessage(TextFormat::RED . "THEBRIDGE >" . TextFormat::AQUA . "{$player->getName()} Left The Game " . count($this->players) . "/8", self::MSG_MESSAGE);
            if ($sendMessage) $player->sendMessage($this->getPrefix() . "You Left The Game");
        }
    }

    public function  onStart()
    {

        $this->phase = self::PHASE_GAME;
        $players = [];
        $blues = [];
        $reds = [];
        $this->gameStarted = true;
        $this->scheduler->crt = 10;
        $this->scheduler->minustime = true;
        $this->scheduler->startMsg = true;

        foreach ($this->players as $player) {
            foreach ($this->redss as $red) {
    foreach ($this->bluess as $blue){
            $players[$player->getName()] = $player;
        $reds[$red->getName()] = $red;
        $blues[$blue->getName()] = $blue;
            $player->setGamemode(GameMode::SURVIVAL());
            $player->getInventory()->clearAll();
            $player->setImmobile(false);
            if(in_array($player->getName(), $this->reds)){
                $this->tpRed($player);
            } elseif(in_array($player->getName(), $this->blues)){
                $this->tpBlue($player);

            }
        }
        

         $this->players = $players;
         $this->bluess = $blues;
         $this->redss = $reds;
         $this->phase = 1;
}
}
}

public function tpBlue(Player $player, bool $addGlass = false){
    if ($addGlass){
        foreach ($this->bluess as $players){
        $this->scheduler->addGlassBlue();
        $this->scheduler->addMsg = true;
        $this->scheduler->minustime = true;
        $this->scheduler->crt = 5;
        $this->scheduler->startMsg = false;
        $this->preventfalldamage[] = $players->getName();
    $this->preventfalldamage[$players->getName()] = microtime(true);
   $players->teleport(Position::fromObject(new \pocketmine\math\Vector3($this->data["spawnBlue"][2], $this->data["spawnBlue"][3], $this->data["spawnBlue"][4]), $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["spawnBlue"][1])));
        $players->setHealth(20);
        $inv = $players->getInventory();
    $inv->setItem(0, (new ItemFactory())->get(ItemIds::WOODEN_SWORD));
    $inv->setItem(1, (new ItemFactory())->get(ItemIds::BOW));
    $inv->setItem(2, (new ItemFactory())->get(ItemIds::SHEARS));
    $inv->setItem(3, (new ItemFactory())->get(ItemIds::WOOL, 11, 64));
    $inv->setItem(4, (new ItemFactory())->get(ItemIds::WOOL, 11, 64));
    $inv->setItem(8, (new ItemFactory())->get(ItemIds::ARROW, 0 , 10));
    $h = VanillaItems::LEATHER_CAP();
    $c = VanillaItems::LEATHER_TUNIC();
    $l = VanillaItems::LEATHER_PANTS();
    $b = VanillaItems::LEATHER_BOOTS();
    $color = new Color(0, 0, 255);
    $h->setCustomColor($color);
    $c->setCustomColor($color);
    $l->setCustomColor($color);
    $b->setCustomColor($color);
    $players->getArmorInventory()->setHelmet($h);
    $players->getArmorInventory()->setChestplate($c);
    $players->getArmorInventory()->setLeggings($l);
    $players->getArmorInventory()->setBoots($b);
        }
        return;
    }
    $this->preventfalldamage[] = $player->getName();
    $this->preventfalldamage[$player->getName()] = microtime(true);
   $player->teleport(Position::fromObject(new \pocketmine\math\Vector3($this->data["spawnBlue"][2], $this->data["spawnBlue"][3], $this->data["spawnBlue"][4]), $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["spawnBlue"][1])));
        $player->setHealth(20);
        $inv = $player->getInventory();
    $inv->setItem(0, (new ItemFactory())->get(ItemIds::WOODEN_SWORD));
    $inv->setItem(1, (new ItemFactory())->get(ItemIds::BOW));
    $inv->setItem(2, (new ItemFactory())->get(ItemIds::SHEARS));
    $inv->setItem(3, (new ItemFactory())->get(ItemIds::WOOL, 11, 64));
    $inv->setItem(4, (new ItemFactory())->get(ItemIds::WOOL, 11, 64));
    $inv->setItem(8, (new ItemFactory())->get(ItemIds::ARROW, 0 , 10));
    $h = VanillaItems::LEATHER_CAP();
    $c = VanillaItems::LEATHER_TUNIC();
    $l = VanillaItems::LEATHER_PANTS();
    $b = VanillaItems::LEATHER_BOOTS();
    $color = new Color(0, 0, 255);
    $h->setCustomColor($color);
    $c->setCustomColor($color);
    $l->setCustomColor($color);
    $b->setCustomColor($color);
    $player->getArmorInventory()->setHelmet($h);
    $player->getArmorInventory()->setChestplate($c);
    $player->getArmorInventory()->setLeggings($l);
    $player->getArmorInventory()->setBoots($b);
}
    public function tpRed(Player $player, bool $addGlass = false){
        if ($addGlass){
            foreach($this->redss as $players){
            $this->scheduler->addGlassRed();
            $this->scheduler->minustime = true;
            $this->scheduler->crt = 5;
            $this->scheduler->startMsg = false;
            $this->preventfalldamage[] = $players->getName();
        $this->preventfalldamage[$players->getName()] = microtime(true);
        $players->teleport(Position::fromObject(new \pocketmine\math\Vector3($this->data["spawnRed"][2], $this->data["spawnRed"][3], $this->data["spawnRed"][4]), $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["spawnRed"][1])));
        $players->setHealth(20);
        $inv = $players->getInventory();


        $inv->setItem(0, (new ItemFactory())->get(ItemIds::WOODEN_SWORD));
        $inv->setItem(1, (new ItemFactory())->get(ItemIds::BOW));
        $inv->setItem(2, (new ItemFactory())->get(ItemIds::SHEARS));
        $inv->setItem(3, (new ItemFactory())->get(ItemIds::WOOL, 14, 64));
        $inv->setItem(4, (new ItemFactory())->get(ItemIds::WOOL, 14, 64));
        $inv->setItem(8, (new ItemFactory())->get(ItemIds::ARROW, 0 , 10));
        $h = VanillaItems::LEATHER_CAP();
        $c = VanillaItems::LEATHER_TUNIC();
        $l = VanillaItems::LEATHER_PANTS();
        $b = VanillaItems::LEATHER_BOOTS();
        $color = new Color(255, 0, 0);
        $h->setCustomColor($color);
        $c->setCustomColor($color);
        $l->setCustomColor($color);
        $b->setCustomColor($color);

        $players->getArmorInventory()->setHelmet($h);
        $players->getArmorInventory()->setChestplate($c);
        $players->getArmorInventory()->setLeggings($l);
        $players->getArmorInventory()->setBoots($b);
            }
            return;
        }
        $this->preventfalldamage[] = $player->getName();
        $this->preventfalldamage[$player->getName()] = microtime(true);
        $player->teleport(Position::fromObject(new \pocketmine\math\Vector3($this->data["spawnRed"][2], $this->data["spawnRed"][3], $this->data["spawnRed"][4]), $this->plugin->getServer()->getWorldManager()->getWorldByName($this->data["spawnRed"][1])));
        $player->setHealth(20);
        $inv = $player->getInventory();


        $inv->setItem(0, (new ItemFactory())->get(ItemIds::WOODEN_SWORD));
        $inv->setItem(1, (new ItemFactory())->get(ItemIds::BOW));
        $inv->setItem(2, (new ItemFactory())->get(ItemIds::SHEARS));
        $inv->setItem(3, (new ItemFactory())->get(ItemIds::WOOL, 14, 64));
        $inv->setItem(4, (new ItemFactory())->get(ItemIds::WOOL, 14, 64));
        $inv->setItem(8, (new ItemFactory())->get(ItemIds::ARROW, 0 , 10));
        $h = VanillaItems::LEATHER_CAP();
        $c = VanillaItems::LEATHER_TUNIC();
        $l = VanillaItems::LEATHER_PANTS();
        $b = VanillaItems::LEATHER_BOOTS();
        $color = new Color(255, 0, 0);
        $h->setCustomColor($color);
        $c->setCustomColor($color);
        $l->setCustomColor($color);
        $b->setCustomColor($color);

        $player->getArmorInventory()->setHelmet($h);
        $player->getArmorInventory()->setChestplate($c);
        $player->getArmorInventory()->setLeggings($l);
        $player->getArmorInventory()->setBoots($b);
    }

public function checkWinRed() : bool
{

    $countend = count($this->blues) == 0;
    return $this->redsp == 5 || $countend;
}
    public function checkWinBlue() : bool
    {
        $countend = count($this->reds) == 0;
        return $this->bluesp == 5 || $countend;
    }
     public function broadcastMessage(string $message, int $id = 0): void
    {
        $players = $this->players;

        foreach ($players as $player) {
            switch ($id) {
                case self::MSG_MESSAGE:
                    $player->sendMessage($message);
                    break;
                case self::MSG_TIP:
                    $player->sendTip($message);
                    break;
                case self::MSG_POPUP:
                    $player->sendPopup($message);
                    break;
                case self::MSG_TITLE:
                    $lines = explode("\n", $message);
                    $title = $lines[0];
                    $subtitle = $lines[1] ?? "";

                    $player->sendTitle($title, $subtitle);
                    break;
            }
        }
    }


    public function selectTeamForm($player)
    {
        $inv = $player->getInventory();
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0;
                    if (count($this->reds) < 4) {
                        $index = "";
                        foreach ($this->bluess as $i => $p) {
                            if ($p->getId() == $player->getId()) {
                                $index = $i;
                            }
                        }
                        if (in_array($player->getName(), $this->blues)) {
                            unset($this->blues[array_search($player->getName(), $this->blues)]);
                            unset($this->bluess[$index]);
                            array_push($this->reds, $player->getName());
                            $this->redss[] = $player;
                            $this->teamBlock[$player->getName()] = 14;
                            $inv = $player->getInventory();
                            $inv->setItem(4, (new ItemFactory)->get(ItemIds::WOOL, $this->teamBlock[$player->getName()], 1)->setCustomName("§r§e Change Team\n§7[Use]"));
                            $player->sendMessage("§aYou Have Joined The Red Team");
                        } else {
                            $player->sendMessage("§cYou Are Already on the Red Team");
                        }
                    } else {
                        $player->sendMessage("§cThe Red Team is Full");
                    }
                    break;
                case 1;
                    if (count($this->blues) < 4) {
                        $index = "";
                        foreach ($this->redss as $i => $p) {
                            if ($p->getId() == $player->getId()) {
                                $index = $i;
                            }
                        }
                        if(in_array($player->getName(), $this->reds)){
                            unset($this->reds[array_search($player->getName(), $this->reds)]);
                            unset($this->redss[$index]);
                            array_push($this->blues, $player->getName());
                            $this->bluess[] = $player;
                            $this->teamBlock[$player->getName()] = 11;
                            $inv = $player->getInventory();
                            $inv->setItem(4, (new ItemFactory)->get(ItemIds::WOOL, $this->teamBlock[$player->getName()], 1)->setCustomName("§r§e Change Team\n§7[Use]"));
                            $player->sendMessage("§aYou Have Joined The Blue Team");
                        } else {
                            $player->sendMessage("§cYou Are Already on the Blue Team");
                        }
                    }
                    else {
                        $player->sendMessage("§cThe Blue Team is Full");
                    }
                    break;
            }
            return true;
        });
        $form->setTitle("§lTeam Selector");
        $form->addButton("§cRed Team §7(§a" . count($this->reds) . "§7/§a4§7)", 0, "textures/blocks/wool_colored_red");
        $form->addButton("§1Blue Team §7(§a" . count($this->blues) . "§7/§a4§7)", 0, "textures/blocks/wool_colored_blue");
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @throws ScoreFactoryException
     */
    public function Interact(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if ($this->onGame($player, true) && $event->getAction() === $event::RIGHT_CLICK_BLOCK && $this->phase == 0) {
            if ($player->getInventory()->getItemInHand()->getId() === 35) {
                $this->selectTeamForm($player);
                return;
            }
            if ($player->getInventory()->getItemInHand()->getId() == ItemIds::BED){
                $this->leaveGame($player);
            }
        }
    }

    public function onGame(Player $player, bool $forinteract = false): bool
    {
        switch ($this->phase) {
            case self::PHASE_LOBBY:
                $inGame = false;
                foreach ($this->players as $players) {
                    if ($players->getId() == $player->getId()) {
                        $inGame = true;
                    }
                }
                return $inGame;
            default:
                return isset($this->players[$player->getName()]);
        }
    }

    public function makeBasicData()
    {
        $this->data = [
            "world" => null,
            "spawnRed" => null,
            "spawnBlue" => null,
            "dcpos" => null,
            "lobby" => null
        ];
    }


    /**
     * @param PlayerExhaustEvent $event
     */
    public function onExhaust(PlayerExhaustEvent $event)
    {
        $player = $event->getPlayer();

        if (!$player instanceof Player) return;

        if ($this->onGame($player)) {
            $player->getHungerManager()->setFood(20);
            $event->cancel();
        }
    }

    public function restart()
    {
        $this->phase = self::PHASE_RESTART;
    }


    public function getLevel(): ?World
    {
        $data = $this->data;
        if(isset($data["world"])){
            $name = $data["world"];
            if($this->plugin->getServer()->getWorldManager()->getWorldByName($name)->isLoaded()){
                $level = $this->plugin->getServer()->getWorldManager()->getWorldByName($name);
                return $level;
            }
        }
        return null;
    }


    public function getBlueSpawn(): ?Position
    {
        $data = $this->data;
        if(isset($data["spawnBlue"])){
            $dt = $data["spawnBlue"];
            if(isset($data["world"])){
                $name = $data["world"];
                if($this->plugin->getServer()->getWorldManager()->getWorldByName($name)->isLoaded()){
                    $level = $this->plugin->getServer()->getWorldManager()->getWorldByName($name);
                    return Position::fromObject(Vector3::fromString($dt["0"]), $level);
                }
            }
        }
        return null;
    }

    public function getRedSpawn(): ?Position
    {
        $data = $this->data;
        if(isset($data["spawnRed"])){
            $dt = $data["spawnRed"];
            if(isset($data["world"])){
                $name = $data["world"];
                if($this->plugin->getServer()->getWorldManager()->getWorldByName($name)->isLoaded()){
                    $level = $this->plugin->getServer()->getWorldManager()->getWorldByName($name);
                    return Position::fromObject(Vector3::fromString($dt["0"]), $level);
                }
            }
        }
        return null;
    }

    public function getPrefix(): string
    {
        return TextFormat::RED . "THEBRIDGE >" .TextFormat::AQUA ;
    }


}
