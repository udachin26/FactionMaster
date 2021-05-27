<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class TopFactionPanel implements Route {

    const SLUG = "topFaction";

    public $PermissionNeed = [];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    /**
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $menu = $this->topFactionMenu();
        $player->sendForm($menu);;
    }

    public function call() : callable{
        $backRoute = $this->backMenu;
        return function (Player $Player, $data) use ($backRoute) {
            if ($data === null) return;
            Utils::processMenu($backRoute, $Player);
        };
    }

    private function topFactionMenu() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu->setTitle("Top Faction");
        $content = '';
        foreach (MainAPI::getTopFaction() as $key => $Faction) {
            $content .= "§r §6> §7[§e".($key+1)."§7]§f " . $Faction->name . "  §o§7Level " . $Faction->level . " \n";
        }
        $menu->addButton("§4Back");
        $menu->setContent($content);
        return $menu;
    }
}