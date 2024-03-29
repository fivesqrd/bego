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
];

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
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->filter(Condition::attribute('Year')->eq(1966))
    ->fetch(); 

/* Query a global index */
$results = $music->query('My-Global-Index')
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->filter(Condition::attribute('Year')->eq(1966))
    ->fetch(); 

/* Query a local index */
$results = $music->query('My-Local-Index')
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->filter(Condition::attribute('Year')->eq(1966))
    ->fetch(); 
```

### Key condition and filter expressions ###
Multiple key condition / filter expressions can be added. DynamoDb applies key conditions to the query but filters are applied to the query results
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->beginsWith('How'))
    ->filter(Condition::attribute('Year')->in(['1966', '1967']))
    ->fetch(); 
```

### Descending Order ###
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$results = $music->query()
    ->reverse()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->fetch(); 
```

### Projecting attributes ###
To get just some, rather than all of the attributes, use a projection expression.
```
$results = $music->query()
    ->key('Bob Dylan')
    ->projection(['Year', 'SongTitle'])
    ->condition(Condition::attribute('SongTitle')->beginsWith('How'))
    ->filter(Condition::attribute('Year')->eq('1966'))
    ->fetch(); 
```

### Working with result sets ###
The result set object implements the Iterator interface and canned by used straight way. It provived some handy methods as well.
```
/* Execute query and return first page of results */
$results = $music->query()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->fetch(); 

foreach ($results as $item) {
    echo "{$item->attribute('Id')}\n";
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
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->consistent()
    ->fetch(); 
```

### Limiting Results ###
DynamoDb allows you to limit the number of items returned in the result. Note that this limit is applied to the key conidtion only. DynamoDb will apply filters after the limit is imposed on the result set:
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->limit(100)
    ->fetch();
```

### Paginating ###
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work:
```
$results = $music->query()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'));

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
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'));

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
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
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
    ->filter(Condition::attribute('Year')->eq(1966))
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

Batch writing will automatically deal with a) DynamoDb's batch size limits, b) efficiency, i.e. running multiple workers in parallel, c) handling unresolved items and d) retrying any errors due to provision limits
```
/* Create multiple items with batch writing */
$item = $music->putBatch(
   [
       'Id'          => uniqid(),
       'Artist'     => 'Neil Diamond',
       'SongTitle'  => 'Red, red wine',
       'Year'       => 1968,
       'Time'       => date('Y-m-d H:i:s')
   ],
   [
       'Id'          => uniqid(),
       'Artist'     => 'Bob Marley',
       'SongTitle'  => 'Buffalo Soldier',
       'Year'       => 1984,
       'Time'       => date('Y-m-d H:i:s')
   ]
);
```

## Get an item ##
```
/* Fetch an item */
$item = $music->fetch(
    'Bob Dylan', 'How many roads'
);

if ($item->isEmpty()) {
    throw new \Exception("Requested record could not be found");
}

if ($item->isSet('hit')) {
    echo "{$item->attribute('SongTitle')} is a hit";
}

echo $item->attribute('Id');
```

```
/* Perform a consistent read */
$item = $music->fetch(
    'Bob Dylan', 'How many roads', true
);

if ($item->isEmpty()) {
    throw new \Exception("Requested record could not be found");
}

echo $item->attribute('Id');
```

## Working with item's attribute values ##
```
/* Return value if attribute exists, otherwise NULL */
echo $item->attribute('Artist');
echo $item->Artist; //shorthand

/* Return value if attribute exists, otherwise throw exception */
echo $item->ping('Artist');

/* Checking if an attribute exists and not empty */
echo $item->isSet('Artist') ? 'Yes' : 'No';
echo isset($item->Artist) ? 'Yes' : 'No'; //shorthand
```

## Update an item ##
```
/* Update an item */
$item->set('Year', 1966);
$item->Year = 1966; //shorthand

$result = $music->update($item);

$results = $music->query()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->filter(Condition::attribute('Year')->eq(1966))
    ->fetch(); 

foreach ($results as $item) {
    $item->set('Year', $item->attribute('Year') + 1);
    $music->update($item);
}
```

Making use of an item's magic properties instead of set() and attribute()
```
/* Update an item */
$item->Year = 1966;

$result = $music->update($item);

$results = $music->query()
    ->key('Bob Dylan')
    ->condition(Condition::attribute('SongTitle')->eq('How many roads'))
    ->filter(Condition::attribute('Year')->eq(1966))
    ->fetch(); 

foreach ($results as $item) {
    $item->Year = $item->Year + 1;
    $music->update($item);
}
```

## Conditional update ##

```
use Bego\Condition;

$conditions = [
    Condition::attribute('Year')->beginsWith('19')
    Condition::attribute('Year')->exists()
    Condition::attribute('Year')->eq(1966)
    Condition::attribute('Year')->in([1966, 1967])
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

/* Delete multiple items with batch writing */
```
$response = $table->deleteBatch($items);
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
