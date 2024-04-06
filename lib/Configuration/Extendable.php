<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sequelize\Configuration;

use MwbExporter\Configuration\Configuration;

/**
 * Allow table attributes and options to be extended in a such ways to provide
 * extra definitions without modifying generated model files (and thus, being able
 * to regenerate models) `Sequelize 6+`.
 *
 * Example scenario:
 *
 * _`User.js` (generated)_
 *
 * ```javascript
 * const { DataTypes } = require('sequelize');
 *
 * module.exports = (sequelize, attrCallback, optCallback) => {
 *     let attributes = {
 *         lastName: {
 *             type: DataTypes.STRING(200),
 *             field: 'name',
 *             allowNull: false,
 *             defaultValue: ''
 *         },
 *         firstName: {
 *             ...
 *         },
 *         ...
 *     }
 *     let options = {
 *         ...
 *     }
 *     if (typeof attrCallback === 'function') {
 *         attributes = attrCallback(attributes);
 *     }
 *     if (typeof optCallback === 'function') {
 *         options = optCallback(options);
 *     }
 *     const Model = sequelize.define('User', attributes, options);
 *
 *     Model.associate = () => {
 *         ...
 *     }
 *
 *     return Model;
 * }
 * ```
 *
 * _`extensions/User.js` (manually created)_
 *
 * ```javascript
 * const { DataTypes } = require('sequelize');
 *
 * module.exports = sequelize => attributes => {
 *     return Object.assign(attributes, {
 *         lastName: {
 *             get() {
 *                 const rawValue = this.getDataValue('lastName');
 *                 return rawValue ? rawValue.toUpperCase() : null;
 *             }
 *         },
 *         fullName: {
 *             type: DataTypes.VIRTUAL,
 *             get() {
 *                 return `${this.firstName} ${this.lastName}`
 *             },
 *             set(value) {
 *                 throw new Error('Do not try to set the `fullName` value!')
 *             }
 *         }
 *     });
 * } 
 * ```
 *
 * Initialization can be achieved like this:
 *
 * ```javascript
 * const Sequelize = require('sequelize');
 *
 * const sequelize = new Sequelize({...});
 * const userExtension = require('./path/to/extensions/User')(sequelize);
 * const User = require('./path/to/User')(sequelize, userExtension, options => {
 *     return Object.assign(options, {
 *         hooks: {
 *             beforeCreate: (instance, options) => {
 *                 ...
 *             }
 *         }
 *     });
 * });
 * ...
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 * @config extendableModelDefinition|injectExtendFunction
 * @label Enable extendable features in model definition
 */
class Extendable extends Configuration
{
    protected function initialize()
    {
        $this->category = 'sequelizeConfiguration';
        $this->defaultValue = true;
    }
}
