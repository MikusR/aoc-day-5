<?php

// hello-world.php

require __DIR__ . '/vendor/autoload.php';

use App\FetchTask;
use Amp\Future;
use Amp\Parallel\Worker;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$client = new Client();

$jar = CookieJar::fromArray([
    'session' => $_ENV['AOC_COOKIE']
], 'adventofcode.com');
$response = $client->request('GET', 'https://adventofcode.com/2023/day/5/input', ['cookies' => $jar]);
echo '<pre>';
$data = (string)$response->getBody();

$data = preg_split('/\n\n/', $data);
$almanac = [];

foreach ($data as $key => $d) {
    if ($key === 0) {
        $temp = explode('seeds:', $d);
        preg_match_all('(\d+)', $temp[1], $matches);
        $seeds = array_map('intval', $matches[0]);
    } else {
        $temp = preg_split('/:\n/', $d);
        $temp[1] = preg_split('/\n/', $temp[1]);
        foreach ($temp[1] as $t) {
            if (strlen($t) > 0) {
                preg_match_all('(\d+)', $t, $matches);
                if (count($matches) > 0) {
                    $almanac[$temp[0]][] =
                        [
                            'destination' => (int)$matches[0][0],
                            'source' => (int)$matches[0][1],
                            'range' => (int)$matches[0][2]
                        ];
                }
            }
        }
    }
}


$executions = [];
foreach ($seeds as $key => $seed) {
    // FetchTask is just an example, you'll have to implement
    // the Task interface for your task.

    if ($key % 2 === 0) {
        $executions[$seed] = Worker\submit(new FetchTask($almanac, $seed, $seeds[$key + 1]));
    }
}

// Each submission returns an Execution instance to allow two-way
// communication with a task. Here we're only interested in the
// task result, so we use the Future from Execution::getFuture()
$responses = Future\await(
    array_map(
        fn(Worker\Execution $e) => $e->getFuture(),
        $executions,
    )
);
//
foreach ($responses as $seed => $response) {
    var_dump($response);
}
var_dump(min($responses));