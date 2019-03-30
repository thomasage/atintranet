<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'Intranet');

// Project repository
set('repository', 'git@github.com:thomasage/atintranet.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', ['.env.local']);
set('shared_dirs', ['var/cache', 'var/log', 'var/sessions']);

// Writable dirs by web server
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

inventory('deploy/hosts.yaml');
set('bin/php', '/usr/local/php7.3/bin/php');

// Tasks

task(
    'deploy:env:load',
    function () {
        $env = [];
        foreach (explode("\n", run('cat {{deploy_path}}/shared/.env.local')) as $e) {
            if (0 === strpos($e, '#') || false === strpos($e, '=')) {
                continue;
            }
            [$var, $val] = explode('=', $e);
            $env[$var] = $val;
        }
        set('env', $env);
    }
)
    ->desc('Load environment vars from shared/.env.local');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');
before('deploy:release', 'deploy:env:load');
