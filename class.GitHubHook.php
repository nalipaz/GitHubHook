<?php
error_reporting(0);
ini_set('display_errors', 1);

/**
 * GitHub Post-Receive Deployment Hook.
 *
 * @author Chin Lee <kwangchin@gmail.com>
 * @copyright Copyright (C) 2012 Chin Lee
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 */

class GitHubHook {
  /**
   * @var string Remote IP of the person.
   * @since 1.0
   */
  private $_remoteIp = '';
  
  /**
   * @var string Admin email address.
   * @since 1.0
   */
  private $_adminEmail = '';

  /**
   * @var object Payload from GitHub.
   * @since 1.0
   */
  private $_payload = '';

  /**
   * @var boolean Log debug messages.
   * @since 1.0
   */
  private $_debug = FALSE;

  /**
   * @var array Branches.
   * @since 1.0
   */
  private $_branches = array();

  /**
   * @var array GitHub's IP addresses for hooks.
   * @since 1.1
   */
  private $_ips = array();

  /**
   * @var array Log settings for directory and file name.
   * @since 1.1
   */
  private $_logSettings = array();

  /**
   * Constructor.
   * @since 1.0
   */
  function __construct() {
    /* Support for EC2 load balancers */
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
      $this->_remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $this->_remoteIp = $_SERVER['REMOTE_ADDR'];
    }

