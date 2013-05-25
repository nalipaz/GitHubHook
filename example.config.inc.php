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
 * @param boolean $enableLogging
 *   Whether or not to enable logging.
 * @param string $logDirectory
 *   The log directory used in all branches when not specified in the branch 
 *   settings.
 * @param string $logFileName
 *   The log file name use in all branches when not specified in the branch and
 *   when the log entry doesn't match a branch.
 * @param array $branches
 *   An array of arrays representing each site/repository.
 *     Each array has a number of configurable keys.
 *     - branchName: Can be a branch name or a tag prefix, dependent on value
 *       of branchType.
 *         - Branch; the branch to deploy. 'stage' branch used on staging 
 *           server, dev for dev, prod for prod servers (stage).
 *         - Tag Prefix: the tag prefix to look for when determining where to
 *           deploy. 'prod' would match 'prod-20130201-1' as an example.
 *     - branchTitle: Name of branch used in logging.
 *     - branchType: Either branch or tag.
 *         - branch: Indicates that a specific branch will be used to make the
 *           deployments.  Examples: master, prod, stage, etc.
 *         - tag: Indicates that a tag prefix will be used to make the 
 *           deployments.  Example: 'stage' would mean that any tag prefixed
 *           with 'stage-' would deploy to the stage server, as in 
 *           'stage-2013-05-01-1'.
 *     - domain: The domain in which this application runs on.
 *     - logFileName: Can be used to specify a log file per site.
 *     - logDirectory: Can be used to specify a log directory per site.
 *     - gitFolder: The directory where the git repository will be held and
 *       git commands will be executed.
 *     - docRoot: The document root for the application, files from the git
 *       repo will be copied here using rsync.
 *     - owner: The linux user whom should own the files in the document root.
 *     - gitURL: The remove URL of the Git project, example:
 *       https://github.com/nalipaz/GitHubHook
 *     - allowedEmails: Optional array of email addresses.
 * @param array githubIPs
 *   An array of allowable IPs which can execute this script for deployment.
 */

$email = '';
$git = '/usr/bin/git';
$enableLogging = TRUE;
$logDirectory = 'log';
$logFileName = 'hook';

$branches = array(
  array(
    'branchName' => '',
    'branchTitle' => '',
    'branchType' => '',
    'domain' => '',
    'logFileName' => '',
    'logDirectory' => '',
    'gitFolder' => '',
    'docRoot' => '',
    'owner' => '',
    'gitURL' => '',
    'allowedEmails' => array(),
  ),
);

$githubIPs = array();
