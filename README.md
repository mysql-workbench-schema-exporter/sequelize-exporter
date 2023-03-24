# README

![Build Status](https://github.com/mysql-workbench-schema-exporter/node-exporter/actions/workflows/continuous-integration.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/mysql-workbench-schema-exporter/node-exporter/v/stable.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/node-exporter)
[![Total Downloads](https://poser.pugx.org/mysql-workbench-schema-exporter/node-exporter/downloads.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/node-exporter) 
[![License](https://poser.pugx.org/mysql-workbench-schema-exporter/node-exporter/license.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/node-exporter)

This is an exporter to convert [MySQL Workbench](http://www.mysql.com/products/workbench/) Models (\*.mwb) to Nodejs Sequelize Schema.

## Prerequisites

  * PHP 7.2+
  * Composer to install the dependencies

## Installation

```
php composer.phar require --dev mysql-workbench-schema-exporter/node-exporter
```

This will install the exporter and also require [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter).

You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

## Formatter Setup Options

Additionally to the [common options](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#configuring-mysql-workbench-schema-exporter) of mysql-workbench-schema-exporter these options are supported:

### Sequelize 5

Currently, no special options can be configured for Sequelize Model.

### Sequelize 6

  * `useSemicolon`

    Whether or not to add semicolons to ends of lines (standard Eslint compliance).

    Default is `true`.
  
  * `generateAssociationMethod`

    Generate an association method to define associations between models.

    You may instantiate your model this way :

    ```javascript
    const Sequelize = require('sequelize');

    const sequelize = new Sequelize({...});
    const MyModel1 = sequelize.import('./path/to/MyModel');
    const MyModel2 = sequelize.import('./path/to/MyMode2');
    ...

    MyModel1.associate();
    MyModel2.associate();
    ...

    ```
    Default is `false`.

  * `generateForeignKeysFields`

    Whether or not to generate foreign keys fields.
    
    You could want to delegate it to association method (one could experiment relations creation order problems when foreign key field is present in definition).

    Default is `true`.

  * `injectExtendFunction`

    Injects an `extend` functions to models in order to provide extra definitions whitout modifying generated model files (and thus, being able to regenerate models).

    Example :

    **`User.js`**
    ```javascript
    const { DataTypes, Model } = require('sequelize')

    class User extends Model {
    }

    module.exports = (sequelize, extend) => {
      User.init(extend({
        ...
        lastName: {
          type: DataTypes.STRING(200),
          field: 'name',
          allowNull: false,
          defaultValue: ''
        },
        firstName: {
          ...
        },
        ...
      }), {
        ...
      })

      User.associate = () => {
        ...
      }

      return User
    }
    ```

    **`extensions/User.js`**
    ```javascript
    const deepmerge = require('deepmerge')
    const { DataTypes } = require('sequelize')

    module.exports = sequelize => function (baseDefinition) {
      return deepmerge(baseDefinition, {
        lastName: {
          get() {
            const rawValue = this.getDataValue('lastName');
            return rawValue ? rawValue.toUpperCase() : null;
          }
        },
        fullName: {
          type: DataTypes.VIRTUAL(DataTypes.STRING, ['firstName', 'lastName']),
          get() {
            return `${this.firstName} ${this.lastName}`
          },
          set(value) {
            throw new Error('Do not try to set the `fullName` value!')
          }
        }
      }, { clone: false })
    } 
    ```

    Initialization can be achieved like this :
    ```javascript
    const Sequelize = require('sequelize');

    const sequelize = new Sequelize({...});
    const extendUser = require('./path/to/extensions/User')(sequelize);
    const User = require('./path/to/User')(sequelize, extendUser);
    ...
    ```

## Command Line Interface (CLI)

See documentation for [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#command-line-interface-cli)

## Nodejs Usage Example

### Sequelize 5

```javascript
const Sequelize = require('sequelize');

const sequelize = new Sequelize({...});
const MyModel = sequelize.import('./path/to/MyModel');

// do something with MyModel
MyModel.findOne({...}).then((res) => {...});
```

### Sequelize 6

```javascript
const Sequelize = require('sequelize');

const sequelize = new Sequelize({...});
const MyModel = require('./path/to/MyModel')(sequelize);

// do something with MyModel
MyModel.findOne({...}).then((res) => {...});
```

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
