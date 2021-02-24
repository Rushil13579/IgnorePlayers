<?php

namespace Rushil13579\IgnorePlayer;

use pocketmine\{Player, Server};

use pocketmine\plugin\PluginBase;

use pocketmine\command\{Command, CommandSender};

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerChatEvent};

use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

  public $list;

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);

    $this->list = new Config($this->getDataFolder() . "Ignorelist.yml", Config::YAML);
  }

  public function onJoin(PlayerJoinEvent $e){
    $player = $e->getPlayer();
    if(!$this->list->exists($player->getName())){
      $this->list->set($player->getName(), []);
      $this->list->save();
    }
  }

  /**
  *@priority HIGEHST
  **/

  public function onChat(PlayerChatEvent $e){
    $player = $e->getPlayer();

    if($e->isCancelled()){
      return null;
    }

    $rec = array();
    foreach($this->getServer()->getOnlinePlayers() as $pl){
      if(empty($this->list->get($pl->getName()))){
        $rec[] = $pl;
      } else {
        if(!in_array($player->getName(), $this->list->get($pl->getName()))){
          $rec[] = $pl;
        }
      }
    }
    $e->setRecipients($rec);
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
          $s->sendMessage("§3=== §bIgnorePlayer §3===\n§c/ignore help: §7Get information regarding IgnorePlayer\n§c/ignore list: §7Get a list of all ignored players\n§c/ignore add [player]: §7Add a player to the ignored list\n§c/ignore remove [player]: §7Remove a player from the ignored list");
        break;

        case 'list':
          $names = "";
          if(empty($this->list->get($s->getName()))){
            $s->sendMessage("§3IgnoredPlayers: §7$names");
            return false;
          }

          $list = $this->list->get($s->getName());
          foreach(array_keys($list) as $key){
            $name = $list[$key];
            $names .= $name . ", ";
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

          if(empty($this->list->get($s->getName()))){
            $list[] = $player->getName();
            $this->list->set($s->getName(), $list);
            $this->list->save();
            $s->sendMessage('§cYou are now ignoring ' . $player->getName());
            return false;
          }

          if(in_array($player->getName(), $this->list->get($s->getName()))){
            $s->sendMessage('§cYou are already ignoring ' . $player->getName());
            return false;
          }

          $list = $this->list->get($s->getName());
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

          $list = $this->list->get($s->getName());
          $key = array_search($player->getName(), $list);
          array_splice($list, $key, 1);
          $this->list->set($s->getName(), $list);
          $this->list->save();
          $s->sendMessage('§cYou are not longer ignoring ' . $player->getName());
        break;

        default:
          $s->sendMessage('§cInvalid argument given. Do /ignore help for more information');
        break;
      }
    }
    return true;
  }
}
