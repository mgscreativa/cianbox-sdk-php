<?php
/**
 * Cianbox Api Integration Library
 * @author Martin Briglia, MGS Creativa
 * @url http://www.mgscreativa.com
 * @copyright Copyright (C) 2020 MGS Creativa - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

class CianboxApi
{
    const VERSION = '0.1.0';

    private $account;
    private $user;
    private $password;
    private $access_token;
    private $expires_in;
    private $refresh_token;

    function __construct()
    {
        $i = func_num_args();

        if ($i !== 3) {
            throw new CianboxApiException('Argumentos inválidos. Use ACCOUNT, USER y PASSWORD');
        }

        if ($i == 3) {
            $this->account = func_get_arg(0);
            $this->user = func_get_arg(1);
            $this->password = func_get_arg(2);
        }
    }

    public function post_auth_credentials()
    {
        if (isset($this->access_token) && !is_null($this->access_token)) {
            return $this->access_token;
        }

        $data = array(
            'user' => $this->user,
            'password' => $this->password,
            'app_name' => 'Cianbox PHP SDK v' . CianboxApi::VERSION,
            'app_code' => 'cianbox-php-sdk-v' . str_replace('.', '-', CianboxApi::VERSION),
        );

        $result = CBRestClient::post(array(
            'uri' => '/auth/credentials',
            'data' => $data,
            'headers' => array(
                'content-type' => 'application/json',
            ),
            'account' => $this->account,
        ));

        if ($result['status'] != 200) {
            throw new CianboxApiException('Error al obtener el access token', $result['status']);
        }

        $this->access_token = $result['response']['body']['access_token'];
        $this->expires_in = $result['response']['body']['expires_in'];
        $this->refresh_token = $result['response']['body']['refresh_token'];

        return $this->access_token;
    }

    public function post_auth_refresh($refreshToken = null)
    {
        if (is_null($refreshToken)) {
            throw new CianboxApiException('No se especifico el refresh token', 400);
        }

        $data = array(
            'refresh_token' => $refreshToken,
        );

        $result = CBRestClient::post(array(
            'uri' => '/auth/refresh',
            'data' => $data,
            'headers' => array(
                'content-type' => 'application/json',
            ),
            'account' => $this->account,
        ));

        if ($result['status'] != 'ok') {
            throw new CianboxApiException('Error al obtener el access token', $result['status']);
        }

        $this->access_token = $result['body']['access_token'];
        $this->expires_in = $result['body']['expires_in'];

        return $this->access_token;
    }

    public function get_clientes_lista($params = null)
    {
        if (is_null($params) || !is_array($params)) {
            throw new CianboxApiException('EL parámetro de búsqueda debe ser un array', 400);
        }

        $params["access_token"] = $this->post_auth_credentials();

        $request = array(
            'uri' => '/clientes/lista',
            'params' => $params,
            'account' => $this->account,
        );

        $response = CBRestClient::get($request);

        return $response['response']['body'];
    }

    public function get_estados_pedidos_lista()
    {
        $params = array(
            "access_token" => $this->post_auth_credentials(),
        );

        $request = array(
            'uri' => '/pedidos/estados/lista',
            'params' => $params,
            'account' => $this->account,
        );

        $response = CBRestClient::get($request);

        return $response['response']['body'];
    }

    public function get_productos_lista($params = null)
    {
        if (is_null($params) || !is_array($params)) {
            throw new CianboxApiException('EL parámetro de búsqueda debe ser un array', 400);
        }

        $params["access_token"] = $this->post_auth_credentials();

        $request = array(
            'uri' => '/productos/lista',
            'params' => $params,
            'account' => $this->account,
        );

        $response = CBRestClient::get($request);

        return $response['response']['body'];
    }

    public function get_sucursales()
    {
        $params = array(
            "access_token" => $this->post_auth_credentials(),
        );

        $request = array(
            'uri' => '/productos/sucursales',
            'params' => $params,
            'account' => $this->account,
        );

        $response = CBRestClient::get($request);

        return $response['response']['body'];
    }

    public function post_pedidos_alta($pedido = null)
    {
        if (is_null($pedido) || !is_array($pedido)) {
            throw new CianboxApiException('Pedido vacío o mal formateado', 400);
        }

        $request = array(
            'uri' => '/pedidos/alta',
            'params' => array(
                'access_token' => $this->post_auth_credentials(),
            ),
            'data' => $pedido,
            'account' => $this->account,
        );

        $response = CBRestClient::post($request);

        return $response['response']['body'];
    }

    /**
     * Generic resource get
     *
     * @param $request
     * @param $params
     * @param $authenticate = true
     *
     * @return array(json)
     * @throws Exception si se encuentra un error en la solucitud.
     */
    public function get($request, $params = null, $authenticate = true)
    {
        if (is_string($request)) {
            $request = array(
                'uri' => $request,
                'params' => $params,
                'authenticate' => $authenticate,
                'account' => $this->account,
            );
        }

        $request['params'] = isset ($request['params']) && is_array($request['params']) ? $request['params'] : array();

        if (!isset ($request['authenticate']) || $request['authenticate'] !== false) {
            $request['params']['access_token'] = $this->post_auth_credentials();
        }

        $result = CBRestClient::get($request);

        return $result;
    }

    /**
     * Generic resource post
     *
     * @param $request
     * @param $data
     * @param $params
     *
     * @return array(json)
     * @throws Exception si se encuentra un error en la solucitud.
     */
    public function post($request, $data = null, $params = null)
    {
        if (is_string($request)) {
            $request = array(
                'uri' => $request,
                'data' => $data,
                'params' => $params,
                'account' => $this->account,
            );
        }

        $request['params'] = isset ($request['params']) && is_array($request['params']) ? $request['params'] : array();

        if (!isset ($request['authenticate']) || $request['authenticate'] !== false) {
            $request['params']['access_token'] = $this->post_auth_credentials();
        }

        $result = CBRestClient::post($request);

        return $result;
    }

    /**
     * Generic resource put
     *
     * @param $request
     * @param $data
     * @param $params
     *
     * @return array(json)
     * @throws Exception si se encuentra un error en la solucitud.
     */
    public function put($request, $data = null, $params = null)
    {
        if (is_string($request)) {
            $request = array(
                'uri' => $request,
                'data' => $data,
                'params' => $params,
                'account' => $this->account,
            );
        }

        $request['params'] = isset ($request['params']) && is_array($request['params']) ? $request['params'] : array();

        if (!isset ($request['authenticate']) || $request['authenticate'] !== false) {
            $request['params']['access_token'] = $this->post_auth_credentials();
        }

        $result = CBRestClient::put($request);

        return $result;
    }

    /**
     * Generic resource delete
     *
     * @param $request
     * @param $params
     *
     * @return array(json)
     * @throws Exception si se encuentra un error en la solucitud.
     */
    public function delete($request, $params = null)
    {
        if (is_string($request)) {
            $request = array(
                'uri' => $request,
                'params' => $params,
                'account' => $this->account,
            );
        }

        $request['params'] = isset ($request['params']) && is_array($request['params']) ? $request['params'] : array();

        if (!isset ($request['authenticate']) || $request['authenticate'] !== false) {
            $request['params']['access_token'] = $this->post_auth_credentials();
        }

        $result = CBRestClient::delete($request);

        return $result;
    }
}


