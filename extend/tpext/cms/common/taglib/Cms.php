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

use think\template\TagLib;
use think\Exception;

/**
 * Cms标签库解析类
 */
class Cms extends Taglib
{
    // 标签定义
    protected $tags = [];

    protected $tables = [];

    protected $usedTags = [];

    protected $bindFunctions = false;

    public function __construct($template)
    {
        $this->tags = Table::getTagsList();
        $this->tables = Table::getTables();
        parent::__construct($template);
    }

    public function tagList($tag, $content)
    {
        $table = $tag['table'] ?? '';
        if (!Table::isAllowTable($table)) {
            return "<!--数据表：{$table}未允许使用标签-->";
        }

        $take = $tag['num'] ?? 0;
        $pagesize = $tag['pagesize'] ?? 0;
        $item = !empty($tag['item']) ? $tag['item'] : ($tag['default_item'] ?? 'item');
        $assign = !empty($tag['assign']) ? $tag['assign'] : $table . '_list_' . time();
        $item = ltrim($item, '$');
        $assign = ltrim($assign, '$');
        $cache = explode(',', $tag['cache'] ?? '');
        $cacheKey = empty($cache[0]) ? 'false' : "'" . trim($cache[0]) . "'";
        $cacheTime = intval($cache[1] ?? 360);
        $tagOrder = !empty($tag['order']) ? $tag['order'] : Table::defaultOrder($table);
        $fields = $tag['fields'] ?? Table::defaultFields($table);
        $fields = is_array($fields) ? implode(',', $fields) : $fields;
        $scope = Table::defaultScope($table);
        $dbNameSpace = Processer::getDbNamespace();
        $links = true;
        if (isset($tag['links']) && ($tag['links'] == '0' || $tag['links'] == 'false' || $tag['links'] == 'n' || $tag['links'] == 'no')) {
            $links = false;
        }

        $parseStr = $this->bindKeyValWhere($tag);

        $parseStr .= <<<EOT

        <?php
        \$__page__ = 1;
        \$__render_links__ = '{$links}' == '1';
        \$__has_paginator__ = false;
        \$__take__ = {$take};
        \$__pagesize__ = {$pagesize} ?: (\$__set_pagesize__ ?? 0);
        if(\$__take__ == 0) {
            if(\$__pagesize__ > 0) {
                \$__page__ = isset(\$page) && \$page > 0 ? \$page : 1;
                \$__take__ = \$__pagesize__;
                \$__has_paginator__ = true;
            } else {
                \$__take__ = 10;
            }
        }
        \$__list__ = [];
        \$__order_by__ = '{$table}' =='cms_content' && !empty(\$__set_order_by__) ? \$__set_order_by__ . '{$tagOrder}' : '{$tagOrder}';

        \$__data__ = {$dbNameSpace}::name('{$table}')
            ->where(\$__where__)
            ->whereRaw(\$__where_raw__, \$__where_binds__)
            ->where('{$scope}')
            ->field('{$fields}')
            ->order(\$__order_by__)
            ->limit((\$__page__ - 1) * \$__take__, \$__take__)
            ->cache({$cacheKey}, {$cacheTime}, '{$table}')
            ->select();
        foreach(\$__data__ as \$__d__) {
            \$__list__[] = \\tpext\\cms\\common\\taglib\\Processer::item('{$table}', \$__d__);
        }
        if(\$__has_paginator__) {
            \$__total__ = {$dbNameSpace}::name('{$table}')
                ->where(\$__where__)
                ->whereRaw(\$__where_raw__, \$__where_binds__)
                ->where('{$scope}')
                ->count();
                
            \$__paginator__ = new \\think\\paginator\\driver\\Bootstrap(\$__data__, \$__pagesize__, \$__page__, \$__total__, false, ['path' => \$__set_page_path__ ?? '']);
            \$__links_html__ = \$__paginator__->render();
        }
        ?>
        {volist name="__list__" id="{$item}"}
        {$content}
        {/volist}
        {if condition="\$__has_paginator__ && \$__render_links__ && !empty(\$__links_html__)"}
        {\$__links_html__|raw}
        {elseif condition="\$__has_paginator__"}
        <!-- 未自动输出分页，请在页面需要的位置调用 -->
        {/if}
        {assign name="{$assign}" value="\$__list__" /}
        <?php
        unset(\$__data__, \$__where_raw__, \$__where_binds__, \$__where__, \$__id_key__, \$__id_val__, \$__cid_key__, \$__cid_val__);
        unset(\$__order_by__, \$__paginator__, \$__total__, \$__take__, \$__pagesize__, \$__page__);
        ?>
EOT;
        $this->usedTags[] = $tag;
        return $parseStr;
    }

