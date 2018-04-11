# Bego

Bego is a PHP library that makes working DynamoDb tables simpler.

## Working with models ##
Create your table's model class
```
<?php

namespace App\MyTables;

class Music extends Bego\Model
{
    /**
     * Table name
     */
    protected $_name = 'Music';

    /**
     * Table's partition key attribute
     */
    protected $_partition = 'Artist';

    /**
     * Table's sort key attribute
     */
    protected $_sort = 'SongTitle';

    /**
     * List of indexes available for this table
     */
    protected $_indexes = [
        'My-Global-Index' => ['key' => 'Timestamp'],
        'My-Local-Index' => []
    ];
}
```

Now the table is ready to be used throughout your app...
```
$config = [
    'version' => 'latest',
    'region'  => 'eu-west-1',
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$db = new Bego\Database(
    new Aws\DynamoDb\DynamoDbClient($config), new Aws\DynamoDb\Marshaler()
);

$table = $db->table(new App\MyTables\Music());
```

## Create an item ##
```
/* Create and persist a new item */
$item = $table->put([
    'Id'        => uniqueid(), 
    'Artitst'   => 'Bob Dylan',
    'SongTitle' => 'How many roads'
]);
```

## Get an item ##
```
/* Fetch an item */
$item = $table->fetch(
    'Bob Dylan', 'How many roads'
);

/* Perform a consistent read */
$item = $table->fetch(
    'Bob Dylan', 'How many roads', true
);

echo $item->get('Id');
```

## Update an item ##
```
/* Update an item */
$item->set('Year', 1966);

$result = $table->update($item);

$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

foreach ($results as $item) {
    $item->set('Year', $item->get('Year') + 1);
    $table->update($item);
}
```

## Batch update items ##
```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

foreach ($results as $item) {
    $item->set('Year', $item->get('Year') + 1);
}

$result = $table->update($results);

echo $result->getConsumedCapacity();
```

## Working with queries ##
You can Query any DynamoDb table or secondary index, provided that it has a composite primary key (partition key and sort key)
```
/* Query the table */
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

/* Query a global index */
$results = $table->query('My-Global-Index')
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

/* Query a local index */
$results = $table->query('My-Local-Index')
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 
```

### Key condition and filter expressions ###
Multiple key condition / filter expressions can be added. DynamoDb applies key conditions to the query but filters are applied to the query results
```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', 'begins_with', 'How')
    ->filter('Year', '=' , '1966')
    ->fetch(); 
```

### Descending Order ###
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$results = $table->query()
    ->reverse()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->fetch(); 
```

### Working with result sets ###
The result set object implements the Iterator interface and canned by used straight way. It provived some handy methods as well.
```
/* Execute query and return first page of results */
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->fetch(); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}

echo "{$results->count()} items in result set\n";
echo "{$results->getScannedCount()} items scanned in query\n";

$item = $results->first();

$item = $results->last();

$item = $results->item(3); //3rd item

```


### Consistent Reads ###
DynamoDb performs eventual consistent reads by default. For strongly consistent reads set the consistent() flag:
```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->consistent()
    ->fetch(); 
```

### Limiting Results ###
DynamoDb allows you to limit the number of items returned in the result. Note that this limit is applied to the key conidtion only. DynamoDb will apply filters after the limit is imposed on the result set:
```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->limit(100)
    ->fetch();
```

### Paginating ###
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work:
```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads');

/* Option 1: Get all items no matter the cost */
$results = $query->fetch(false);

/* Option 2: Execute up to 10 queries and return the aggregrated results */
$results = $query->fetch(10); 
```

In some cases one may want to paginate accross multiple hops;

```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads');

/* First Hop: Get one page */
$results = $query->fetch(1);
$pointer = $results->getLastEvaluatedKey();

/* Second Hop: Get one more page, continueing from previous request */
$results = $query->fetch(1, $pointer); 
```

### Capacity Units Consumed ###
DynamoDb can calculate the total number of read capacity units for every query. This can be enabled using the consumption() flag:

```
$results = $table->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->consumption()
    ->fetch();

echo $results->getCapacityUnitsConsumed();
```