/**
 * Cianbox cURL RestClient
 */
class CBRestClient
{
    const API_BASE_URL = 'https://cianbox.org/{account}/api/v2';

    private static function build_request($request)
    {
        if (!extension_loaded('curl')) {
            throw new CianboxApiException('cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.');
        }

        if (!isset($request['method'])) {
            throw new CianboxApiException('No HTTP METHOD specified');
        }

        if (!isset($request['account']) || empty($request['account'])) {
            throw new CianboxApiException('No account specified');
        }

        if (!isset($request['uri'])) {
            throw new CianboxApiException('No URI specified');
        }

        $headers = array('accept: application/json');
        $json_content = true;
        $form_content = false;
        $default_content_type = true;

        if (isset($request['headers']) && is_array($request['headers'])) {
            foreach ($request['headers'] as $h => $v) {
                $h = strtolower($h);
                $v = strtolower($v);

                if ($h == 'content-type') {
                    $default_content_type = false;
                    $json_content = $v == 'application/json';
                    $form_content = $v == 'application/x-www-form-urlencoded';
                }

                array_push($headers, $h . ': ' . $v);
            }
        }
        if ($default_content_type) {
            array_push($headers, 'content-type: application/json');
        }

        $connect = curl_init();

        curl_setopt($connect, CURLOPT_USERAGENT, 'Cianbox PHP SDK v' . CianboxApi::VERSION);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($connect, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request['method']);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        if (isset ($request['params']) && is_array($request['params']) && count($request['params']) > 0) {
            $request['uri'] .= (strpos($request['uri'], '?') === false) ? '?' : '&';
            $request['uri'] .= self::build_query($request['params']);
        }

        $url = str_replace('{account}', $request['account'], self::API_BASE_URL);
        curl_setopt($connect, CURLOPT_URL, $url . $request['uri']);

        if (isset($request['data'])) {
            if ($json_content) {
                if (gettype($request['data']) == 'string') {
                    json_decode($request['data'], true);
                } else {
                    $request['data'] = json_encode($request['data']);
                }

                if (function_exists('json_last_error')) {
                    $json_error = json_last_error();
                    if ($json_error != JSON_ERROR_NONE) {
                        throw new CianboxApiException('JSON Error [{$json_error}] - Data: ' . $request['data']);
                    }
                }
            } else if ($form_content) {
                $request['data'] = self::build_query($request['data']);
            }

            curl_setopt($connect, CURLOPT_POSTFIELDS, $request['data']);
        }

        return $connect;
    }

