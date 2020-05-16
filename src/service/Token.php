<?php

// +----------------------------------------------------------------------
// | Library for Sveil
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 KuangJia Inc.
// +----------------------------------------------------------------------
// | Website: https://sveil.com
// +----------------------------------------------------------------------
// | License ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee：https://gitee.com/boy12371/think-lib
// | github：https://github.com/boy12371/think-lib
// +----------------------------------------------------------------------

namespace sveil\service;

use sveil\Service;

/**
 * Form Token Management Service
 *
 * Class Token
 * @author Richard <richard@sveil.com>
 * @package sveil\service
 */
class Token extends Service
{

    /**
     * Get the current request token
     * @return array|string
     */
    public function getInputToken()
    {
        return $this->app->request->header('user-token-csrf', input('_token_', ''));
    }

    /**
     * Verify that the form token is valid
     *
     * @param string $token Form token
     * @param string $node Authorized node
     * @return boolean
     */
    public function checkFormToken($token = null, $node = null)
    {
        if (is_null($token)) {
            $token = $this->getInputToken();
        }

        if (is_null($node)) {
            $node = NodeService::instance()->getCurrent();
        }

        // Read the cache and check if it is valid
        $cache = $this->app->session->get($token);

        if (empty($cache['node']) || empty($cache['time']) || empty($cache['token'])) {
            return false;
        }

        if ($cache['token'] !== $token || $cache['time'] + 600 < time() || $cache['node'] !== $node) {
            return false;
        }

        return true;
    }

    /**
     * Clean up form CSRF information
     *
     * @param string $token
     * @return $this
     */
    public function clearFormToken($token = null)
    {

        if (is_null($token)) {
            $token = $this->getInputToken();
        }

        $this->app->session->delete($token);

        return $this;
    }

    /**
     * Generate form CSRF information
     *
     * @param null|string $node
     * @return array
     */
    public function buildFormToken($node = null)
    {

        list($token, $time) = [uniqid('csrf'), time()];

        foreach ($this->app->session->get() as $key => $item) {
            if (stripos($key, 'csrf') === 0 && isset($item['time'])) {
                if ($item['time'] + 600 < $time) {
                    $this->clearFormToken($key);
                }

            }
        }

        $data = ['node' => NodeService::instance()->fullnode($node), 'token' => $token, 'time' => $time];
        $this->app->session->set($token, $data);

        return $data;
    }

}