<?php

namespace Rushil13579\IgnorePlayer;

use pocketmine\{Player, Server};

use pocketmine\plugin\PluginBase;

use pocketmine\command\{Command, CommandSender};

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

  public $list;

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);

    $this->list = new Config($this->getDataFolder() . "Ignorelist.yml", Config::YAML);
  }

  /**
  *@priority HIGEHST
  **/

  public function onChat(PlayerChatEvent $e){
    $player = $e->getPlayer();

    if($e->isCancelled()){
      return null;
    }

    foreach($this->getServer()->getOnlinePlayers() as $pl){
      if(!empty($this->list->get($pl->getName()))){
        if(!in_array($player->getName(), $this->list->get($pl->getName()))){
          $e->setCancelled();
          $var[$pl->getName()] = $pl;
        }
      } else {
        $var[$pl->getName()] = $pl;
      }
    }
    $e->setRecipients($var);
  }

  public function onCommand(CommandSender $s, Command $cmd, String $label, Array $args) : bool {

    switch($cmd->getName()){
      case 'ignore':
      if(!$s instanceof Player){
        $s->sendMessage('§cPlease use this command in-game');
        return false;
      }

      if(!isset($args[0])){
        $s->sendMessage('§cInsufficient arguments given! Do /ignore help for more information');
        return false;
      }

      switch(strtolower($args[0])){
        case 'help':
          $s->sendMessage("§3=== §bIgnorePlayer §3===\n§c/ignore help: §7Get info regarding IgnorePlayer\n§c/ignore list: §7Get a list of all ignored players\n§c/ignore add [player]: §7Start ignoring a player\n§c/ignore remove [player]: §7Stop ignoring a player");
        break;

        case 'list':
          $names = '';
          if(!empty($this->list->get($s->getName()))){
            foreach(array_keys($this->list->get($s->getName())) as $key){
              $name = $this->list->get($s->getName()[$key]);
              $names .= $name . ', ';
            }
          }
          $s->sendMessage("§3IgnoredPlayers: §7$names");
        break;

        case 'add':
          if(!isset($args[1])){
            $s->sendMessage('§cUsage: /ignore add [player]');
            return false;
          }

          if($this->getServer()->getPlayer($args[1]) === null or !$this->getServer()->getPlayer($args[1])->isOnline()){
            $s->sendMessage('§cPlayer not found');
            return false;
          }

          $player = $this->getServer()->getPlayer($args[1]);

          if($player->getName() == $s->getName()){
            $s->sendMessage('§cYou cannot ignore yourself');
            return false;
          }

          if(!empty($this->list->get($s->getName()))){
            if(in_array($player->getName(), $this->list->get($s->getName()))){
              $s->sendMessage('§cYou are already ignoring ' . $player->getName());
              return false;
            }
          }

          $list = $this->list->get($s->getName(), []);
          $list[] = $player->getName();
          $this->list->set($s->getName(), $list);
          $this->list->save();
          $s->sendMessage('§cYou are now ignoring ' . $player->getName());
        break;

        case 'remove':
          if(!isset($args[1])){
            $s->sendMessage('§cUsage: /ignore remove [player]');
            return false;
          }

          if($this->getServer()->getPlayer($args[1]) === null or !$this->getServer()->getPlayer($args[1])->isOnline()){
            $s->sendMessage('§cPlayer not found');
            return false;
          }

          $player = $this->getServer()->getPlayer($args[1]);

          if(empty($this->list->get($s->getName()))){
            $s->sendMessage('§cYou are not ignoring ' . $player->getName());
            return false;
          }

          if(!in_array($player->getName(), $this->list->get($s->getName()))){
            $s->sendMessage('§cYou are not ignoring ' . $player->getName());
            return false;
          }

          $list = $this->list->get($s->getName(), []);
          $key = array_search($player->getName(), $list);
          unset($list[$key]);
          $this->list->set($s->getName(), $list);
          $this->list->save();
          $s->sendMessage('§cYou are no longer ignoring ' . $player->getName());
        break;
      }
    }
    return true;
  }
}
