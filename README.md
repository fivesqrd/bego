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

$statement = Bego\Database\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client);

/* Execute only one call */
$results = $statement->fetch(); 

/* Get all no matter the cost */
$results = $statement->fetchAll();

/* Execute as many calls required to get 1000 items */
$results = $statement->fetchMany(1000); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}
```