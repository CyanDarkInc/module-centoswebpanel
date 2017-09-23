<?php
/**
 * CentOS WebPanel API.
 *
 * @package blesta
 * @subpackage blesta.components.modules.centoswebpanel
 * @copyright Copyright (c) 2010, Phillips Data, Inc.
 * @license http://www.blesta.com/license/ The Blesta License Agreement
 * @link http://www.blesta.com/ Blesta
 */
class CentoswebpanelApi
{
    /**
     * @var string The server hostname
     */
    private $hostname;

    /**
     * @var string The CentOS WebPanel api key
     */
    private $key;

    /**
     * @var bool Use ssl in all the api requests
     */
    private $use_ssl;

    /**
     * Initializes the class.
     *
     * @param mixed $hostname The CentOS WebPanel hostname or IP Address
     * @param mixed $key The api key
     * @param mixed $use_ssl True to connect to the api using SSL
     */
    public function __construct($hostname, $key, $use_ssl = false)
    {
        $this->hostname = $hostname;
        $this->key = $key;
        $this->use_ssl = $use_ssl;
    }

    /**
     * Send a request to the CentOS WebPanel API.
     *
     * @param string $function Specifies the api function to invoke
     * @param array $params The parameters to include in the api
     * @return array An array containing the api response
     */
    public function apiRequest($function, array $params = [])
    {
        // Set api url
        $protocol = ($this->use_ssl ? 'https' : 'http');
        $port = ($this->use_ssl ? '2031' : '2030');

        $url = $protocol . '://' . $this->hostname . ':' . $port . '/api/?key=' . $this->key . '&api=' . $function . '&' . http_build_query($params);

        // Send request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = $this->parseResponse(curl_exec($ch));
        curl_close($ch);

        return $data;
    }

    /**
     * Parse the returned response.
     *
     * @param string $response The api response
     * @return array An array with the parsed response
     */
    private function parseResponse($response)
    {
        // Unfortunately the CentOS WebPanel API does not return parseable data
        // such as JSON or XML, it only returns a human-readable text.
        // Using the returned text we will try to build a parseable response.
        $possible_results = [
            'OK',
            'IP removed from all block lists',
            'Account Removal Script Completed!'
        ];

        $response = trim($response);
        $success = false;

        foreach ($possible_results as $result) {
            if (strpos($response, $result) !== false) {
                $success = true;
            }
        }

        return [
            'success' => $success,
            'message' => $response,
            'code' => ($success ? '200' : '500')
        ];
    }

    /**
     * Creates a new account in the server.
     *
     * @param array $params An array contaning the following arguments:
     *  - domain: The account domain name
     *  - username: The account username
     *  - password: The account password
     *  - package: The package ID to assign to the account
     *  - email: The client email address
     *  - inode: The account inodes limit
     *  - nofile: The maximum number of files that can host the account
     *  - nproc: The maximum number of process that can run simultaneously
     * @return array An array containing the request response
     */
    public function createAccount($params)
    {
        return $this->apiRequest('account_new', $params);
    }

    /**
     * Removes an existing account from the server.
     *
     * @param string $username Specifies the username of the account
     * @return array An array containing the request response
     */
    public function removeAccount($username)
    {
        return $this->apiRequest('account_remove', ['username' => $username]);
    }

    /**
     * Suspend an existing account from the server.
     *
     * @param string $username Specifies the username of the account
     * @return array An array containing the request response
     */
    public function suspendAccount($username)
    {
        return $this->apiRequest('account_suspend', ['username' => $username]);
    }

    /**
     * Unsuspends an existing account from the server.
     *
     * @param string $username Specifies the username of the account
     * @return array An array containing the request response
     */
    public function unsuspendAccount($username)
    {
        return $this->apiRequest('account_unsuspend', ['username' => $username]);
    }

    /**
     * Unblock IP address in CSF firewall.
     *
     * @param string $ip_address The IP address to unblock
     * @return array An array containing the request response
     */
    public function unblockIp($ip_address)
    {
        return $this->apiRequest('unblock_ip', ['user_ip' => $ip_address]);
    }

    /**
     * Check if an account exists.
     *
     * @param string $username The username of the account
     * @return bool True if the account exists, false otherwise
     */
    public function accountExists($username)
    {
        $account = $this->createAccount([
            'domain' => $username . '.com',
            'username' => $username,
            'password' => base64_encode(mt_rand()),
            'package' => 1,
            'email' => $username . '@' . $username . '.com',
            'inode' => 10000,
            'nofile' => 100,
            'nproc' => 25
        ]);

        if ($account['success']) {
            // Delete account
            $this->removeAccount($username);

            return false;
        }

        return true;
    }

    /**
     * Get the client IP address.
     *
     * @return string The client IP address
     */
    public function getClientIp()
    {
        $ip_address = '';

        if (getenv('HTTP_CLIENT_IP')) {
            $ip_address = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip_address = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip_address = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip_address = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip_address = getenv('HTTP_FORWARDED');
        } else {
            $ip_address = getenv('REMOTE_ADDR');
        }

        return $ip_address;
    }
}
