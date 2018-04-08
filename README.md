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
$server    = 'Web-Server-1';
$date      = date('Y-m-d H:i:s', $time);

$query = Bego\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server);

/* Compile all options into one request */
$statement = $query->prepare($client);

/* Execute result and return first page of results */
$results = $statement->fetch(); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}
```


## Combining steps into one query ##
```
$results = Bego\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client)
    ->fetch(); 

```

## Descending Order ##
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$statement = Bego\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->reverse()
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client);
```

## Paginating ##
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work: fetchAll() or fetchMany()
```
$statement = Bego\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client);

/* Get all items no matter the cost */
$results = $statement->fetchAll();

/* Execute as many calls as is required to get 1000 items */
$results = $statement->fetchMany(1000); 
```