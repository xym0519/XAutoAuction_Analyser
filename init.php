<?php
function removeDir($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? removeDir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

if (count($argv) > 1) {
    $projectName = $argv[1];
} else {
    echo "\033[0;32mProject name: \033[0m\n";
    $projectName = readline();
}

if (empty($projectName)) {
    echo "\033[0;31mInvaild project name\033[0m\n";
    exit(255);
}

echo "\033[01;32mInitializing project env file...\033[0m\n";
$envFileContent = file_get_contents("./_env");
$envFileContent = preg_replace('/APP_NAME=[\S]*/i', 'APP_NAME=' . $projectName, $envFileContent);
$envFileContent = preg_replace('/APP_KEY=[\S]*/i', 'APP_KEY=' . md5($projectName), $envFileContent);
file_put_contents("./.env", $envFileContent);

if (file_exists('.idea')) {
    echo "\033[01;32mCleaning idea files...\033[0m\n";
    removeDir('.idea');
}

if (file_exists('vendor')) {
    echo "\033[01;32mCleaning composer files...\033[0m\n";
    removeDir('vendor');
}
unlink('composer.lock');

echo "\033[01;32mInstalling dependencies...\033[0m\n";
system("composer install", $code);
if ($code > 0) {
    echo "\033[0;31mInstall failed. You may need reinstall it with command \"composer install\"\033[0m";
}

echo "\033[01;32mProject $projectName has been created!\033[0m\n";

if (file_exists('.git')) {
    echo "\033[01;31mGit files exists. You may clean them with command \"del .git -Force -Recurse\"\033[0m\n";
}