    public function tagParents($tag, $content)
    {
        $table = $tag['table'] ?? '';
        if (!Table::isAllowTable($table)) {
            return "<!--数据表：{$table}未允许使用标签-->";
        }
        $pid_key = $tag['pid_key'] ?? 'parent_id';
        $id_key = $tag['id_key'] ?? 'id';
        $item = !empty($tag['item']) ? $tag['item'] : ($tag['default_item'] ?? 'item');
        $assign = !empty($tag['assign']) ? $tag['assign'] : $table . '_list_' . time();
        $item = ltrim($item, '$');
        $assign = ltrim($assign, '$');
        $fields = $tag['fields'] ?? Table::defaultFields($table);

        $id_val = '';
        if ($id_key && isset($tag[$id_key]) && $tag[$id_key] !== '') {
            $id_val = trim($tag[$id_key]);
        }

        if ($id_val !== '') {
            if ($id_val[0] == '$' || $id_val[0] == ':') { //解析变量或方法
                $id_val = $this->filterIdVar($id_val);
            } else if (is_int($id_val)) {
                $id_val = "{$id_val}";
            }
        } else {
            $id_val = "\${$id_key}";
        }

        $fields = is_array($fields) ? implode(',', $fields) : $fields;

        $parseStr = <<<EOT
        <?php
        \$__pid_key__ ='{$pid_key}';
        \$__id_key__ = '{$id_key}';
        \$__id_val__ = {$id_val} ?? 0;
        \$__list__ = \\tpext\\cms\\common\\taglib\\Processer::getParents('{$table}', \$__id_val__, \$__id_key__, \$__pid_key__);
        ?>
        {volist name="__list__" id="{$item}"}
        {$content}
        {/volist}
        {assign name="{$assign}" value="\$__list__" /}
        <?php
        unset(\$__pid_key__, \$__id_key__, \$__id_val__);
        ?>
EOT;
        $this->usedTags[] = $tag;
        return $parseStr;
    }

    public function tagGet($tag, $content)
    {
        $table = $tag['table'] ?? '';
        if (!Table::isAllowTable($table)) {
            return "<!--数据表：{$table}未允许使用标签-->";
        }
        $assign = !empty($tag['assign']) ? $tag['assign'] : ($tag['default_assign'] ?? 'data');
        $assign = ltrim($assign, '$');
        $cache = explode(',', $tag['cache'] ?? '');
        $cacheKey = empty($cache[0]) ? 'false' : "'" . trim($cache[0]) . "'";
        $cacheTime = intval($cache[1] ?? 360);
        $order = $tag['order'] ?? '';
        $fields = $tag['fields'] ?? Table::defaultFields($table);
        $fields = is_array($fields) ? implode(',', $fields) : $fields;
        $scope = Table::defaultScope($table);
        $dbNameSpace = Processer::getDbNamespace();

        $parseStr = $this->bindKeyValWhere($tag);

        $parseStr .= <<<EOT
        <?php
        
        \$__detail__ = {$dbNameSpace}::name('{$table}')
            ->where(\$__where__)
            ->whereRaw(\$__where_raw__, \$__where_binds__)
            ->where('{$scope}')
            ->order('{$order}')
            ->field('{$fields}')
            ->cache({$cacheKey}, {$cacheTime}, '{$table}')
            ->find();
        \$__detail__ = \\tpext\\cms\\common\\taglib\\Processer::detail('{$table}', \$__detail__);
        ?>
        {assign name="{$assign}" value="\$__detail__" /}
        {notempty name="{$assign}"}
        {$content}
        {/notempty}
        <?php
        unset(\$__data__, \$__where_raw__, \$__where_binds__, \$__where__, \$__id_key__, \$__id_val__, \$__cid_key__, \$__cid_val__);
        ?>
EOT;
        $this->usedTags[] = $tag;
        return $parseStr;
    }

    /**
     * 引入标签库助手方法
     * 
     * @return string
     */
    public function bindFunctions()
    {
        if ($this->bindFunctions) {
            return '';
        }

        $this->bindFunctions = true;

        $parseStr = <<<EOT

        <?php
        include_once \\tpext\\cms\\common\\Module::getInstance()->getRoot() . 'functions.php';

        ?>
EOT;

        return $parseStr;
    }

