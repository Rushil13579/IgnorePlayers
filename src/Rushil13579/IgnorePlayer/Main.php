<?php

namespace Rushil13579\IgnorePlayer;

use pocketmine\{Player, Server};

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerChatEvent};

use pocketmine\utils\Config;

use Rushil13579\IgnorePlayer\Commands\{
    TellIgnorePlayer,
    IgnoreIgnorePlayer
};

class Main extends PluginBase implements Listener {

    public $list;
    public $cfg;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->saveDefaultConfig();

        $this->cfg = $this->getConfig();
        $this->list = new Config($this->getDataFolder() . "Ignorelist.yml", Config::YAML);

        $this->versionCheck();

        $this->unregisterCommands();
        $this->registerCommands();
    }

    public function versionCheck(){
        if($this->cfg->get('version') != '1.1.0'){
            $this->getLogger()->warning('§cThe configuration file for IgnorePlayer is outdated! Please delete it and restart your server to install the latest version');
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }
    }

    public function unregisterCommands(){
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->unregister($cmdMap->getCommand('tell'));
    }

    public function registerCommands(){
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->register('IgnorePlayer', new TellIgnorePlayer($this));
        $cmdMap->register('IgnorePlayer', new IgnoreIgnorePlayer($this));
    }

    public function onJoin(PlayerJoinEvent $e){
        $player = $e->getPlayer();
        if(!$this->list->exists($player->getName())){
            $this->list->set($player->getName(), []);
            $this->list->save();
        }
    }

    /**
    *@priority HIGHEST
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
}
