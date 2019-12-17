<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/slack.php';

set('default_timeout', null);

// Project name
set('application', 'odin-app');

// Project repository
set('repository', 'https://git-codecommit.eu-central-1.amazonaws.com/v1/repos/ODIN');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Slack configuration
//set('slack_webhook', 'https://hooks.slack.com/services/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'); // DevOps Channel
set('slack_webhook', 'https://hooks.slack.com/services/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');
set('slack_text', 'Deploying {{application}} to production');
set('slack_success_text', 'Deploy to production successful');
set('slack_failure_text', 'Deploy to production failed');

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);


// set('bin/npm', function () {
//     return run('which npm');
// });


// Hosts

// Disabled to deploy in the same server
//
// host('odin.saga-be.host')
//     ->set('deploy_path', '/var/www/{{application}}');

// localhost()
//    ->set('deploy_path', '/var/www/{{application}}');

//host('172.32.16.10', '172.32.32.10', '172.33.16.10')
host('172.32.16.10', '172.32.32.10') // US01 and US02
->set('deploy_path', '/var/www/{{application}}')
    ->user('deployment')
    ->port(22)
//    ->configFile('~/.ssh/config')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no');


// Tasks


task('build', function () {
    run('cp env {{build_path}}/.env');
    cd('{{build_path}}');
    run('composer install');
    run('npm install');
    run('npm run prod');
})->local();

desc('Push to s3');
task('s3:push', function () {
    run("cd {{build_path}}/public/assets && aws s3 sync . s3://mediaodin/assets --profile=odinprod --cache-control max-age=259200");
})->local();

task('upload', function () {
    upload("{{build_path}}/", '{{release_path}}');
});

// desc('Install npm packages');
// task('npm:install', function () {
//     if (has('previous_release')) {
//         if (test('[ -d {{previous_release}}/node_modules ]')) {
//             run('cp -R {{previous_release}}/node_modules {{release_path}}');
//
//             // If package.json is unmodified, then skip running `npm install`
//             if (!run('diff {{previous_release}}/package.json {{release_path}}/package.json')) {
//                 return;
//             }
//         }
//     }
//     run("cd {{release_path}} && {{bin/npm}} install");
// });
//
// desc('Webpack (npm run prod)');
// task('npm:prod', function () {
//     run("cd {{release_path}} && {{bin/npm}} run prod");
// });

desc('Restart PHP-FPM');
task('php-fpm:restart', function () {
    run('sudo systemctl restart php7.3-fpm.service');
});

// Clone repo

set('build_path', '/home/ubuntu/odin-app-build');
set('branch', function () {
    try {
        $branch = runLocally('git rev-parse --abbrev-ref HEAD');
    } catch (\Throwable $exception) {
        $branch = null;
    }
    if ($branch === 'HEAD') {
        $branch = null; // Travis-CI fix
    }
    if (input()->hasOption('branch') && !empty(input()->getOption('branch'))) {
        $branch = input()->getOption('branch');
    }
    return $branch;
});
desc('Update code');
task('deploy:update_code', function () {
    $repository = get('repository');
    $branch = get('branch');
    $git = get('bin/git');
    $recursive = get('git_recursive', true) ? '--recursive' : '';
    $dissociate = get('git_clone_dissociate', true) ? '--dissociate' : '';
    $quiet = isQuiet() ? '-q' : '';
    $options = [
        'tty' => get('git_tty', false),
    ];
    $at = '';
    if (!empty($branch)) {
        $at = "-b $branch";
    }
    // If option `tag` is set
    if (input()->hasOption('tag')) {
        $tag = input()->getOption('tag');
        if (!empty($tag)) {
            $at = "-b $tag";
        }
    }
    // If option `tag` is not set and option `revision` is set
    if (empty($tag) && input()->hasOption('revision')) {
        $revision = input()->getOption('revision');
        if (!empty($revision)) {
            $depth = '';
        }
    }

    cd('{{build_path}}');
    run('ls -A | xargs rm -rf --');
    run("$git clone $at $recursive $quiet $repository {{build_path}} 2>&1", $options);
    if (!empty($revision)) {
        run("cd {{build_path}} && $git checkout $revision");
    }
})->local();

// end clone repo





desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code', // BUILD LOCALLY
    'build',
    's3:push',
    'upload',
    'deploy:shared',
    // 'deploy:vendors', // BUILD LOCALLY
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:optimize',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);
after('deploy', 'success');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

//before('deploy:symlink', 'artisan:migrate');

before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');
