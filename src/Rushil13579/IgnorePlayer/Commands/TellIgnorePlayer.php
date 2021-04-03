<?php

namespace Rushil13579\IgnorePlayer\Commands;

use pocketmine\{
    Server,
    Player
};

use pocketmine\command\{
    Command,
    CommandSender
};

use Rushil13579\IgnorePlayer\Main;

class TellIgnorePlayer extends Command implements PluginIdentifiabeCommand {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('tell', 'Sends a private message to the given player', '/tell <player> <private message ...>', ['msg', 'w']);
        $this->setPermission('pocketmine.command.tell');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) < 2){
			$sender->sendMessage('Usage: /tell <player> <private message ...>');
            return false;
		}

		$player = $this->main->getServer()->getPlayer(array_shift($args));

		if($player === $sender){
			$sender->sendMessage('§cYou can\'t send a private message to yourself!');
			return false;
		}

		if($player instanceof Player){
            if($this->main->cfg->get('ignore-private-message') == 'true'){
                if(!empty($this->main->list->get($player->getName()))){
                    if(in_array($sender->getName(), $this->main->list->get($player->getName()))){
                        $sender->sendMessage('§cYou cannot msg this player as they are ignoring you!');
                        return false;
                    }
                }
                $sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
			    $name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			    $player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
            } else {
                $sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
			    $name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			    $player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
            }
		} else {
			$sender->sendMessage('That player cannot be found');
		}
    }

    public function getPlugin(){
        return $this->main;
    }
}