<?php

declare(strict_types=1);

namespace Piyush\Arena;



use Piyush\Main;

class EmptyArenaChooser {

    /** @var Main $plugin */
    public Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }



    public function getRandomArena(): ?Arena {

        /** @var Arena[] $availableArenas */
        $availableArenas = [];
        foreach ($this->plugin->arena as $index => $arena) {
            $availableArenas[$index] = $arena;
        }

        //2.
        foreach ($availableArenas as $index => $arena) {
            if($arena->phase !== 0 || $arena->setup || ($arena->scheduler->teleportPlayers && $arena->scheduler->startTime < 7)) {
                unset($availableArenas[$index]);
            }
        }

        //3.
        $arenasByPlayers = [];
        foreach ($availableArenas as $index => $arena) {
            $arenasByPlayers[$index] = count($arena->players);
        }

        arsort($arenasByPlayers);
        $top = -1;
        $availableArenas = [];

        foreach ($arenasByPlayers as $index => $players) {
            if($top == -1) {
                $top = $players;
                $availableArenas[] = $index;
            }
            else {
                if($top == $players) {
                    $availableArenas[] = $index;
                }
            }
        }

        if(empty($availableArenas)) {
            return null;
        }

        return $this->plugin->arena[$availableArenas[array_rand($availableArenas, 1)]];
    }
}