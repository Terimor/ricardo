<?php
namespace Deployer;

require 'recipe/laravel.php';

set('default_timeout', null);
// Project name
set('application', 'odin-app');
// Project repository
set('repository', 'git@github.com:webholdingsllc/ODIN.git');
set('allow_anonymous_stats', false);
set('keep_releases', 2);

// Hosts

host('stg-odin.saga-be.host')
->set('deploy_path', '/var/www/{{application}}')
    ->user('ubuntu')
    ->port(22)
    ->identityFile('~/.ssh/odin_staging-2.pem')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no');

// Tasks

task('frontend:build', function () {
    cd('{{release_path}}');
    run('composer install');
    run('npm install');
    run('npm run prod');
});

desc('Upload to S3');
task('frontend:s3:upload', function () {
    run("cd {{release_path}}/public/assets && aws s3 sync . s3://mediaodinstg/assets --profile=odinstg --cache-control max-age=259200");
});

after('artisan:storage:link', 'frontend:build');
after('frontend:build', 'frontend:s3:upload');

//if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
