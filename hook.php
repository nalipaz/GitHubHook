<?php
require_once('class.github_hook.php');

// Initiate the GitHub Deployment Hook.
$hook = new github_hook;

// Initiate the location of git.
$hook->add_admin_email($email);

// Initiate the location of git.
$hook->add_git($git);

// Turn logging on if set in config file.
if ($enable_logging) {
  $hook->enable_debug();
}

// Initiate settings for the location of logs.
$hook->set_log_settings(array('directory' => $log_directory, 'filename' => $log_filename));

// Initiate the allowed GitHub IP addresses.
$hook->add_github_ips($github_ips);

// Initialize all the branches.
foreach($branches as $val){
  $hook->add_branch($val);
}

// Deploy the commits.
$hook->deploy();