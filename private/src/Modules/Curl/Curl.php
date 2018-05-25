<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 22.05.2018
 * Time: 20:02
 */

namespace Modules\Curl;


class Curl
{

    /**
     * @param $url
     * @param $header
     * @param null $post
     * @param null $method GET,POST,PUT,DELETE
     * @return mixed
     */
    public function run($url, $header, $post = null, $method = null)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if(!empty($post))curl_setopt($ch, CURLOPT_POST, true);
        if(!empty($post))curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $response = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $response;
    }

}