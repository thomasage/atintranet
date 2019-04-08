<?php

declare(strict_types=1);

exec(sprintf('php %s/../bin/console app:import-toggl 2019-01-01', __DIR__), $output, $return_var);

if (0 !== $return_var) {

    foreach ($output as $o) {
        echo sprintf("%s\n", $o);
    }

    exit($return_var);

}