    private static function exec($request)
    {
        $connect = self::build_request($request);

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === false) {
            throw new CianboxApiException (curl_error($connect));
        }

        $response = array(
            'status' => $api_http_code,
            'response' => json_decode($api_result, true)
        );

        if ($response['response']['status'] == 'error') {
            $message = $response['response']['status'] . ': ' . $response['response']['message'];
            if (isset ($response['response']['cause'])) {
                if (isset ($response['response']['cause']['code']) && isset ($response['response']['cause']['description'])) {
                    $message .= ' - ' . $response['response']['cause']['code'] . ': ' . $response['response']['cause']['description'];
                } else if (is_array($response['response']['cause'])) {
                    foreach ($response['response']['cause'] as $cause) {
                        $message .= ' - ' . $cause['code'] . ': ' . $cause['description'];
                    }
                }
            }

            throw new CianboxApiException ($message, 400);
        }

        curl_close($connect);

        return $response;
    }

    private static function build_query($params)
    {
        if (function_exists('http_build_query')) {
            return http_build_query($params, '', '&');
        } else {
            foreach ($params as $name => $value) {
                $elements[] = '{$name}=' . urlencode($value);
            }

            return implode('&', $elements);
        }
    }

    public static function get($request)
    {
        $request['method'] = 'GET';

        return self::exec($request);
    }

    public static function post($request)
    {
        $request['method'] = 'POST';

        return self::exec($request);
    }

    public static function put($request)
    {
        $request['method'] = 'PUT';

        return self::exec($request);
    }

    public static function delete($request)
    {
        $request['method'] = 'DELETE';

        return self::exec($request);
    }
}

class CianboxApiException extends Exception
{
    public function __construct($message, $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