    /**
     * 解析标签中的 cid_key, id_key, where 等参数
     * 
     * @param mixed $tag
     * @return string
     */
    protected function bindKeyValWhere($tag)
    {
        $cid_key = $tag['cid_key'] ?? '';
        $id_key = $tag['id_key'] ?? 'id';
        $where = $tag['where'] ?? '1=1';

        $cid_val = '';
        if ($cid_key && isset($tag[$cid_key]) && $tag[$cid_key] !== '') {
            $cid_val = trim($tag[$cid_key]);
        } else if ($cid_key && isset($tag['cid']) && $tag['cid'] !== '') {
            $cid_val = trim($tag['cid']);
        } else if ($cid_key == 'parent_id' && isset($tag['pid']) && $tag['pid'] !== '') {
            $cid_val = trim($tag['pid']);
        }

        if ($cid_val !== '') {
            if ($cid_val[0] == '$' || $cid_val[0] == ':') { //变量或方法
                $cid_val = $this->filterIdVar($cid_val);
            } else if (is_int($cid_val)) {
                $cid_val = "{$cid_val}";
            } else if (preg_match('/^[\d,]+$/', $cid_val)) {
                $cid_val = "'{$cid_val}'";
            } else {
                $whereExp = $this->parseIdVal($cid_key, $cid_val);
                if ($whereExp) {
                    $where .= $whereExp;
                    $cid_val = '0';
                }
            }
        } else if ($cid_key) {
            $cid_val = "(!empty(\${$cid_key}s) ? \${$cid_key}s : (\${$cid_key} ?? 0))";
        } else {
            $cid_val = '0';
        }

        $id_val = '';
        if ($id_key && isset($tag[$id_key]) && $tag[$id_key] !== '') {
            $id_val = trim($tag[$id_key]);
        }
        if ($id_val !== '') {
            if ($id_val[0] == '$' || $id_val[0] == ':') { //解析变量或方法
                $id_val = $this->filterIdVar($id_val);
            } else if (is_int($id_val)) {
                $id_val = "{$id_val}";
            } else if (preg_match('/^[\d,]+$/', $id_val)) {
                $id_val = "'{$id_val}'";
            } else {
                $whereExp = $this->parseIdVal($id_key, $id_val);
                if ($whereExp) {
                    $where .= $whereExp;
                    $id_val = '0';
                }
            }
            $cid_val = '0';
        } else {
            $id_val = '0';
        }

        $binds = '';
        if ($where && $where != '1=1') {
            [$where, $binds] = $this->parseWhere($where, $cid_key); //解析变量
        }

        $parseStr = <<<EOT

        <?php
        \$__where_raw__ = "{$where}";
        \$__where_binds__ = [$binds];
        \$__where__ = [];
        \$__cid_key__ = '{$cid_key}';
        \$__id_key__ = '{$id_key}';

        if(\$__cid_key__) {
            \$__cid_val__ = {$cid_val} ?? 0;
            if(\$__cid_val__) {
                if(is_array(\$__cid_val__) || strstr(\$__cid_val__, ',')) {
                    \$__where__[] = [\$__cid_key__, 'in', \$__cid_val__];
                } else {
                    \$__where__[] = [\$__cid_key__, '=', \$__cid_val__];
                }
            }
        }
        if(\$__id_key__) {
            \$__id_val__ = {$id_val} ?? 0;
            if(\$__id_val__) {
                if(is_array(\$__id_val__) || strstr(\$__id_val__, ',')) {
                    \$__where__[] = [\$__id_key__, 'in', \$__id_val__];
                } else {
                    \$__where__[] = [\$__id_key__, '=', \$__id_val__];
                }
            }
        }
        ?>
EOT;

        return $parseStr;
    }

    /**
     * 解析where中的变量
     *
     * @param string $where
     * @param string $cid_key
     * 
     * @return array
     */
    protected function parseWhere($where, $cid_key)
    {
        $where = $this->whereExp($where, $cid_key);
        $binds = [];
        preg_match_all('/([\'\"])?\%?(\$[a-zA-Z_][a-zA-Z_\.\[\]\'\"]*)\%?\1?/', $where, $matches);
        if (isset($matches[2]) && count($matches[2]) > 0) {
            foreach ($matches[2] as $i => $match) {
                $varName = preg_replace('/\W/is', '_', $match) . $i;
                $replace = ':' . $varName;
                $bind = '';
                if (strpos($match, '.') !== false) {
                    $names = explode('.', $match);
                    $first = array_shift($names);
                    $bind = $first . '[\'' . implode('\'][\'', $names) . '\']';
                } else {
                    $bind = $match;
                }
                $find = $match;
                if ($matches[0][$i][0] == '%') {
                    $find = '%' . $find;
                    $bind = "'%' . {$bind}";
                }
                if ($matches[0][$i][-1] == '%') {
                    $find = $find . '%';
                    $bind = "{$bind} . '%'";
                }
                $binds[] = "'{$varName}' => {$bind}";
                $where = substr_replace($where, $replace, strpos($where, $find), strlen($find));
            }
        }
        return [$where, implode(', ', $binds)];
    }

