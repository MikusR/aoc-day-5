<?php

namespace App;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;

class FetchTask implements Task
{
    public function __construct(
        private readonly array $almanac,
        private readonly string $seed,
        private readonly string $range
    ) {
    }

    public function run(Channel $channel, Cancellation $cancellation): string
    {
        $minimum = false;
        for ($i = 0; $i < $this->range; $i++) {
            $destination = (int)$this->seed + $i;
            $dest = $destination;
            //var_dump('seed:',$destination);
            foreach ($this->almanac as $item) {
                foreach ($item as $line) {
                    if (($destination >= $line['source']) &&
                        ($destination <= $line['source'] + $line['range'] - 1)) {
                        $dest = $line['destination'] + abs($line['source'] - $destination);
                    }
                }
                $destination = $dest;
//
            }
            if ($minimum === false) {
                $minimum = $destination;
            }
            if ($destination < $minimum) {
                $minimum = $destination;
            }
        }
        return $minimum;
    }
}