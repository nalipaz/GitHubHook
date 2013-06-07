<?php
/**
 * @file
 * An example config file to be used as a starting point in your config.
 * 
 * This example configuration file is all you need to start. Make a copy
 * as config.inc.php and the file will be used by hook.php to set up the git 
 * process and post-git commands (modifying folder security for apache).
 */

/**
 * Configuration options
 * 
 * @param string $email
 *   Configure admin email for error messages.
 * @param string $git
 *   Location of git binary.
 * @param boolean $enable_logging
 *   Whether or not to enable logging.
 * @param string $log_directory
 *   The log directory used in all branches when not specified in the branch 
 *   settings.
 * @param string $log_filename
 *   The log file name use in all branches when not specified in the branch and
 *   when the log entry doesn't match a branch.
 * @param array $branches
 *   An array of arrays representing each site/repository.
 *     Each array has a number of configurable keys.
 *     - branch_name: Can be a branch name or a tag prefix, dependent on value
 *       of branchType.
 *         - Branch; the branch to deploy. 'stage' branch used on staging 
 *           server, dev for dev, prod for prod servers (stage).
 *         - Tag Prefix: the tag prefix to look for when determining where to
 *           deploy. 'prod' would match 'prod-20130201-1' as an example.
 *     - branch_title: Name of branch used in logging.
 *     - branch_type: Either branch or tag.
 *         - branch: Indicates that a specific branch will be used to make the
 *           deployments.  Examples: master, prod, stage, etc.
 *         - tag: Indicates that a tag prefix will be used to make the 
 *           deployments.  Example: 'stage' would mean that any tag prefixed
 *           with 'stage-' would deploy to the stage server, as in 
 *           'stage-2013-05-01-1'.
 *     - domain: The domain in which this application runs on.
 *     - log_filename: Can be used to specify a log file per site.
 *     - log_directory: Can be used to specify a log directory per site.
 *     - git_folder: The directory where the git repository will be held and
 *       git commands will be executed.
 *     - doc_root: The document root for the application, files from the git
 *       repo will be copied here using rsync.
 *     - owner: The linux user whom should own the files in the document root.
 *     - git_url: The remove URL of the Git project, example:
 *       https://github.com/nalipaz/GitHubHook
 *     - allowed_emails: Optional array of email addresses.
 * @param array github_ips
 *   An array of allowable IPs which can execute this script for deployment.
 */

$email = '';
$git = '/usr/bin/git';
$enable_logging = TRUE;
$log_directory = 'log';
$log_filename = 'hook';

$branches = array(
  array(
    'branch_name' => '',
    'branch_title' => '',
    'branch_type' => '',
    'domain' => '',
    'log_filename' => '',
    'log_directory' => '',
    'git_folder' => '',
    'doc_root' => '',
    'owner' => '',
    'git_url' => '',
    'allowed_emails' => array(),
  ),
);

$github_ips = array();
