<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
 * Copyright (c) 2013 WitteStier <development@wittestier.nl>
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

namespace MwbExporter\Formatter\Node\Sequelize5\Model;

use MwbExporter\Configuration\Comment as CommentConfiguration;
use MwbExporter\Configuration\Indentation as IndentationConfiguration;
use MwbExporter\Configuration\M2MSkip as M2MSkipConfiguration;
use MwbExporter\Formatter\DatatypeConverterInterface;
use MwbExporter\Helper\Comment;
use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Object\JS;
use MwbExporter\Writer\WriterInterface;

class Table extends BaseTable
{
    /**
     * Get JSObject.
     *
     * @param mixed $content    Object content
     * @param bool  $multiline  Multiline result
     * @param bool  $raw        Is raw object
     * @return \MwbExporter\Object\JS
     */
    public function getJSObject($content, $multiline = true, $raw = false)
    {
        /** @var \MwbExporter\Configuration\Indentation $indentation */
        $indentation = $this->getConfig(IndentationConfiguration::class);

        return new JS($content, [
            'indentation' => $indentation->getIndentation(1),
            'multiline' => $multiline,
            'raw' => $raw,
        ]);
    }

    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isExternal():
                return self::WRITE_EXTERNAL;
            case $this->getConfig(M2MSkipConfiguration::class)->getValue() && $this->isManyToMany():
                return self::WRITE_M2M;
            default:
                $writer->open($this->getTableFileName());
                $this->writeBody($writer);
                $writer->close();

                return self::WRITE_OK;
        }
    }

    /**
     * Write model body code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Node\Sequelize5\Model\Table
     */
    protected function writeBody(WriterInterface $writer)
    {
        $writer
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if ($_this->getConfig(CommentConfiguration::class)->getValue()) {
                    $writer
                        ->write($_this->getFormatter()->getComment(Comment::FORMAT_JS))
                        ->write('')
                    ;
                }
            })
            ->write("module.exports = function(sequelize, DataTypes) {")
            ->indent()
            ->write("return sequelize.define('%s', %s, %s);", $this->getModelName(), $this->asModel(), $this->asOptions())
            ->outdent()
            ->write("}")
        ;

        return $this;
    }

    protected function asOptions()
    {
        /** @var \MwbExporter\Formatter\Node\Formatter $formatter */
        $formatter = $this->getFormatter();
        $result = array_merge([
            'tableName' => $this->getRawTableName(),
            'indexes' => count($indexes = $this->getIndexes()) ? $indexes : null,
        ], $formatter->getTableProp());

        return $this->getJSObject($result);
    }

    protected function asModel()
    {
        $result = $this->getFields();

        return $this->getJSObject($result);
    }

    /**
     * Get model fields.
     *
     * @return array
     */
    protected function getFields()
    {
        $result = [];
        foreach ($this->getColumns() as $column) {
            $type = $this->getFormatter()->getDatatypeConverter()->getType($column);
            if (DatatypeConverterInterface::DATATYPE_DECIMAL == $column->getColumnType()) {
                $type .= sprintf('(%s, %s)', $column->getParameters()->get('precision'), $column->getParameters()->get('scale'));
            } elseif (($len = $column->getLength()) > 0) {
                $type .= sprintf('(%s)', $len);
            }
            $c = [];
            $c['type'] = $this->getJSObject(sprintf('DataTypes.%s', $type ? $type : 'STRING.BINARY'), true, true);
            if ($column->isPrimary()) {
                $c['primaryKey'] = true;
            }
            if ($column->isAutoIncrement()) {
                $c['autoIncrement'] = true;
            } elseif ($column->isNotNull()) {
                $c['allowNull'] = false;
            }
            $result[$column->getColumnName()] = $c;
        }

        return $result;
    }

    protected function getIndexes()
    {
        $result = [];
        foreach ($this->getIndices() as $index) {
            if ($index->isIndex() || $index->isUnique()) {
                $result[] = [
                    'name' => $index->getName(),
                    'fields' => $this->getJSObject($index->getColumnNames(), false),
                    'unique' => $index->isUnique() ? true : null,
                ];
            }
        }

        return $result;
    }
}
