<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012-2025 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Sequelize\V7\Model;

use MwbExporter\Formatter\Sequelize\V6\Model\Table as BaseTable;

class Table extends BaseTable
{
    protected function init()
    {
        parent::init();
        $this->attributeFieldKey = 'columnName';
        $this->referencesModelKey = 'tableName';
    }

    protected function getAssociationOne($fk, $as, $inverse = null, $constrained = null)
    {
        return [
            'foreignKey' => [
                'name' => $this->getNaming($fk->getLocal()->getColumnName()),
                'field' => $fk->getLocal()->getColumnName(),
                'allowNull' => !$fk->getLocal()->isNotNull(),
                'onUpdate' => !$constrained ? null : $fk->getParameter('updateRule'),
                'onDelete' => !$constrained ? null : $fk->getParameter('deleteRule'),
            ],
            'sourceKey' => $this->getNaming($fk->getForeign()->getColumnName()),
            'targetKey' => $this->getNaming($fk->getForeign()->getColumnName()),
            'as' => $as,
            'inverse' => $inverse ? ['as' => $inverse] : null,
            'foreignKeyConstraints' => !$constrained ? false : null,
        ];
    }

    protected function getAssociationMany($fk, $as, $constrained = null)
    {
        return [
            'foreignKey' => [
                'name' => $this->getNaming($fk->getLocal()->getColumnName()),
                'field' => $fk->getLocal()->getColumnName(),
                'allowNull' => !$fk->getLocal()->isNotNull(),
                'onUpdate' => !$constrained ? null : $fk->getParameter('updateRule'),
                'onDelete' => !$constrained ? null : $fk->getParameter('deleteRule'),
            ],
            'targetKey' => $this->getNaming($fk->getForeign()->getColumnName()),
            'as' => $as,
            'foreignKeyConstraints' => !$constrained ? false : null,
        ];
    }

    protected function getAssociationThrough($relation)
    {
        return [
            'through' => $relation['reference']->getOwningTable()->getRawTableName(),
            'foreignKey' => [
                'name' => $relation['reference']->getLocal()->getColumnName(),
                'onUpdate' => $relation['reference']->getParameter('updateRule'),
                'onDelete' => $relation['reference']->getParameter('deleteRule'),
            ],
            'targetKey' => $this->getNaming($relation['target']->getForeign()->getColumnName()),
            'as' => $this->pluralize($this->getNaming($relation['refTable']->getModelName(), $this->associationNaming, true)),
        ];
    }
}
