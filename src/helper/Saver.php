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

namespace sveil\helper;

use sveil\Helper;
use think\db\Query;
use think\Exception;
use think\exception\PDOException;

/**
 * Save data assistant
 *
 * Class Saver
 * @author Richard <richard@sveil.com>
 * @package sveil\helper
 */
class Saver extends Helper
{

    /**
     * Form extension data
     * @var array
     */
    protected $data;

    /**
     * Additional form update conditions
     * @var array
     */
    protected $where;

    /**
     * Data object primary key name
     * @var array|string
     */
    protected $field;

    /**
     * Data object primary key value
     * @var string
     */
    protected $value;

    /**
     * Logic initialization
     *
     * @param Query|string $dbQuery
     * @param array $data Form extension data
     * @param string $field Data object primary key name
     * @param array $where Additional form update conditions
     * @return boolean
     * @throws Exception
     * @throws PDOException
     */
    public function init($dbQuery, $data = [], $field = '', $where = [])
    {

        $this->where = $where;
        $this->query = $this->buildQuery($dbQuery);
        $this->data  = empty($data) ? $this->app->request->post() : $data;
        $this->field = empty($field) ? $this->query->getPk() : $field;
        $this->value = $this->app->request->post($this->field, null);

        // Primary key restriction processing
        if (!isset($this->where[$this->field]) && is_string($this->value)) {
            $this->query->whereIn($this->field, explode(',', $this->value));
            if (isset($this->data)) {
                unset($this->data[$this->field]);
            }

        }

        // Pre-callback processing
        if (false === $this->controller->callback('_save_filter', $this->query, $this->data)) {
            return false;
        }

        // Perform update operation
        $result = $this->query->where($this->where)->update($this->data) !== false;

        // Result callback processing
        if (false === $this->controller->callback('_save_result', $result)) {
            return $result;
        }

        // Reply to front-end results
        if ($result !== false) {
            $this->controller->success(lang('think_library_save_success'), '');
        } else {
            $this->controller->error(lang('think_library_save_error'));
        }

    }

}
