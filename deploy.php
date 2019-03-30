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
set('writable_use_sudo', false);

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

desc('Deploy project');
task(
    'deploy',
    [
        'deploy:info',
        'deploy:prepare',
        'deploy:lock',
        'deploy:env:load',
        'deploy:release',
        'deploy:update_code',
        'deploy:shared',
        'deploy:vendors',
        'deploy:cache:clear',
        'deploy:cache:warmup',
        'database:migrate',
        'deploy:symlink',
        'deploy:unlock',
        'cleanup',
    ]
);

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
