# listdata

Yii CActiveRecord behavior to generate list data arrays.

## Attachment

```php
'listDataBehavior' => array(
    'class' => 'ext.behaviors.listdata.ListDataBehavior',
    'idAttribute' => 'id',
    'labelAttribute' => 'name',
    'orderByLabel' => true,
    'useModels' => false,
),
```

## PhpDoc example

You can copy this part to your model class phpdoc to enable code autocompletion.

```php
/**
 * @see ListDataBehavior
 * @property ListDataBehavior $listDataBehavior
 * @property-read array $listData
 * @method array getListData($condition = array(), $labelAttribute = null)
 * @method array arrayListData($items, $labelAttribute = null)
 */
```
