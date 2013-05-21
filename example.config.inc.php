<?php
/**
 * This configuration file is all you need to edit. It 
 * will be used by hook.php to set up the git process and 
 * post-git commands (modifying folder security for apache).
 */

// Configure admin email for error messages.
$email = '';

// Location of git binary.
$git = '/usr/bin/git';

// Enable logging.
$enableLogging = TRUE;

// The log settings use for all branches if not specified otherwise in the branch settings.
$logDirectory = 'log';
$logFileName = 'hook';

// Branch settings.
$branches = array(
  array(
    'branchName' => 'master', // The branch to deploy. 'stage' branch used on staging server, dev for dev, prod for prod servers (stage).
    'branchTitle' => 'Development', // Just used in logging (staging).
    'logFileName' => 'example.com', // Log filename to use, when empty the log settings near the top of this file are used.
    'logDirectory' => '', // Log directory to use, when empty the log settings near the top of this file are used.
    'gitFolder' => '/var/www/example.com', // The folder for the site that we're deploying (/var/www/MyWebsite).
    'docRoot' => '',
    'owner' => '',
    'gitURL' => 'https://github.com/username/example', // The remote URL of the Git project (https://github.com/ajbogh/GitHubHook)
    'allowedEmails' => array( // Optional, can be blank array.
//      'email@example.com',
    ),
  ),
  array(
    'branchName' => 'prod', // The tagname identifier to deploy, example: prod-2013-02-01
    'branchType' => 'tag', // The type to look for, branch is assumed, but you could also use tag
    'branchTitle' => 'Production', // Just used in logging (staging).
    'logFileName' => 'example.com', // Log filename to use, when empty the log settings near the top of this file are used.
    'logDirectory' => '', // Log directory to use, when empty the log settings near the top of this file are used.
    'gitFolder' => '/var/www/example.com', // The folder for the site that we're deploying (/var/www/MyWebsite).
    'docRoot' => '',
    'owner' => '',
    'gitURL' => 'https://github.com/username/example', // The remote URL of the Git project (https://github.com/ajbogh/GitHubHook)
    'allowedEmails' => array( // Optional, can be blank array.
//      'email@example.com',
    ),
  ),
//  array(
//    'branchName' => '',
//    'branchTitle' => '',
//    'logFileName' => '',
//    'logDirectory' => '',
//    'gitFolder' => '',
//    'gitURL' => '',
//    'allowedEmails' => array(),
//  ),
);

// An array of IPs that can run the deployment.
$githubIPs = array(
  '207.97.227.253',
  '50.57.128.197',
  '108.171.174.178',
  '50.57.231.61',
  '54.235.183.49',
  '54.235.183.23',
  '54.235.118.251',
  '54.235.120.57',
  '54.235.120.61',
  '54.235.120.62',
  '204.232.175.75',
  '204.232.175.64', 
  '192.30.252.0',
);

?>
