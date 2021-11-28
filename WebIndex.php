<?php
require 'RenderMarkdown.php';

function console_log($data)
{
    echo '<script>';
    echo 'console.log('. json_encode($data) .')';
    echo '</script>';
}

$userAgent = 'Replace with own UserAgent';

// Check if REQUEST_URI has been provided
if ($_SERVER['REQUEST_URI'] != '/')
{
    console_log('REQUEST_URI has been provided: ' . $_SERVER['REQUEST_URI']);
    $mdFileUrl = $_SERVER['REQUEST_URI'];
}
else
{
    console_log('No REQUEST_URI has been provided.');
    console_log('For easy use, just append the direct link to the requested markdown files. For example /microsoft/winget-cli/blob/master/README.md');
}


if (isset($mdFileUrl))
{
    $githubClass = new GitHub;
    echo $githubClass->renderMarkdown($mdFileUrl, $userAgent);
}
else
{
    echo 'An error has occured. Please check the console for more details';
}

?>