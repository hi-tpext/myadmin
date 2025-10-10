<?php

// +----------------------------------------------------------------------
// | tpext.cms
// +----------------------------------------------------------------------
// | Copyright (c) tpext.cms All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lhy <ichynul@163.com>
// +----------------------------------------------------------------------

namespace tpext\cms\common\taglib;

use tpext\common\ExtLoader;
use tpext\cms\common\Module;

class Table
{
    /**
     * 表标签映射
     * ---------
     * tag_name：标签名称
     * id_key：主键名
     * cid_key：分类键名
     * default_order：默认排序
     * default_fields：默认字段
     * default_scope：默认查询条件
     * pid_key：上级键名
     * ---------
     * @var array 
     */
    protected static $tables = [
        'cms_channel' => [
            'tag_name' => 'channel',
            'id_key' => 'id',
            'cid_key' => 'parent_id',
            'default_order' => 'sort asc,id asc',
            'default_fields' => '*',
            'default_scope' => 'is_show=1 and delete_time is null',
            'pid_key' => 'parent_id',
        ],
        'cms_content' => [
            'tag_name' => 'content',
            'id_key' => 'id',
            'cid_key' => 'channel_id',
            'default_order' => 'sort desc,publish_time desc,id desc',
            'default_fields' => '*',
            'default_scope' => 'is_show=1 and delete_time is null',
            'pid_key' => '',
        ],
        'cms_banner' => [
            'tag_name' => 'banner',
            'id_key' => 'id',
            'cid_key' => 'position_id',
            'default_order' => 'sort asc,id asc',
            'default_fields' => '*',
            'default_scope' => 'is_show=1',
            'pid_key' => '',
        ],
        'cms_position' => [
            'tag_name' => 'position',
            'id_key' => 'id',
            'cid_key' => '',
            'default_order' => 'sort asc,id asc',
            'default_fields' => '*',
            'default_scope' => 'is_show=1',
            'pid_key' => '',
        ],
        'cms_tag' => [
            'tag_name' => 'tag',
            'id_key' => 'id',
            'cid_key' => '',
            'default_order' => 'sort asc,id asc',
            'default_fields' => '*',
            'default_scope' => 'is_show=1',
            'pid_key' => '',
        ]
    ];

    protected static $init = false;

    protected static $allowTables = [];

    /**
     * 扩展或修改标签
     *
     * @param array $tables
     * @return void
     */
    public static function extend($tables)
    {
        foreach ($tables as $table => $info) {
            if (!isset(static::$tables[$table])) {
                static::$tables[$table] = $info;
            } else {
                static::$tables[$table] = array_merge(static::$tables[$table], $info);
            }
        }
    }

    /**
     * 添加允许的表
     * tables（包含扩展的）中的默认允许，其他的表设为允许后可以使用{list}或{detail}标签
     * 
     * @return void
     */
    public static function addAllowTables($allowTables)
    {
        static::$allowTables = array_merge(static::$allowTables, $allowTables);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public static function getTagsList()
    {
        $tags = [
            // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次 expression 允许表达式
            'list' => ['attr' => 'table,num,pagesize,where,order,fields,item,assign,cache,links,simple'],
            'parents' => ['attr' => 'table,num,until,where,order,fields,assign,id_key,pid_key'],
            //
            'get' => ['attr' => 'table,where,order,fields,assign,cache', 'close' => 0],
            'prev' => ['attr' => 'table,where,order,fields,assign,cache,sort', 'close' => 0],
            'next' => ['attr' => 'table,where,order,fields,assign,cache,sort', 'close' => 0],
            'show@vars' => ['attr' => '', 'close' => 0],
        ];

        $tables = static::getTables();

        foreach ($tables as $info) {
            if (empty($info['tag_name'])) {
                continue;
            }

            $listAttr = 'num,pagesize,where,order,fields,item,assign,cache,links,simple';
            $parentsAttr = 'num,until,where,order,fields,assign';
            $getAttr = 'where,order,fields,assign,cache';
            $parentAttr = 'where,order,fields,assign,cache';

            if (!empty($info['id_key'])) {
                $listAttr .= ',' . $info['id_key'];
                $getAttr .= ',' . $info['id_key'];
                $parentsAttr .= ',' . $info['id_key'];
                $parentAttr .= ',' . $info['id_key'];
            }

            //expression为true的作用是attr可以为空
            $tags[$info['tag_name'] . '@get'] = ['attr' => $getAttr, 'close' => 0, 'expression' => true];
            $tags[$info['tag_name'] . '@prev'] = ['attr' => $getAttr, 'close' => 0, 'expression' => true];
            $tags[$info['tag_name'] . '@next'] = ['attr' => $getAttr, 'close' => 0, 'expression' => true];

            if (!empty($info['cid_key'])) {
                $listAttr .= ',' . $info['cid_key'];
                if ($info['cid_key'] != 'cid') {
                    $listAttr .= ',cid';
                }
                if ($info['cid_key'] == 'parent_id') {
                    $listAttr .= ',pid';
                }
            }

            $tags[$info['tag_name'] . '@list'] = ['attr' => $listAttr, 'expression' => true];

            if (!empty($info['pid_key'])) {
                $parentsAttr .= ',' . $info['pid_key'];
                $parentAttr .= ',' . $info['pid_key'];
                $tags[$info['tag_name'] . '@parents'] = ['attr' => $parentsAttr, 'expression' => true];
            }
        }

        return $tags;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public static function getTables()
    {
        if (!static::$init) {
            ExtLoader::trigger('tpext_cms_get_tables'); //监听此事件以扩展标签
            $tables = Module::getInstance()->config('allow_tables', '');
            if (!empty($tables)) {
                static::$allowTables = array_merge(static::$allowTables, explode(',', $tables));
            }
            static::$init = true;
        }
        return static::$tables;
    }

    /**
     * 是否允许数据表使用标签
     *
     * @param string $table
     * @return boolean
     */
    public static function isAllowTable($table)
    {
        if (stristr($table, 'admin')) {
            return false;
        }

        $tables = static::getTables();
        return in_array($table, array_keys($tables)) || in_array($table, static::$allowTables);
    }

    /**
     * 列表默认排序
     *
     * @param string $table
     * @return string
     */
    public static function defaultOrder($table)
    {
        $tables = static::getTables();
        if (isset($tables[$table])) {
            return $tables[$table]['default_order'] ?? 'id desc';
        }
        return 'id desc';
    }

    /**
     * 默认查询字段
     *
     * @param string $table
     * @return string
     */
    public static function defaultFields($table)
    {
        $tables = static::getTables();
        if (isset($tables[$table]) && !empty($tables[$table]['default_fields'])) {
            return $tables[$table]['default_fields'];
        }
        return '*';
    }

    /**
     * 默认查询条件，字符串形式，如： 'is_show=1'。不支持变量，如： 'name=' . input('name')。
     *
     * @param string $table
     * @return string
     */
    public static function defaultScope($table)
    {
        $tables = static::getTables();
        if (isset($tables[$table])) {
            return $tables[$table]['default_scope'] ?? '';
        }
        return '';
    }
}
