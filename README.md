# Bego

Bego is a library for making DynamoDb queries simpler to work with

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

/* Compile all query options into one request */
$statement = $query->prepare();

/* Execute result and return first page of results */
$results = $statement->fetch(); 

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
    ->prepare()
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
    ->prepare()
    ->fetch(); 
```

## Descending Order ##
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$statement = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->reverse()
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->prepare();
```

## Indexes ##
```
$results = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->prepare()
    ->fetch();
```

## Consistent Reads ##
DynamoDb performs eventual consistent reads by default. For strongly consistent reads set the consistent() flag:
```
$statement = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->consistent()
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->prepare();
```

## Paginating ##
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work: fetchAll() or fetchMany()
```
$statement = Bego\Query::create($client, $marshaler)
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('User', '=', $User)
    ->prepare();

/* Get all items no matter the cost */
$results = $statement->fetchAll();

/* Execute as many calls as is required to get 1000 items */
$results = $statement->fetchMany(1000); 
```