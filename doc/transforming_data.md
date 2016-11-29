
# Transforming data

When simple transformations are needed eq. for generating an import identifier this can be done  by the 
*TransformingProviderDecorator*.

In the following table there is no unique identifier for the entity, but when updating existing records we need
to uniquely identify them.

The source is an Excel file containing the following fields:

|cat_id|source_id|strasse       |plz  |stadt    |phone_number |
|------|---------|--------------|-----|---------|-------------|
|10    |100      |Gardenstreet 1|23445|Baltimore|+494080021345|
|10    |120      |Rosestreet 212|24103|Hamburg  |+494080021346|
|20    |100      |Bakerstreet 80|43534|London   |+494080021347|

```php
$mapping = array(
	'import-identifier' => '${row["cat_id"]~"-"~row["source_id"]}',
	'street' => 'strasse',
	'zip' => 'plz',
	'city' => 'stadt',
	'phonenumber' => 'phone_number'
)
```

We now have a unique identifier by concatenating the **cat_id** and the **source_id**. Expressions inside of **${}** are parsed
by the [symfony expression language](http://symfony.com/doc/current/components/expression_language/index.html).

Decorators can be stacked together with the dataProvider by passing them as constructor argument.

```php
$excelDataprovider = new ExcelDataProvider();
$excelDataProvider->setOptions(array(...));

$dataProvider = new TransformingProviderDecorator($excelDataprovider);
```
Now the **$dataProvider** behaves like a normal dataProvider but will transform each row before it is returned. The 
resulting array will contain all keys from the mappping.

```php
array (
  'import-identifier' => '10-100',
  'street' => 'Gardenstreet 1',
  'zip' => '23445',
  'city' => 'Baltimore',
  'phonenumber' => '+494080021345'
)
```
When using the entity importer it is possible to rewrite all the keys to match the propertyname in the resulting class 
making transformation by the customProperty() method nearly obsolet.

## Helper
The expressions inside the mapping can use the provided helper for the expression language. Take a look at the 
**\Networkteam\Import\DataProvider\TransformingHelper**. All defined functions can be used, it is populated to the 
context as **helper**. So by calling **helper.md5(row['street'])** the md5 value of the field **street** is returned.

A custom helper can be set by calling **setExpressionHelper()** on the **TransformingDataProvider**.
