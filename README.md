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

$results = Bego\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client)
    ->fetch(); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}
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
DynanmoDb limits the results return to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available for paginating results: fetchAll() or fetchMany()
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