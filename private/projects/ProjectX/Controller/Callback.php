<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 21.05.2018
 * Time: 12:04
 */

namespace projects\ProjectX\Controller;

use \projects\ProjectX\Helper;

class Callback extends \Core\Parent\Controller
{

    /** @var \projects\ProjectX\Dic */
    protected $dic;

    public function run()
    {
        Helper\HelperAuthed::isAuthed($this->dic);

        $httpRequest = $this->dic->getHttpRequest();
        $authCode = $httpRequest->getVar('get', 'code', 'string');
        $state = $httpRequest->getVar('get', 'state', 'string');

        $configuration = $this->dic->getConfiguration('projects/ProjectX');
        $clientId = $configuration->getParameter('esi', 'clientId');
        $secretKey = $configuration->getParameter('esi', 'secretKey');

        $esiRepository = $this->dic->getEsiRepository();

        $session = $this->dic->getSession();

        /** @var \Modules\Curl\Curl $curl */
        $curl = $this->dicManager->getDic('Modules')->getCurl();

        $authResponse = \projects\ProjectX\Helper\HelperEsi::getAccessToken($clientId, $secretKey, $authCode, $curl);

        $charInfo = \projects\ProjectX\Helper\HelperEsi::getCharacterVerify($authResponse['access_token'], $authResponse['token_type'], $curl);

        $esiDb = $esiRepository->getByUserIdAndCharacterId($session->getVar('user')->id, $charInfo['CharacterID']);

        $esiDb->userId = $session->getVar('user')->id;
        $esiDb->characterId = $charInfo['CharacterID'];
        $esiDb->refreshToken = $authResponse['refresh_token'];
        $esiDb->accessToken = $authResponse['access_token'];
        $esiDb->tokenType = $authResponse['token_type'];
        $esiDb->expireTime = date('Y-m-d H:i:s',time() + $authResponse['expires_in']);
        $esi = $esiRepository->save($esiDb);

        $session->setVar('esi', $esi);

        $this->dic->getHttpResponse()->setState(301, $this->dic->getUrl($this->dic->getRouter())->get('Homepage'));
    }
}