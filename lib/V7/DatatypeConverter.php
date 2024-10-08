<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012-2024 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sequelize\V7;

use MwbExporter\Formatter\Sequelize\Configuration\Dialect as DialectConfiguration;
use MwbExporter\Formatter\Sequelize\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        parent::setup();
        $this->register([
            static::DATATYPE_DATE => 'DATEONLY',
            static::DATATYPE_DATE_F => 'DATEONLY',
            static::DATATYPE_TIME => 'TIME',
            static::DATATYPE_TIME_F => 'TIME',
        ]);
    }

    public function transformDataType($key, $dataType)
    {
        // @see https://sequelize.org/docs/v7/models/data-types/
        $dialect = $this->formatter->getConfig(DialectConfiguration::class)->getValue();
        if ($dialect === DialectConfiguration::SQLITE) {
            // SQLite doesn't support blob size
            if (in_array($key, [static::DATATYPE_TINYBLOB, static::DATATYPE_MEDIUMBLOB, static::DATATYPE_LONGBLOB])) {
                $dataType = $this->dataTypes[static::DATATYPE_BLOB];
            }
            // SQLite doesn't support text size
            if (in_array($key, [static::DATATYPE_TINYTEXT, static::DATATYPE_MEDIUMTEXT, static::DATATYPE_LONGTEXT])) {
                $dataType = $this->dataTypes[static::DATATYPE_TEXT];
            }
        }

        return $dataType;
    }
}
