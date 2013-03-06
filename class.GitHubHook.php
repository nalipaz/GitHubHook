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

  /**
   * Add all branches.
   * @param array $branchArrElem Array of branches and their settings.
   * @since 1.0
   */
  public function addBranch($branchArrElem) {
    $this->_branches[] = $branchArrElem;
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
   * Deploys.
   * @since 1.0
   */
  public function deploy() {
    if (in_array($this->_remoteIp, $this->_ips)) {
      foreach ($this->_branches as $branch) {
        //remove http:// and https:// from the URL. We don't really care about this.
        if (preg_replace('/(https?):\/\//', '', $this->_payload->repository->url) == preg_replace('/(https?):\/\//', '', $branch['gitURL'])) {
          $this->log('', $branch);
          $this->log('Beginning deployment...', $branch);
          $this->log('Deploying ' . $this->_payload->repository->url, $branch);
          $this->log($this->_payload->ref . '==' . 'refs/heads/' . $branch['branchName'], $branch);
          if ($this->_payload->ref == 'refs/heads/' . $branch['branchName']) {
            $this->log('Deploying to ' . $branch['branchTitle'] . ' server', $branch);
            $dir = getcwd();
            chdir($branch['gitFolder']);
            // have to avoid conflicts by always overwriting the local.
            $reset_output = trim(shell_exec($this->_git . ' reset --hard HEAD 2>&1'));
            $pull_output = trim(shell_exec($this->_git . ' pull origin ' . $branch['branchName'] . ' 2>&1'));
            shell_exec('/bin/chmod -R 755 .');
            chdir($dir);
            $this->log($reset_output, $branch);
            $this->log($pull_output, $branch);
          }
        }
      }
    }
    else {
      $this->_notFound('IP address not recognized: ' . $this->_remoteIp);
    }
  }
}
