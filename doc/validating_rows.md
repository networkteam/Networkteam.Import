
## Validating rows

In special cases when the imported data is very unclean it is useful to ignore rows or validate them before continuing 
with the import. In this case the **\Networkteam\Import\DataProvider\RowValidationDecorator** is useful. It depends on a
validator which then decides if the row is valid. Because of this architecture only the validator needs to be changed 
when the source changes but the Decorator chain stays the same.
 
 
|source_id|street        |zip      |city       |phone_number |
|---------|--------------|---------|-----------|-------------|
|100      |Gardenstreet 1| 23445   | Baltimore |+494080021345|
|120      | <empty>      | <empty> | <empty>   |+494080021346|
|140      |Bakerstreet 80| 43534   | London    | <empty>     |
 
```php
$dataProvider = ...

$notEmptyValidator = new NotEmptyRowValidator();
$notEmptyValidator->setConfiguration(array(
	'ignoreFields' => array('phone_number')
));

$rowValidator = new RowValidationDecorator($dataProvider);
$rowValidator->setValidator($notEmptyValidator);

// now use the $rowValidator as the dataProvider
$someImporter = new SomeImporter($rowValidator);
```

This configuration will skip the second row but return the first and third row. A validator must implement the 
**\Networkteam\Import\Validation\RowValidationInterface**, the isValid method gets the current row passed. To get good 
fieldnames it`s a good idea to put the row validator behind the 
**\Networkteam\Import\DataProvider\TransformingProviderDecorator**. This assures static fieldnames when writing a 
validator which needs access to specific fields like in this case *phone_number*.