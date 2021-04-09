<?php

namespace Rushil13579\IgnorePlayer\Commands;

use pocketmine\{Server, Player};

use pocketmine\plugin\Plugin;

use pocketmine\command\{Command, CommandSender, PluginIdentifiableCommand};

use Rushil13579\IgnorePlayer\Main;

class Ignore extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('ignore', 'Allows a player to ignore the specified player on this server', '/ignore help', ['ig']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage('§cPlease use this command in-game');
            return false;
        }
      
        if(!isset($args[0])){
            $sender->sendMessage('§cInsufficient arguments given! Do /ignore help for more information');
            return false;
        }
    
        switch(strtolower($args[0])){
            case 'help':
                $sender->sendMessage("§3=== §bIgnorePlayer §3===\n§c/ignore help: §7Get information regarding IgnorePlayer\n§c/ignore list: §7Get a list of all ignored players\n§c/ignore add [player]: §7Add a player to the ignored list\n§c/ignore remove [player]: §7Remove a player from the ignored list");
            break;
            
            case 'list':
                $names = "";
                if(empty($this->main->list->get($sender->getName()))){
                    $sender->sendMessage("§3IgnoredPlayers: §7$names");
                    return false;
                }
    
                $list = $this->main->list->get($sender->getName());
                foreach(array_keys($list) as $key){
                    $name = $list[$key];
                    $names .= $name . ", ";
                }
    
                $sender->sendMessage("§3IgnoredPlayers: §7$names");
            break;
    
            case 'add':
                if(!isset($args[1])){
                    $sender->sendMessage('§cUsage: /ignore add [player]');
                    return false;
                }
    
                if($this->main->getServer()->getPlayer($args[1]) === null or !$this->main->getServer()->getPlayer($args[1])->isOnline()){
                    $sender->sendMessage('§cPlayer not found');
                    return false;
                }
    
                $player = $this->main->getServer()->getPlayer($args[1]);
    
                if($player->getName() == $sender->getName()){
                    $sender->sendMessage('§cYou cannot ignore yourself');
                    return false;
                }
    
                if(empty($this->main->list->get($sender->getName()))){
                    $list[] = $player->getName();
                    $this->main->list->set($sender->getName(), $list);
                    $this->main->list->save();
                    $sender->sendMessage('§cYou are now ignoring ' . $player->getName());
                    return false;
                }
    
                if(in_array($player->getName(), $this->main->list->get($sender->getName()))){
                    $sender->sendMessage('§cYou are already ignoring ' . $player->getName());
                    return false;
                }
    
                $list = $this->main->list->get($sender->getName());
                $list[] = $player->getName();
                $this->main->list->set($sender->getName(), $list);
                $this->main->list->save();
                $sender->sendMessage('§cYou are now ignoring ' . $player->getName());
            break;
    
            case 'remove':
                if(!isset($args[1])){
                    $sender->sendMessage('§cUsage: /ignore remove [player]');
                    return false;
                }
    
                if($this->main->getServer()->getPlayer($args[1]) === null or !$this->main->getServer()->getPlayer($args[1])->isOnline()){
                    $sender->sendMessage('§cPlayer not found');
                    return false;
                }
    
                $player = $this->main->getServer()->getPlayer($args[1]);
    
                if(empty($this->main->list->get($sender->getName()))){
                    $sender->sendMessage('§cYou are not ignoring ' . $player->getName());
                    return false;
                }
    
                if(!in_array($player->getName(), $this->main->list->get($sender->getName()))){
                    $sender->sendMessage('§cYou are not ignoring ' . $player->getName());
                    return false;
                }
    
                $list = $this->main->list->get($sender->getName());
                $key = array_search($player->getName(), $list);
                array_splice($list, $key, 1);
                $this->main->list->set($sender->getName(), $list);
                $this->main->list->save();
                $sender->sendMessage('§cYou are not longer ignoring ' . $player->getName());
            break;
    
            default:
                $sender->sendMessage('§cInvalid argument given. Do /ignore help for more information');
            break;
        }
    }

    public function getPlugin() : Plugin {
        return $this->main;
    }
}
