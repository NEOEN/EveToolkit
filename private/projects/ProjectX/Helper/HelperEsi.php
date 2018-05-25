<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 22.05.2018
 * Time: 19:54
 */

namespace projects\ProjectX\Helper;

use \Modules\Curl\Curl;

class HelperEsi
{
    const ESI_URL = 'https://esi.tech.ccp.is/';

    /**
     * @param $clientId configuration[esi][clientId]
     * @param $secretKey configuration[esi][secretKey]
     * @param $authCode
     * @param $curl \Modules\Curl\Curl
     *
     * @return mixed
     */
    static public function getAccessToken($clientId, $secretKey, $authCode, Curl $curl)
    {
        $url = "https://login.eveonline.com/oauth/token";
        $post = "grant_type=authorization_code&code=".$authCode;

        $header = [
            'Host: login.eveonline.com',
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '.base64_encode($clientId.":".$secretKey),
        ];

        return $curl->run($url, $header, $post);

    }

    /**
     * @param $clientId configuration[esi][clientId]
     * @param $secretKey configuration[esi][secretKey]
     * @param $refreshToken
     * @param $curl \Modules\Curl\Curl
     *
     * @return mixed
     */
    static public function refreshToken($clientId, $secretKey, $refreshToken, Curl $curl)
    {
        $url = "https://login.eveonline.com/oauth/token";
        $post = "grant_type=refresh_token&refresh_token=".$refreshToken;

        $header = [
            'Host: login.eveonline.com',
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '.base64_encode($clientId.":".$secretKey),
        ];

        return $curl->run($url, $header, $post);
    }


    /**
     * @param $accessToken
     * @param $tokenType
     * @param $curl
     * @param $urlPath
     *
     * @return mixed
     */
    static public function get($accessToken, $tokenType, $curl, $urlPath)
    {
        $url = HelperEsi::ESI_URL.$urlPath;

        $header = [
            'Host: esi.tech.ccp.is',
            'Authorization: '.$tokenType.' '.$accessToken,
            'Accept: application/json',
        ];

        return $curl->run($url, $header, null, 'GET');
    }

    static public function post()
    {

    }

    /**
     * Gibt Daten zum mit dem AccessToken verifizierten User zurück
     *
     * @param $accessToken
     * @param $tokenType
     * @param $curl
     *
     * @return array
     */
    static public function getCharacterVerify($accessToken, $tokenType, $curl)
    {
        $url = "verify/";

        return self::get($accessToken, $tokenType, $curl, $url);
    }


    /**
     * Gibt Daten des übergebenen Characters zurück
     *
     * @param $accessToken
     * @param $tokenType
     * @param $curl
     * @param $characterId
     *
     * @return array
     */
    static public function getCharacters($accessToken, $tokenType, $curl, $characterId)
    {
        $url = "latest/characters/$characterId/";
        $get = '?datasource=tranquility';

        return self::get($accessToken, $tokenType, $curl, $url.$get);
    }


    static public function getCharactersPortrait($accessToken, $tokenType, $curl, $characterId)
    {
        $url = "latest/characters/$characterId/portrait/";
        $get = '?datasource=tranquility';

        return self::get($accessToken, $tokenType, $curl, $url.$get);
    }

    /**
     * Gibt die Orders zurück die der Character gerade im Markt hat
     *
     * @param $accessToken
     * @param $tokenType
     * @param $curl
     * @param $characterId
     *
     * @return mixed
     */
    static public function getCharactersOrders($accessToken, $tokenType, $curl, $characterId)
    {
        $url = "latest/characters/$characterId/orders/";
        $get = '?datasource=tranquility';

        return self::get($accessToken, $tokenType, $curl, $url.$get);
    }

    static public function getCharactersOrderHistory($accessToken, $tokenType, $curl, $characterId)
    {
        $url = "latest/characters/$characterId/orders/history/";
        $get = '?datasource=tranquility';

        return self::get($accessToken, $tokenType, $curl, $url.$get);
    }
}