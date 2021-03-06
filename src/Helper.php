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

namespace sveil;

use think\App;
use think\Container;
use think\Db;
use think\db\Query;

/**
 * Assistant base class
 *
 * Abstract Class Helper
 * @author Richard <richard@sveil.com>
 * @package sveil
 */
abstract class Helper
{

    /**
     * Current application container
     * @var App
     */
    public $app;

    /**
     * Database instance
     * @var Query
     */
    public $query;

    /**
     * Current controller instance
     * @var Controller
     */
    public $controller;

    /**
     * Helper constructor
     * @param App $app
     * @param Controller $controller
     */
    public function __construct(App $app, Controller $controller)
    {
        $this->app        = $app;
        $this->controller = $controller;
    }

    /**
     * Get database objects
     *
     * @param string|Query $dbQuery
     * @return Query
     */
    protected function buildQuery($dbQuery)
    {
        return is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
    }

    /**
     * Instance object reflection
     *
     * @return static
     */
    public static function instance()
    {
        return Container::getInstance()->invokeClass(static::class);
    }

}
