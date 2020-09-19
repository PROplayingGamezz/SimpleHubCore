<?php

namespace simplehubcore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener
{

    /** @var Config */
    public $config;

    function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->getLogger()->notice("If your default world is NOT the 'HubsName' in the config then you will crash on joining");
    }

    public function onCommand(CommandSender $s, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "hub":
                if ($s instanceof Player) {
                    if (count($args) <= 0) {
                        if ($this->getConfig()->get("DifferentServer") == true) {
                            $address = $this->getConfig()->get("Address");
                            $port = $this->getConfig()->get("Port");
                            $s->transfer($address, $port);
                        } else {
                            if ($this->getConfig()->get("ClearInventory") == true) {
                                $s->teleport($this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))->getSafeSpawn());
                                $s->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 3, 5, true));
                                $s->addEffect(new EffectInstance(Effect::getEffect(Effect::NAUSEA), 10 * 3, 10, true));
                                $s->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 10 * 3, 10, true));
                                $s->getInventory()->clearAll();
                                $s->getArmorInventory()->clearAll();
                                $s->sendPopup("§gYour inventory has been cleared!");
                                $s->sendMessage("§gYou have been teleported to the hub!");
                            } else {
                                $s->teleport($this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))->getSafeSpawn());
                                $s->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 3, 5, true));
                                $s->addEffect(new EffectInstance(Effect::getEffect(Effect::NAUSEA), 10 * 3, 10, true));
                                $s->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 10 * 3, 10, true));
                                $s->sendMessage("§gYou have been teleported to the hub!");
                            }
                        }
                    }
                    if (count($args) >= 1) {
                        $p = $this->getServer()->getPlayer($args[0]);
                        if ($s->hasPermission("hubothers.permission")) {
                            if ($this->getServer()->getPlayer($args[0]) !== null) {
                                if ($this->getConfig()->get("DifferentServer") == true) {
                                    $address = $this->getConfig()->get("Address");;
                                    $port = $this->getConfig()->get("Port");
                                    $p->transfer($address, $port);
                                    $s->sendMessage("§gYou have send §b$p §gto the hub");
                                } else {
                                    if ($this->getConfig()->get("ClearInventory") == true) {
                                        $p->teleport($this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))->getSafeSpawn());
                                        $p->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 3, 5, true));
                                        $p->addEffect(new EffectInstance(Effect::getEffect(Effect::NAUSEA), 10 * 3, 10, true));
                                        $p->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 10 * 3, 10, true));
                                        $p->getInventory()->clearAll();
                                        $p->getArmorInventory()->clearAll();
                                        $p->sendPopup("§gYour inventory has been cleared!");
                                        $p->sendMessage("§b" . $s->getDisplayName() . " §ghas sent you to the hub!");
                                        $s->sendMessage("§gYou have send §b" . $p->getDisplayName() . " §gto the hub");
                                    } else {
                                        $p->teleport($this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))->getSafeSpawn());
                                        $s->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 3, 5, true));
                                        $s->addEffect(new EffectInstance(Effect::getEffect(Effect::NAUSEA), 10 * 3, 10, true));
                                        $s->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 10 * 3, 10, true));
                                        $p->sendMessage("§b" . $s->getDisplayName() . " §ghas sent you to the hub!");
                                        $s->sendMessage("§gYou have send §b" . $p->getDisplayName() . " §gto the hub");
                                    }
                                }
                            } else {
                                $s->sendMessage("§b$args[0] §gnot online");
                            }
                        } else {
                            $s->sendMessage("§gYou don't have permission to tp others to hub !");
                        }
                    }
                }
                break;
            case "sethub";
                if ($s instanceof Player) {
                    if (count($args) <= 0) {
                        if ($this->getConfig()->get("DifferentServer") == false) {
                            if ($s->hasPermission("sethub.permission")) {
                                if ($s->getLevel() === $this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))) {
                                    $level = $s->getLevelNonNull();
                                    $pos = (new Vector3($s->x, $s->y, $s->z))->round();
                                    $level->setSpawnLocation($pos);
                                    $s->sendMessage("§gHub has been set at §b" . $s->getX() . "§g, §b" . $s->getY() . "§g, §b" . $s->getZ());
                                } else {
                                    $s->sendMessage("§gYou can only sethub in §b" . $this->getConfig()->get("HubsName") . " §gworld");
                                }
                            } else {
                                $s->sendMessage("§gYou don't have permission to to use this command");
                            }
                        } else {
                            $s->sendMessage("§gPlease disable 'DifferentServer' in the config to be able to sethub");
                        }
                    }
                }
        }
        return true;
    }

    function PlayerBreakevent(BlockBreakEvent $ev)
    {
        $p = $ev->getPlayer();
        if ($this->getConfig()->get("BlockBreak") == false) {
            if ($p->getLevel() === $this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))) {
                $ev->setCancelled(true);
                $p->sendPopup("§gYou can't break blocks here");
                if ($p->hasPermission("breakblocks.permission")) {
                    $ev->setCancelled(false);
                    $p->sendPopup("");
                }
            } else {
                $ev->setCancelled(false);
            }
        }
    }

    function PlayerPlaceEvent(BlockPlaceEvent $ev)
    {
        $p = $ev->getPlayer();
        if ($this->getConfig()->get("BlockPlace") == false) {
            if ($p->getLevel() === $this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))) {
                $ev->setCancelled(true);
                $p->sendPopup("§gYou can't place blocks here");
                if ($p->hasPermission("placeblocks.permission")) {
                    $ev->setCancelled(false);
                    $p->sendPopup("");
                }
            } else {
                $ev->setCancelled(false);
            }
        }
    }

    public function PvPEvent(EntityDamageEvent $ev)
    {
        $p = $ev->getEntity();
        if ($p instanceof Player) {
            if ($this->getConfig()->get("PvP") == false) {
                if ($p->getLevel() === $this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))) {
                    $ev->setCancelled(true);
                } else {
                    $ev->setCancelled(false);
                }
            } else {
                $ev->setCancelled(false);
            }
        }
    }

    function onDamage(EntityDamageEvent $ev)
    {
        $e = $ev->getEntity();
        if ($e instanceof Player) {
            if ($e->getLevel() === $this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))) {
                if ($ev->getCause() === EntityDamageEvent::CAUSE_FIRE)
                    $ev->setCancelled(true);
                if ($ev->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK)
                    $ev->setCancelled(true);
                if ($ev->getCause() === EntityDamageEvent::CAUSE_LAVA)
                    $ev->setCancelled(true);
                if ($ev->getCause() === EntityDamageEvent::CAUSE_FALL)
                    $ev->setCancelled(true);
            } else {
                if ($ev->getCause() === EntityDamageEvent::CAUSE_FIRE)
                    $ev->setCancelled(false);
                if ($ev->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK)
                    $ev->setCancelled(false);
                if ($ev->getCause() === EntityDamageEvent::CAUSE_LAVA)
                    $ev->setCancelled(false);
                if ($ev->getCause() === EntityDamageEvent::CAUSE_FALL)
                    $ev->setCancelled(false);
            }
        }
        return true;
    }

    function PlayerJoinEvent(PlayerJoinEvent $ev)
    {
        $p = $ev->getPlayer();
        if ($this->getConfig()->get("ClearInventory") == true) {
            $ev->setJoinMessage(str_replace(["{name}"], [$p->getName()], $this->getConfig()->get("PlayerJoinMessage")));
            $ev->getPlayer()->teleport($this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))->getSafeSpawn());
            $p->getArmorInventory()->clearAll();
            $p->getInventory()->clearAll();
        } else {
            $ev->setJoinMessage(str_replace(["{name}"], [$p->getName()], $this->getConfig()->get("PlayerJoinMessage")));
            $ev->getPlayer()->teleport($this->getServer()->getLevelByName($this->getConfig()->get("HubsName"))->getSafeSpawn());
        }
    }

    function PlayerLeaveEvent(PlayerQuitEvent $ev)
    {
        $p = $ev->getPlayer();
        $ev->setQuitMessage(str_replace(["{name}"], [$p->getName()], $this->getConfig()->get("PlayerQuitMessage")));
    }

    function PlayerChatEvent(PlayerChatEvent $ev)
    {
        $p = $ev->getPlayer();
        $msg = $ev->getMessage();
        $ev->setFormat(str_replace(["{name}", "{msg}"], [$p->getName(), $msg], $this->getConfig()->get("PlayerChatFormat")));
        $p->setNameTag(str_replace(["{name}"], [$p->getName()], $this->getConfig()->get("PlayerNameTag")));
    }
}