    if (isset($_POST['payload'])) {
      $this->_payload  = json_decode($_POST['payload']);
    }
    else {
      $this->_notFound('Payload not available from: ' . $this->_remoteIp);
    }
  }

  /**
   * Centralize our 404.
   * @param string $reason Reason of 404 Not Found.
   * @since 1.1
   */
  private function _notFound($reason = NULL) {
    if ($reason !== NULL) {
      $this->log($reason);
      if (!empty($this->_adminEmail)) {
        mail($this->_adminEmail, '[GitHubHook Error] Not Found', $reason);
      }
    }

    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found.';
    exit;
  }

  /**
   * Enable log of debug messages.
   * @since 1.0
   */
  public function enableDebug() {
    $this->_debug = TRUE;
  }

  /**
   * Sets the admin email.
   * @param type $git Path to git binary.
   * @since 1.2
   */
  public function addAdminEmail($email) {
    $this->_adminEmail = $email;
  }

  /**
   * Sets the path to git.
   * @param type $git Path to git binary.
   * @since 1.2
   */
  public function addGit($git) {
    $this->_git = $git;
  }

  /**
   * Sets up the IP addresses that are whitelisted.
   * @param array $ipArr List of IP Addresses.
   */
  public function addGitHubIPs($ipArr) {
    $this->_ips = array_merge($this->_ips, $ipArr);
  }

  public function setBranchType(&$branchArrElem) {
    switch ($branchArrElem['branchType']) {
      case 'tag':
        $branchArrElem['branchType'] = 'tags';
        break;
      case 'branch':
      default:
        $branchArrElem['branchType'] = 'heads';
        break;
    }
  }

  /**
   * Add all branches.
   * @param array $branchArrElem Array of branches and their settings.
   * @since 1.0
   */
  public function addBranch($branchArrElem) {
    $this->setBranchType($branchArrElem);
    $this->_branches[] = $branchArrElem;
  }
  
  public function setLogSettings($settings) {
    $this->_logSettings['directory'] = $settings['directory'];
    $this->_logSettings['filename'] = $settings['filename'];
  }

  /**
   * Log a message.
   * @param string $message Message to log.
   * @param array $branch The branch to which to log the message.
   * @since 1.0
   */
  public function log($message, $branch = array()) {
    if ($this->_debug) {
      $logDirectory = (!empty($branch['logDirectory'])) ? rtrim($branch['logDirectory'], '/') : $this->_logSettings['directory'];
      $logFileName = (!empty($branch['logFileName'])) ? $branch['logFileName'] : $this->_logSettings['filename'];

      file_put_contents($logDirectory . '/' . $logFileName . '.log', '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL, FILE_APPEND);
    }
  }

  /**
   * Log the head of the log message.
   * @param array $branch
   */
  public function logHead($branch) {
    $this->log('', $branch);
    $this->log('Beginning deployment...', $branch);
    $this->log('Deploying ' . $this->_payload->repository->url, $branch);
    $this->log($this->_payload->ref . '==' . 'refs/' . $branch['branchType'] . '/' . $branch['branchName'], $branch);
    $this->log('Deploying to ' . $branch['branchTitle'] . ' server', $branch);
  }
  
  /**
   * Test to see if current branch is a match for the payload.
   * @param array $branch
   * @return boolean
   */
  public function branchMatches($branch) {
    return (preg_replace('/(https?):\/\//', '', $this->_payload->repository->url) == preg_replace('/(https?):\/\//', '', $branch['gitURL']));
  }

  /**
   * Check if the IP is a valid GitHub IP address.
   * @return boolean
   */
  public function validIP() {
    return (in_array($this->_remoteIp, $this->_ips));
  }

  public function getBranch() {
    foreach ($this->_branches as $branch) {
      //remove http:// and https:// from the URL. We don't really care about this.
      if ($this->branchMatches($branch)) {
        return $branch;
      }
    }
  }

  public function getRefInfo() {
    $payload_ref = explode('/', $this->_payload->ref);
    $payload_ref_info = array(
      'type' => $payload_ref[1],
      'id' => $payload_ref[2],
    );

    return $payload_ref_info;
  }

  public function logOutput($branch, $output) {
    foreach ($output as $message) {
      $this->log($message, $branch);
    }
  }

  public function processPayload($branch) {
    $output = array();

    $this->logHead($branch);
    $payload_ref = $this->getRefInfo();
    $method = 'execute' . ucfirst($branch['branchType']) . 'Script';
    $this->$method($branch, $payload_ref, $output);
    $this->logOutput($branch, $output);
  }

  public function checkPayload($branch, $condition) {
    if ($condition) {
      return TRUE;
    }
    else {
      $this->log('This payload did not match a configured site/repo', $branch);
      
      return FALSE;
    }
  }

  public function executeHeadsScript($branch, $payload_ref, &$output) {
    if ($this->checkPayload($branch, ($payload_ref['id'] === $branch['branchName']))) {
      $dir = $this->executeScriptStart($branch);
      $this->executeGitPull($payload_ref, $output);
      $this->executeScriptEnd($branch, $output, $dir);
    }
  }

  public function executeGitPull($payload_ref, &$output) {
    // have to avoid conflicts by always overwriting the local.
    $output[] = trim(shell_exec($this->_git . ' reset --hard HEAD 2>&1'));
    $output[] = trim(shell_exec($this->_git . ' pull origin ' . $payload_ref['id'] . ' 2>&1')); //      shell_exec('/bin/chmod -R 755 .');
  }

  public function executeTagsScript($branch, $payload_ref, &$output) {
    // Check that tag name starts with 'stage-' for example.
    if ($this->checkPayload($branch, (strpos($payload_ref['id'], $branch['branchName'] . '-') === 0))) {
      $dir = $this->executeScriptStart($branch);
      $this->executeGitCheckout($payload_ref, $output);
      $this->executeScriptEnd($branch, $output, $dir);
    }
  }

  public function executeDrushCommands($branch, &$output) {
    $aegir_posix = posix_getpwnam($branch['owner']);
    $output[] = 'posix_user: uid=' . $aegir_posix['uid'] . ' name: ' . $branch['owner'];
    posix_setuid($aegir_posix['uid']);
    $output[] = 'running scripts as ' . posix_getuid();
    $output[] = trim(shell_exec('drush --verbose @hostmaster hosting-task @' . $branch['domain'] . ' backup 2>&1'));
    $output[] = trim(shell_exec('drush --verbose @' . $branch['domain'] . ' updatedb 2>&1'));
    $output[] = trim(shell_exec('drush --verbose @hostmaster hosting-task @' . $branch['domain'] . ' verify 2>&1'));
  }

  public function executeGitCheckout($payload_ref, &$output) {
    $output[] = trim(shell_exec($this->_git . ' fetch --tags 2>&1'));
    $output[] = trim(shell_exec($this->_git . ' checkout ' . $payload_ref['type'] . '/' . $payload_ref['id'] . ' 2>&1'));
  }

  public function executeScriptStart($branch) {
    $dir = getcwd();
    chdir($branch['gitFolder']);

    return $dir;
  }

  public function executeScriptEnd($branch, &$output, $dir) {
    // try http://de.php.net/manual/en/function.posix-setuid.php and set
    // exclusions and shell script in here rather than external file.
//    $rsync_command = '/var/www/GitHubHook/rsync-data.sh ' . $this->rsyncExclusions() . $this->ensureTrailingSlash($branch['gitFolder']) . ' ' . $this->ensureTrailingSlash($branch['docRoot']);
    $rsync_command = '/var/www/GitHubHook/rsync-data.sh ' . $this->ensureTrailingSlash($branch['gitFolder']) . ' ' . $this->ensureTrailingSlash($branch['docRoot']);
    $output[] = trim(shell_exec($rsync_command . ' 2>&1'));
    chdir($dir);
    $this->executeDrushCommands($branch, $output);
  }

  public function ensureTrailingSlash($directory) {
    return preg_replace('@([^/]$)@', '$1/', $directory);
  }

  public function rsyncExclusions() {
    $exclude_list = array(
      '.git',
      '.gitignore',
      'drushrc.php',
      'files',
      'modules/development',
      'private',
      ' README.md',
      'settings.php',
    );

    foreach ($exclude_list as $exclude) {
      $excludes .= '--filter="' . $exclude . '" ';
    }
  }

  /**
   * Deploys.
   * @since 1.0
   */
  public function deploy() {
    if ($this->validIP()) {
      $branch = $this->getBranch();

      if ($branch) {
        $this->processPayload($branch);
      }
    }
    else {
      $this->_notFound('IP address not recognized: ' . $this->_remoteIp);
    }
  }
}
