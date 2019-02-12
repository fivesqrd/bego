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

## Instantiating a table ##
Instantiate the tables you need throughout your app...
```
$config = [
    'version' => '2012-08-10',
    'region'  => 'eu-west-1',
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$db = new Bego\Database(
    new Aws\DynamoDb\DynamoDbClient($config), new Aws\DynamoDb\Marshaler()
);

$music = $db->table(new App\MyTables\Music());
```

## Working with queries ##
You can Query any DynamoDb table or secondary index, provided that it has a composite primary key (partition key and sort key)
```
/* Query the table */
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

/* Query a global index */
$results = $music->query('My-Global-Index')
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

/* Query a local index */
$results = $music->query('My-Local-Index')
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 
```

### Key condition and filter expressions ###
Multiple key condition / filter expressions can be added. DynamoDb applies key conditions to the query but filters are applied to the query results
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', 'begins_with', 'How')
    ->filter('Year', '=' , '1966')
    ->fetch(); 
```

### Descending Order ###
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$results = $music->query()
    ->reverse()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->fetch(); 
```

### Working with result sets ###
The result set object implements the Iterator interface and canned by used straight way. It provived some handy methods as well.
```
/* Execute query and return first page of results */
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->fetch(); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}

echo "{$results->count()} items in result set\n";
echo "{$results->getScannedCount()} items scanned in query\n";
echo "{$results->getQueryCount()} trips to the database\n";
echo "{$results->getQueryTime()} total execution time (seconds)\n";

$item = $results->first();

$item = $results->last();

//Get the 3rd item
$item = $results->item(3); 

//Extract one attribute from all items
$allTitles = $results->attribute('SongTitle'); 

//Aggregegate one attribute for all items
$totalSales = $results->sum('Sales'); 

```

### Consistent Reads ###
DynamoDb performs eventual consistent reads by default. For strongly consistent reads set the consistent() flag:
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->consistent()
    ->fetch(); 
```

### Limiting Results ###
DynamoDb allows you to limit the number of items returned in the result. Note that this limit is applied to the key conidtion only. DynamoDb will apply filters after the limit is imposed on the result set:
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->limit(100)
    ->fetch();
```

### Paginating ###
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work:
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads');

/* Option 1: Get one page orf results only (default) */
$results = $query->fetch();

/* Option 2: Execute up to 10 queries */
$results = $query->fetch(10); 

/* Option 3: Get all items in the dataset no matter the cost */
$results = $query->fetch(null);
```

In some cases one may want to paginate accross multiple hops;

```
$results = $music->query()
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
$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->consumption()
    ->fetch();

echo $results->getCapacityUnitsConsumed();
```

## Performing a scan ##
Basic table scan's are supported. Filter expressions, results and pagination work the same as with queries
```
/* Scan the table */
$results = $table->scan()
    ->filter('Artist', '=', $artist)
    ->consistent()
    ->consumption()
    ->limit(100)
    ->fetch();
```

```
/* Scan the secondary index */
$results = $table->scan('My-Global-Index')
    ->filter('Artist', '=', $artist)
    ->fetch();
```

## Create an item ##
```
/* Create and persist a new item */
$item = $music->put([
    'Id'        => uniqid(), 
    'Artitst'   => 'Bob Dylan',
    'SongTitle' => 'How many roads'
]);
```

## Get an item ##
```
/* Fetch an item */
$item = $music->fetch(
    'Bob Dylan', 'How many roads'
);

/* Perform a consistent read */
$item = $music->fetch(
    'Bob Dylan', 'How many roads', true
);

echo $item->attribute('Id');

if ($item->isset('hit')) {
    echo "{$item->attribute('SongTitle')} is a hit";
}
```

## Update an item ##
```
/* Update an item */
$item->set('Year', 1966);

$result = $music->update($item);

$results = $music->query()
    ->key('Bob Dylan')
    ->condition('SongTitle', '=', 'How many roads')
    ->filter('Year', '=', '1966')
    ->fetch(); 

foreach ($results as $item) {
    $item->set('Year', $item->attribute('Year') + 1);
    $music->update($item);
}
```

## Conditional update ##

```
use Bego\Condition;

$conditions = [
    Condition::beginsWith('Year', '19'),
    Condition::attributeExists('Year'),
    Condition::comperator('Year', '=', 1967),
];

$result = $music->update($item, $conditions);

if ($result) {
    echo 'Item updated successfully'
}
```

## Delete an item ##
```
$music->delete($item);
```

## Creating a table (experimental) ##
```
$spec = [
    'types'     => [
        'partition' => 'S',
        'sort'      => 'S',
    ],
    'capacity'  => ['read' => 5, 'write' => 5],
    'indexes'   => [
        'My-Global-Index' => [
            'type'     => 'global',
            'keys' => [
                ['name' => 'Year', 'types' => ['key' => 'HASH', 'attribute' => 'N']],
                ['name' => 'Artist', 'types' => ['key' => 'RANGE', 'attribute' => 'S']],
            ],
            'capacity' => ['read' => 5, 'write' => 5]
        ],
    ],
];

$music->create($spec);
```
