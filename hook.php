<?php
require_once('class.GitHubHook.php');
require_once('config.inc.php');

// Initiate the GitHub Deployment Hook.
$hook = new GitHubHook;

// Initiate the location of git.
$hook->addGit($git);

// Turn logging on if set in config file.
if ($enableLogging) {
  $hook->enableDebug();
}

// Initiate settings for the location of logs.
$hook->setLogSettings(array('directory' => $logDirectory, 'filename' => $logFileName));

// Initiate the allowed GitHub IP addresses.
$hook->addGitHubIPs($githubIPs);

// Initialize all the branches.
foreach($branches as $val){
  $hook->addBranch($val);
}

// Deploy the commits.
$hook->deploy();