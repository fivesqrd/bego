# Bego

Bego is a library for making DynamoDb queries simpler to work with

You can Query any DynamoDb table or secondary index, provided that it has a composite primary key (partition key and sort key)
## Example ##
```
$client = new Aws\DynamoDb\DynamoDbClient([
    'version' => 'latest',
    'region'  => 'eu-west-1',
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$time      = strtotime('-24 hours');
$name      = 'Test';
$User    = 'Web-User-1';
$date      = date('Y-m-d H:i:s', $time);

$query = Bego\Query::create($client, new Aws\DynamoDb\Marshaler())
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->filter('User', '=', $User);

/* Execute result and return first page of results */
$results = $query->fetch(); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}
```

## Combining all steps into one chain ##
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->fetch(); 
```

## Key condition and filter expressions ##
Multiple key condition / filter expressions can be added. DynamoDb applies key conditions to the query but filters are applied to the query results
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->fetch(); 
```

## Descending Order ##
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->reverse()
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->fetch();
```

## Indexes ##
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->fetch();
```

## Consistent Reads ##
DynamoDb performs eventual consistent reads by default. For strongly consistent reads set the consistent() flag:
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->consistent()
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->fetch();
```

## Limiting Results ##
DynamoDb allows you to limit the number of items returned in the result. Note that this limit is applied to the key conidtion only. DynamoDb will apply filters after the limit is imposed on the result set:
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->consistent()
    ->condition('Timestamp', '>=', $date)
    ->filter('User', '=', $User)
    ->limit(100)
    ->fetch();
```

## Paginating ##
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work:
```
$query = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->filter('User', '=', $User);

/* Option 1: Get all items no matter the cost */
$results = $query->fetch(false);

/* Option 2: Execute up to 10 queries and return the aggregrated results */
$results = $query->fetch(10); 
```

In some cases one may want to paginate accross multiple hops;

```
$query = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->filter('User', '=', $User);

/* First Hop: Get one page */
$results = $query->fetch(1);
$pointer = $results->getLastEvaluatedKey();

/* Second Hop: Get one more page, continueing from previous request */
$results = $query->fetch(1, $pointer); 
```

## Capacity Units Consumed ##
DynamoDb can calculate the total number of read capacity units for every query. This can be enabled using the consumption() flag:

```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->consumption()
    ->condition('Timestamp', '>=', $date)
    ->filter('User', '=', $User)
    ->fetch();

echo $results->getCapacityUnitsConsumed();
```