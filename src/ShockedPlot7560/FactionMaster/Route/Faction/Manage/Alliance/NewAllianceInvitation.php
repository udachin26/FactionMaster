<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewAllianceInvitation implements Route {

    const SLUG = "allianceInvitationCreate";

    public $PermissionNeed = [
        Ids::PERMISSION_SEND_ALLIANCE_INVITATION
    ];
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
        $this->backMenu = RouterFactory::get(AllianceMainMenu::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());

        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createInvitationMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            $FactionRequest = MainAPI::getFaction($data[1]);

            if ($data[1] !== "") {
                if ($FactionRequest instanceof FactionEntity) {
                    if (!MainAPI::areInInvitation($this->Faction->name, $data[1], "alliance")) {
                        if (MainAPI::makeInvitation($this->Faction->name, $data[1], "alliance")) {
                            Utils::processMenu($backMenu, $Player, ['§2Sent invitation to ' . $data[1] . " successfuly" ] );
                        }else{
                            $menu = $this->createInvitationMenu(" §c>> §4An error has occured");
                            $menu->sendToPlayer($Player);
                        }
                    }else{
                        $menu = $this->createInvitationMenu(" §c>> §4You have already pending an invitation to this player");
                        $menu->sendToPlayer($Player);
                    }
                }else{
                    $menu = $this->createInvitationMenu(" §c>> §4This user don't exist");
                    $menu->sendToPlayer($Player);
                } 
            }else{
                Utils::processMenu($backMenu, $Player);
            }
        };
    }

    private function createInvitationMenu(string $message = "") : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->setTitle("Send a new invitation");
        $menu->addLabel($message . " \nTo go back, submit nothing");
        $menu->addInput("Name of the faction : ");
        return $menu;
    }
}