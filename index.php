<?php
error_reporting(0);
ini_set('display_errors', 1);

require_once('class.GithubHook.php');
require_once('class.GithubHookCustom.php');

// Initiate the GitHub Deployment Hook.
$hook = new GithubHookCustom;

$hook->deploy();
