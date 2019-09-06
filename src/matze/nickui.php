<?php

namespace matze;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use jojoe77777\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\utils\TextFormat as C;

class nickui extends PluginBase implements Listener{
    function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($sender instanceof Player){
            if($command->getName() === "nick"){
                $this->openNickUI($sender);
            }
        } return true;
    }

    function openNickUI($player){
        $form = new SimpleForm(function (Player $player, $data){
            $result = $data;
            if($result === null){
                return true;
            }
            switch ($result){
                case 0:
                    break;
                case 1:
                    if($this->getConfig()->get("random-nick") === true){
                        $this->randomNick($player);
                    } elseif($this->getConfig()->get("custom-nick") === true){
                        $this->openCustomNickUI($player);
                    }
                    break;
                case 2:
                    if($this->getConfig()->get("custom-nick") === true){
                        $this->openCustomNickUI($player);
                    }
            }
        });
        $form->setTitle($this->getConfig()->get("Main-Title"));
        $form->addButton($this->getConfig()->get("Main-Button-Close"));
        if($this->getConfig()->get("random-nick") === true){
            $form->addButton($this->getConfig()->get("Main-Button-RandomNick"));
        }
        if($this->getConfig()->get("custom-nick") === true){
            $form->addButton($this->getConfig()->get("Main-Button-CustomNick"));
        }
        $form->sendToPlayer($player);
        return $form;
    }

    function randomNick(Player $player){
        $zahl = mt_rand(0, count($this->getConfig()->get("random-nicks")) -1 );
        $player->setDisplayName($this->getConfig()->get("random-nicks")[$zahl]);
        $player->setNameTag($this->getConfig()->get("random-nicks")[$zahl]);
        $message = $this->getConfig()->get("nick-set");
        $player->sendMessage(str_replace("{nick}", $this->getConfig()->get("random-nicks")[$zahl], $message));
    }

    function openCustomNickUI($player){
        $form = new CustomForm(function (Player $player, $data){
            if($data[0] === null){
               return true;
            }
            if($data[0] !== null){
                $config = $this->getConfig();
                $config = $config->getAll();
                if(!in_array($data[0], $config["not-allow-custom-nicks"])){
                    $player->setDisplayName($data[0]);
                    $player->setNameTag($data[0]);
                    $message = $this->getConfig()->get("nick-set");
                    $player->sendMessage(str_replace("{nick}", $data[0], $message));
                } else {
                    $player->sendMessage($this->getConfig()->get("not-allowed-nick"));
                }
            }
        });
        $form->setTitle($this->getConfig()->get("CustomNick-Title"));
        $form->addInput("Nick", "Nick", "Nick");
        $form->sendToPlayer($player);
        return $form;
    }
}