    /**
     * 解析id值
     *
     * @param string $where
     * @return string
     */
    protected function parseIdVal($idKey, $idVal)
    {
        $op = '=';
        if (preg_match('/^\(?\d,[,\d]+\)?$/is', $idVal)) {
            $op = 'in';
            $idVal = trim($idVal, ',');
        } else if (preg_match('/^(in|not\s*in)\s*\(?(.+?)\)?$/is', $idVal, $mch)) {
            $op = $mch[1];
            $idVal = '(' . trim($mch[2]) . ')';
        } else if (preg_match('/^(between|not\s*between)\s*\(?(.+?)\)?$/is', $idVal, $mch)) {
            $op = $mch[1];
            $idVal = trim($mch[2]);
            if (!strstr($idVal, 'and')) {
                $idVal = str_replace(',', ' and ', $idVal);
            }
        } else if (preg_match('/^(>|=|<|>=|<=|<>)\s*(.+?)$/is', $idVal, $mch)) {
            $op = $mch[1];
            $idVal = trim($mch[2]);
        } else if (preg_match('/^(gt|eq|lt|egt|elt|neq|!=)\s*(.+?)$/is', $idVal, $mch)) {
            $op = $mch[1];
            $idVal = trim($mch[2]);
        } else if (preg_match('/^(like|not\s*like)\s+(.+)$/is', $idVal, $mch)) {
            $op = $mch[1];
            $idVal = trim($mch[2], "'\"");
            if (!strstr($idVal, '%')) {
                $mch[2] = '%' . $idVal . '%';
            }
        }

        return " and {$idKey} {$op} {$idVal}";
    }

    /**
     * 替换表达式
     *
     * @param string $where
     * @param string $cid_key
     * 
     * @return string
     */
    protected function whereExp($where, $cid_key)
    {
        //替换where中的cid语法糖为真实字段
        if ($cid_key && strstr($where, 'cid')) {
            $where = preg_replace('/(\bcid\s+)(gt|eq|lt|egt|elt|neq|not\s*in|like|not\s*like|between|not\s*between)/is', $cid_key . '$2', $where);
            $where = preg_replace('/(\bcid\s*)(\<|\>|=|!=)/is', $cid_key . '$2', $where);
        }
        if ($cid_key == 'parent_id' && strstr($where, 'pid')) {
            $where = preg_replace('/(\bpid\s+)(gt|eq|lt|egt|elt|neq|not\s*in|like|not\s*like|between|not\s*between)/is', $cid_key . '$2', $where);
            $where = preg_replace('/(\bpid\s*)(\<|\>|=|!=)/is', $cid_key . '$2', $where);
        }
        //替换表达式
        $where = str_ireplace(['egt', 'elt', 'neq'], ['>=', '<=', '<>'], $where);
        $where = str_ireplace(['gt', 'eq', 'lt', '!='], ['>', '=', '<', '<>'], $where);
        $where = str_ireplace(['notbetween', 'notin', 'notlike'], ['not between', 'not in', 'not like'], $where);

        return $where;
    }

    /**
     * 安全转换变量
     * 
     * @param string $var
     * @return string
     */
    protected function filterIdVar($var)
    {
        $var = $this->autoBuildVar($var);
        if (preg_match('/\$_(SERVER|REQUEST|GET|POST|COOKIE|SESSION)/i', $var) || preg_match('/app\(/i', $var)) {
            $var = "filter_var({$var}, FILTER_VALIDATE_INT)";
        }
        return $var;
    }

