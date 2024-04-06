# Sequelize Model (v7) Configuration

Auto generated at 2024-04-07T01:23:12+0700.

## Global Configuration

  * `language`

    Language detemines which language used to transform singular and plural words
    used in certains other schema like Doctrine.

    Valid values: `none`, `english`, `french`, `norwegian-bokmal`, `portuguese`, `spanish`,
    `turkish`

    Default value: `none`

  * `useTab` (alias: `useTabs`)

    Use tab as blank instead of space in generated files.

    Default value: `false`

  * `indentation`

    Number of spaces used as blank in generated files.

    Default value: `4`

  * `eolDelimiter` (alias: `eolDelimeter`)

    End of line (EOL) delimiter detemines the end of line in generated files.

    Valid values: `win`, `unix`

    Default value: `win`

  * `filename`

    The output filename format, use the following tag `%schema%`, `%table%`, `%entity%`, and
    `%extension%` to allow the filename to be replaced with contextual data.

    Default value: `%entity%.%extension%`

  * `backupExistingFile`

    Perform backup existing file before writing generated file.

    Backup file will have an extension of *.bak.

    Default value: `true`

  * `headerFile`

    Include file as header in the generated files. It will be wrapped as a
    comment by choosen formatter. This configuration useful as for example
    to include notice to generated files such as license file.

    Default value: `blank`

  * `addGeneratorInfoAsComment`

    Include generated information as a comment in generated files.

    Default value: `true`

  * `namingStrategy`

    Naming strategy detemines how objects, variables, and methods name will be generated.

    Valid values: `as-is`, `camel-case`, `pascal-case`

    Default value: `as-is`

  * `identifierStrategy`

    Determines how identifier like table name will be treated for generated
    entity/model name. Supported identifier strategies are `fix-underscore`
    which will fix for double underscore to single underscore, and `none` which
    will do nothing.

    Valid values: `none`, `fix-underscore`

    Default value: `none`

  * `cleanUserDatatypePrefix` (alias: `asIsUserDatatypePrefix`)

    Clean user datatype matched the prefix specified.

    Default value: `blank`

  * `enhanceManyToManyDetection`

    Allows generate additional model for many to many relations.

    Default value: `true`

  * `sortTableAndView` (alias: `sortTablesAndViews`)

    Perform table name and view name sorting before generating files.

    Default value: `true`

  * `skipManyToManyTables`

    Skip many to many table generation.

    Default value: `true`

  * `skipPluralNameChecking`

    Skip checking the plural name of model and leave as is, useful for non English
    table names.

    Default value: `false`

  * `exportOnlyInCategory` (alias: `exportOnlyTableCategorized`)

    Some models may have category defined in comment, process only if it is matched.

    Default value: `blank`

  * `logToConsole`

    Activate logging to console.

    Default value: `false`

  * `logFile`

    Activate logging to filename.

    Default value: `blank`

  * `useLoggedStorage`

    Useful to use the generated files content for further processing.

    Default value: `false`

## Sequelize Global Configuration

  * `npmPackageName`

    Sequelize NPM package name. For Sequelize 6 package name is `sequelize` while
    the next version is `@sequelize/core`.

    Default value: `@sequelize/core`

  * `commonTableProp`

    Provides a JSON file used as table options override.

    Default table options defined as follows `{timestamps: false, underscored: false,
    syncOnAssociation: false}`

    Default value: `blank`

  * `useSemicolon`

    Whether or not to add semicolon to line ending (standard Eslint compliant).

    Default value: `true`

  * `generateForeignKeysField` (alias: `generateForeignKeysFields`)

    Whether or not to generate foreign keys fields `Sequelize 6+`.

    Default value: `true`

  * `generateAssociationMethod`

    Generate association method to define associations between models.
    Each model then has a `associate()` method which can be called
    to associate the models `Sequelize 6+`.

    To use the association is described as follows:

    ```javascript
    const { Sequelize } = require('sequelize');

    const sequelize = new Sequelize({...});
    const MyModel1 = sequelize.import('./path/to/MyModel');
    const MyModel2 = sequelize.import('./path/to/MyMode2');
    ...

    MyModel1.associate();
    MyModel2.associate();
    ...
    ```

    Default value: `false`

  * `alwaysGenerateAssociationAlias`

    Allows to always generate association alias (`as`) when association is enabled.
    Association alias by default will be omitted if the generated model is same as
    referenced model `Sequelize 6+`.

    Default value: `false`

  * `extendableModelDefinition` (alias: `injectExtendFunction`)

    Allow table attributes and options to be extended in a such ways to provide
    extra definitions without modifying generated model files (and thus, being able
    to regenerate models) `Sequelize 6+`.

    Example scenario:

    _`User.js` (generated)_

    ```javascript
    const { DataTypes } = require('sequelize');

    module.exports = (sequelize, attrCallback, optCallback) => {
        let attributes = {
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
        }
        let options = {
            ...
        }
        if (typeof attrCallback === 'function') {
            attributes = attrCallback(attributes);
        }
        if (typeof optCallback === 'function') {
            options = optCallback(options);
        }
        const Model = sequelize.define('User', attributes, options);

        Model.associate = () => {
            ...
        }

        return Model;
    }
    ```

    _`extensions/User.js` (manually created)_

    ```javascript
    const { DataTypes } = require('sequelize');

    module.exports = sequelize => attributes => {
        return Object.assign(attributes, {
            lastName: {
                get() {
                    const rawValue = this.getDataValue('lastName');
                    return rawValue ? rawValue.toUpperCase() : null;
                }
            },
            fullName: {
                type: DataTypes.VIRTUAL,
                get() {
                    return `${this.firstName} ${this.lastName}`
                },
                set(value) {
                    throw new Error('Do not try to set the `fullName` value!')
                }
            }
        });
    }
    ```

    Initialization can be achieved like this:

    ```javascript
    const Sequelize = require('sequelize');

    const sequelize = new Sequelize({...});
    const userExtension = require('./path/to/extensions/User')(sequelize);
    const User = require('./path/to/User')(sequelize, userExtension, options => {
        return Object.assign(options, {
            hooks: {
                beforeCreate: (instance, options) => {
                    ...
                }
            }
        });
    });
    ...
    ```

    Default value: `true`

## Sequelize 6 Configuration

  * No configuration available


## Sequelize 7 Configuration

  * No configuration available


