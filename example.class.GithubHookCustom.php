<?php

/**
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
 */
class GithubHookCustom extends GitHubHook {
  protected $branches = array(
    array(
//      'branch_name' => '',
//      'branch_title' => '',
//      'branch_type' => '',
//      'domain' => '',
//      'log_filename' => '',
//      'log_directory' => '',
//      'git_folder' => '',
//      'doc_root' => '',
//      'owner' => '',
//      'git_url' => '',
    ),
  );
  protected $debug = TRUE;
  protected $rsyncExcludes = array(
    '/.git/',
    '/.gitignore',
  );
  protected $githubIPs = array(
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
    '192.30.252.56',
    '192.30.252.57',
    '192.30.252.58',
    '192.30.252.59',
    '192.30.252.60',
    '192.30.252.61',
    '192.30.252.62',
    '192.30.252.63',
    '192.30.252.64',
    '204.232.175.64',
    '204.232.175.75',
    '207.97.227.253',
  );

//  protected function executeScriptEnd($branch, &$output, $dir) {
//    parent::executeScriptEnd($branch, $output, $dir);
//    // do some stuff
//  }
}
