<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Node;

use MwbExporter\Formatter\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        $this->register([
            static::DATATYPE_TINYINT => 'TINYINT',
            static::DATATYPE_SMALLINT => 'SMALLINT',
            static::DATATYPE_MEDIUMINT => 'MEDIUMINT',
            static::DATATYPE_INT => 'INTEGER',
            static::DATATYPE_BIGINT => 'BIGINT',
            static::DATATYPE_FLOAT => 'FLOAT',
            static::DATATYPE_DOUBLE => 'DOUBLE',
            static::DATATYPE_DECIMAL => 'DECIMAL',
            static::DATATYPE_CHAR => 'STRING',
            static::DATATYPE_NCHAR => 'STRING',
            static::DATATYPE_VARCHAR => 'STRING',
            static::DATATYPE_NVARCHAR => 'STRING',
            static::DATATYPE_JSON => 'JSON',
            static::DATATYPE_BINARY => 'BLOB',
            static::DATATYPE_VARBINARY => 'BLOB',
            static::DATATYPE_TINYTEXT => 'TEXT(\'tiny\')',
            static::DATATYPE_TEXT => 'TEXT',
            static::DATATYPE_MEDIUMTEXT => 'TEXT(\'medium\')',
            static::DATATYPE_LONGTEXT => 'TEXT(\'long\')',
            static::DATATYPE_TINYBLOB => 'BLOB(\'tiny\')',
            static::DATATYPE_BLOB => 'BLOB',
            static::DATATYPE_MEDIUMBLOB => 'BLOB(\'medium\')',
            static::DATATYPE_LONGBLOB => 'BLOB(\'long\')',
            static::DATATYPE_DATETIME => 'DATE',
            static::DATATYPE_DATETIME_F => 'DATE',
            static::DATATYPE_DATE => 'DATE',
            static::DATATYPE_DATE_F => 'DATE',
            static::DATATYPE_TIME => 'DATE',
            static::DATATYPE_TIME_F => 'DATE',
            static::DATATYPE_TIMESTAMP => 'DATE',
            static::DATATYPE_TIMESTAMP_F => 'DATE',
            static::DATATYPE_YEAR => 'INTEGER',
            static::DATATYPE_GEOMETRY => 'GEOMETRY', //??
            static::DATATYPE_LINESTRING => 'STRING',
            static::DATATYPE_POLYGON => 'STRING', //??
            static::DATATYPE_MULTIPOINT => 'STRING', //??
            static::DATATYPE_MULTILINESTRING => 'STRING', //??
            static::DATATYPE_MULTIPOLYGON => 'STRING', //??
            static::DATATYPE_GEOMETRYCOLLECTION => 'STRING', //??
            static::DATATYPE_BIT => 'INTEGER',
            static::DATATYPE_ENUM => 'ENUM',
            static::DATATYPE_SET => 'STRING',
            static::USERDATATYPE_BOOLEAN => 'BOOLEAN',
            static::USERDATATYPE_BOOL => 'BOOLEAN',
            static::USERDATATYPE_FIXED => 'INTEGER',
            static::USERDATATYPE_FLOAT4 => 'INTEGER',
            static::USERDATATYPE_FLOAT8 => 'INTEGER',
            static::USERDATATYPE_INT1 => 'INTEGER',
            static::USERDATATYPE_INT2 => 'INTEGER',
            static::USERDATATYPE_INT3 => 'INTEGER',
            static::USERDATATYPE_INT4 => 'INTEGER',
            static::USERDATATYPE_INT8 => 'INTEGER',
            static::USERDATATYPE_INTEGER => 'INTEGER',
            static::USERDATATYPE_LONGVARBINARY => 'STRING',
            static::USERDATATYPE_LONGVARCHAR => 'STRING',
            static::USERDATATYPE_LONG => 'INTEGER',
            static::USERDATATYPE_MIDDLEINT => 'INTEGER',
            static::USERDATATYPE_NUMERIC => 'INTEGER',
            static::USERDATATYPE_DEC => 'INTEGER',
            static::USERDATATYPE_CHARACTER => 'STRING',
        ]);
    }
}
