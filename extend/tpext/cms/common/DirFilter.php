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

namespace tpext\cms\common;

class DirFilter extends \RecursiveFilterIterator
{
    protected $defaultExclude = array(
        '.svn',
        '.git',
        '.vscode',
        'node_modules'
    );

    protected $exclude = array();

    public function __construct(\RecursiveIterator $iterator, $exclude = [])
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function accept(): bool
    {
        $filename = strtolower($this->current()->getFilename());

        return !in_array(
            $filename,
            $this->defaultExclude
        ) &&
            !in_array(
                $filename,
                $this->exclude
            );
    }

    /**
     * Undocumented function
     *
     * @return \RecursiveFilterIterator|null
     */
    public function getChildren(): ?\RecursiveFilterIterator
    {
        return new self($this->getInnerIterator()->getChildren(), $this->exclude);
    }
}
