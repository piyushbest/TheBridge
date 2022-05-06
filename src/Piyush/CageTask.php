<?php

namespace Piyush;

use Piyush\Arena\Arena;
use pocketmine\block\BlockFactory;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class CageTask extends Task
{

    public Arena $plugin;
    public bool $aCage = false;
    public bool $rCage = false;

    public function __construct(Arena $plugin)
    {
        $this->plugin = $plugin;
    }

    /** @noinspection PhpDeprecationInspection */
    public function onRun(): void
    {
        //creating cage cause server lag tried everything help if you can on Discord

            $level = $this->plugin->world;
            if ($this->aCage){
                $data = $this->plugin->data;
                if ($this->plugin->team == "red") {
                    $data = $data["spawnRed"];
                }
                if ($this->plugin->team == "blue") {
                    $data = $data["spawnBlue"];
                }
                $x = $data["2"];
                $y = $data["3"];
                $z = $data["4"];

                $level->setBlock(new Vector3($x+1, $y-1, $z+1), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x+2, $y-1, $z+1), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x-1, $y-1, $z+1), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x-2, $y-1, $z+1), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x-2, $y-1, $z), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x-1, $y-1, $z), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x+2, $y-1, $z), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x+1, $y-1, $z), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z+1), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z+2), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z-1), (new BlockFactory())->get(20, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z-2), (new BlockFactory())->get(20, 0), false);
                $this->aCage = false;
                foreach ($this->plugin->players as $player){
                    $player->setImmobile(false);
                }
            }

            if ($this->rCage){
                $data = $this->plugin->data;
                if ($this->plugin->team == "red") {
                    $data = $data["spawnRed"];
                }
                if ($this->plugin->team == "blue") {
                    $data = $data["spawnBlue"];
                }
                $x = $data["2"];
                $y = $data["3"];
                $z = $data["4"];

                $level->setBlock(new Vector3($x+1, $y-1, $z+1), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x+2, $y-1, $z+1), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x-1, $y-1, $z+1), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x-2, $y-1, $z+1), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x-2, $y-1, $z), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x-1, $y-1, $z), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x+2, $y-1, $z), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x+1, $y-1, $z), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z+1), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z+2), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z-1), (new BlockFactory())->get(0, 0), false);
                $level->setBlock(new Vector3($x, $y-1, $z-2), (new BlockFactory())->get(0, 0), false);
                $this->rCage = false;
                foreach ($this->plugin->players as $player){
                    $player->setImmobile(false);
                }
            }
     }
}