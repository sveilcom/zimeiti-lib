<?php

// +----------------------------------------------------------------------
// | Library for sveil/zimeiti-cms
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 KuangJia Inc.
// +----------------------------------------------------------------------
// | Website: https://sveil.com
// +----------------------------------------------------------------------
// | License ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee：https://gitee.com/sveil/zimeiti-lib
// | github：https://github.com/sveil/zimeiti-lib
// +----------------------------------------------------------------------

namespace sveil\rep\wechat\wemini;

use sveil\exception\InvalidResponseException;
use sveil\exception\LocalCacheException;
use sveil\rep\WeChat;

/**
 * Modify server address
 *
 * Class Domain
 * @author Richard <richard@sveil.com>
 * @package sveil\rep\wechat\wemini
 */
class Domain extends WeChat
{

    /**
     * 1. Set the applet server domain name
     *
     * @param string $action add,delete,set,get。There is no need to fill in four domain name fields when the parameter is get
     * @param array $data Required parameter data
     * @return array
     * @throws InvalidResponseException
     * @throws LocalCacheException
     */
    public function modifyDomain($action, $data = [])
    {

        $data['action'] = $action;
        $url            = 'https://api.weixin.qq.com/wxa/modify_domain?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());

        return $this->httpPostForJson($url, $data, true);
    }

    /**
     * 2. Set the applet business domain name (only for the third-party to call the applet)
     *
     * @param string $action add, delete, set, get. When the parameter is get, there is no need to fill in the webview domain field.
     * If there is no action field parameter, then by default, the applet service domain name registered by the third party of
     * the open platform is added to the authorized applet
     *
     * @param string $webviewdomain Applet business domain name, this field is not needed when the action parameter is get
     * @return array
     * @throws InvalidResponseException
     * @throws LocalCacheException
     */
    public function setWebViewDomain($action, $webviewdomain)
    {

        $url = 'https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());

        return $this->httpPostForJson($url, ['action' => $action, 'webviewdomain' => $webviewdomain], true);
    }

}
