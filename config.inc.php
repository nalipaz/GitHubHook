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
  '50.57.128.197',
  '50.57.231.61',
  '54.235.183.23',
  '54.235.183.49',
  '54.235.118.251',
  '54.235.120.57',
  '54.235.120.61',
  '54.235.120.62',
  '108.171.174.178',
  '192.30.252.0',
  '192.30.252.48',
  '192.30.252.49',
  '192.30.252.50',
  '192.30.252.51',
  '192.30.252.52',
  '192.30.252.53',
  '192.30.252.55',
  '192.30.252.60',
  '192.30.252.63',
  '192.30.252.59',
  '204.232.175.64',
  '204.232.175.75',
  '207.97.227.253',
);

?>