    public function __call($name, $arguments = [])
    {
        if (preg_match('/^tag(\w+@\w+)$/i', $name, $mchs) && count($arguments) == 2) {
            $tagName = strtolower($mchs[1]);
            if ('use@functions' == $tagName) {
                return $this->bindFunctions();
            }
            $tag = $arguments[0];
            $content = $arguments[1];
            $tagArr = explode('@', $tagName);
            foreach ($this->tables as $table => $info) {
                if (empty($info['tag_name'])) {
                    continue;
                }

                if ($info['tag_name'] . '@list' == $tagName) {
                    $tag['table'] = $table;
                    $tag['tag_name'] = $tagName;
                    $tag['default_item'] = $tagArr[0];
                    $tag['id_key'] = $info['id_key'] ?? 'id';
                    $tag['cid_key'] = $info['cid_key'] ?? '';
                    $tag['pid_key'] = $info['pid_key'] ?? '';
                    return $this->tagList($tag, $content);
                }
                if ($info['tag_name'] . '@parents' == $tagName) {
                    $tag['table'] = $table;
                    $tag['tag_name'] = $tagName;
                    $tag['default_item'] = $tagArr[0];
                    $tag['id_key'] = $info['id_key'] ?? 'id';
                    $tag['cid_key'] = $info['cid_key'] ?? '';
                    $tag['pid_key'] = $info['pid_key'] ?? '';
                    return $this->tagParents($tag, $content);
                }
                if ($info['tag_name'] . '@get' == $tagName) {
                    $tag['table'] = $table;
                    $tag['tag_name'] = $tagName;
                    $tag['default_assign'] = $tagArr[0];
                    $tag['id_key'] = $info['id_key'] ?? 'id';
                    $tag['cid_key'] = $info['cid_key'] ?? '';
                    return $this->tagGet($tag, $content);
                }
                if ($info['tag_name'] . '@prev' == $tagName) {
                    $tag['table'] = $table;
                    $tag['tag_name'] = $tagName;
                    $tag['assign'] = empty($tag['assign']) ? 'prev' : $tag['assign'];
                    $tag['default_assign'] = $tagArr[0];
                    $tag['id_key'] = $info['id_key'] ?? 'id';
                    $where = '';
                    if (empty($tag['where'])) {
                        $cid_key = $info['cid_key'] ?? '';
                        if ($cid_key) {
                            $where = $cid_key . "=\${$tagArr[0]}." . $cid_key;
                        } else {
                            $where = '1=1';
                        }
                    } else {
                        $where = $tag['where'];
                    }

                    $id = $tag[$tag['id_key']] ?? '$id';
                    $order = $tag['order'] ?? Table::defaultOrder($table);
                    $sort = $tag['sort'] ?? "\${$tagArr[0]}.sort";
                    $fields = [];
                    $orders = explode(',', $order);
                    foreach ($orders as $sod) {
                        if (stripos($sod, 'desc') !== false) {
                            $fields[] = preg_replace('/^(\w+\s+)desc$/is', '$1asc', $sod);
                        } else {
                            $fields[] = preg_replace('/^(\w+)(?:\s+asc)?$/is', '$1 desc', $sod);
                        }
                    }
                    $first = preg_replace('/\s*(?:desc|asc)/', '', $orders[0]);
                    $isDesc = stripos($orders[0], 'desc') !== false;
                    $cmp = $isDesc ? '>' : '<';
                    $tag['where'] = "{$tag['id_key']} != {$id} and {$first} {$cmp} {$sort} and " . $where;
                    $tag['order'] = implode(',', $fields);
                    $tag[$tag['id_key']] = '';
                    return $this->tagGet($tag, $content);
                }
                if ($info['tag_name'] . '@next' == $tagName) {
                    $tag['table'] = $table;
                    $tag['tag_name'] = $tagName;
                    $tag['assign'] = empty($tag['assign']) ? 'next' : $tag['assign'];
                    $tag['default_assign'] = $tagArr[0];
                    $tag['id_key'] = $info['id_key'] ?? 'id';
                    $where = '';
                    if (empty($tag['where'])) {
                        $cid_key = $info['cid_key'] ?? '';
                        if ($cid_key) {
                            $where = $cid_key . "=\${$tagArr[0]}." . $cid_key;
                        } else {
                            $where = '1=1';
                        }
                    } else {
                        $where = $tag['where'];
                    }
                    $id = $tag[$tag['id_key']] ?? '$id';
                    $order = $tag['order'] ?? Table::defaultOrder($table);
                    $sort = $tag['sort'] ?? "\${$tagArr[0]}.sort";
                    $orders = explode(',', $order);
                    $first = preg_replace('/\s*(?:desc|asc)/', '', $orders[0]);
                    $isDesc = stripos($orders[0], 'desc') !== false;
                    $cmp = $isDesc ? '<' : '>';
                    $tag['where'] = "{$tag['id_key']} != {$id} and {$first} {$cmp} {$sort} and " . $where;
                    $tag['order'] = $order;
                    $tag[$tag['id_key']] = '';
                    return $this->tagGet($tag, $content);
                }
            }
            throw new Exception("未知标签：{$tagName}");
        }

        throw new Exception('Call to undefined method : ' . $name);
    }
}
