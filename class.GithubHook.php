<?php
/**
 * @file
 * Main class for the project
 * 
 * All base methods and vars for the GitHub post-receive deployment hook.
 * 
 * @author Nicholas Alipaz http://nicholas.alipaz.net/
 * @author Chin Lee <kwangchin@gmail.com>
 */

class GithubHook {
  protected $remoteIP = '';
  protected $notificationEmails = array();
  protected $sendNotificationEmails = FALSE;
  protected $payload = '';
  protected $debug = FALSE;
  protected $branches = array();
  protected $git = '/usr/bin/git';
  protected $rsync = '/usr/bin/rsync';
  protected $logDirectory = 'log/';
  protected $logFilename = 'hook';
  protected $rsyncExcludes = array();
  protected $githubIPs = array();
  protected $checkIP = TRUE;
  
  protected $output = array(); // not for direct usage in sub-classes

  function __construct() {
    $this->setRemoteIP();
    $this->setPayload();
    $this->addBranches();
  }

  protected function setRemoteIP() {
    // Support for EC2 load balancers
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
      $this->remoteIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $this->remoteIP = $_SERVER['REMOTE_ADDR'];
    }
  }

  protected function setPayload() {
    if (isset($_POST['payload'])) {
      $this->payload  = json_decode($_POST['payload']);
    }
    else {
      $this->notFound('Payload not available from: ' . $this->remoteIP);
    }
  }

  protected function sendEmails($reason) {
    $emails = $this->notificationEmails;
    array_push($emails, $this->payload->repository->owner->email);
    array_push($emails, $this->payload->pusher->email);
    $emailList = implode(',', $emails);

    mail($emailList, '[GitHubHook Error] Not Found', $reason);
  }

  protected function notFound($reason = NULL) {
    if ($reason !== NULL) {
      $this->log($reason);
      if ($this->sendNotificationEmails) {
        $this->sendEmails($reason);
      }
    }

    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found.';
    exit;
  }

  protected function setBranchType(&$branch) {
    switch ($branch['branch_type']) {
      case 'tag':
        $branch['branch_type'] = 'tags';
        break;

      default:
        $branch['branch_type'] = 'heads';
        break;
    }
  }

  protected function addBranches() {
    $branches = $this->branches;
    // Empty the branches so we can refill with proper details.
    $this->branches = array();

    foreach($branches as $branch){
      $this->addBranch($branch);
    }
  }

  protected function addBranch($branch) {
    $this->setBranchType($branch);
    $this->branches[] = $branch;
  }

  protected function log($message, $branch = array()) {
    if ($this->debug) {
      $logDirectory = (!empty($branch['log_directory'])) ? rtrim($branch['log_directory'], '/') : $this->logDirectory;
      $logFilename = (!empty($branch['log_filename'])) ? $branch['log_filename'] : $this->logFilename;

      file_put_contents($logDirectory . '/' . $logFilename . '.log', '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL, FILE_APPEND);
    }
  }

  protected function logHead($branch) {
    $this->log('', $branch);
    $this->log('Beginning deployment...', $branch);
    $this->log('Deploying ' . $this->payload->repository->url, $branch);
    $this->log($this->payload->ref . '==' . 'refs/' . $branch['branch_type'] . '/' . $branch['branch_name'], $branch);
    $this->log('Deploying to ' . $branch['branch_title'] . ' server', $branch);
  }

  protected function branchMatches($branch) {
    //remove http:// and https:// from the URL. We don't really care about this.
    return (preg_replace('/(https?):\/\//', '', $this->payload->repository->url) == preg_replace('/(https?):\/\//', '', $branch['git_url']));
  }

  protected function validIP() {
    return (in_array($this->remoteIP, $this->githubIPs));
  }

  protected function getBranch() {
    foreach ($this->branches as $branch) {
      if ($this->branchMatches($branch)) {
        return $branch;
      }
    }
  }

  protected function getRefInfo() {
    $payloadRef = explode('/', $this->payload->ref);
    $payloadRefInfo = array(
      'type' => $payloadRef[1],
      'id' => $payloadRef[2],
    );

    return $payloadRefInfo;
  }

  protected function logOutput($branch) {
    foreach ($this->output as $message) {
      $this->log($message, $branch);
    }
  }

  protected function processPayload($branch) {
    $this->logHead($branch);
    $payloadRef = $this->getRefInfo();
    $method = 'execute' . ucfirst($branch['branch_type']) . 'Script';
    $this->$method($branch, $payloadRef);
    $this->logOutput($branch);
  }

  protected function checkPayload($branch, $condition) {
    if ($condition) {
      return TRUE;
    }
    else {
      $this->log('This payload did not match a configured site/repo.', $branch);
      
      return FALSE;
    }
  }

  protected function executeHeadsScript($branch, $payloadRef) {
    if ($this->checkPayload($branch, ($payloadRef['id'] === $branch['branch_name']))) {
      $dir = $this->executeScriptStart($branch);
      $this->executeGitPull($payloadRef);
      $this->executeScriptEnd($branch, $dir);
    }
  }

  protected function executeGitPull($payloadRef) {
    // have to avoid conflicts by always overwriting the local.
    $this->output[] = trim(shell_exec($this->git . ' reset --hard HEAD 2>&1'));
    $this->output[] = trim(shell_exec($this->git . ' pull origin ' . $payloadRef['id'] . ' 2>&1'));
  }

  protected function executeTagsScript($branch, $payloadRef) {
    // Check that tag name starts with 'stage-' for example.
    if ($this->checkPayload($branch, (strpos($payloadRef['id'], $branch['branch_name'] . '-') === 0))) {
      $dir = $this->executeScriptStart($branch);
      $this->executeGitCheckout($payloadRef);
      $this->executeScriptEnd($branch, $dir);
    }
  }

  protected function executeGitCheckout($payloadRef) {
    $this->output[] = trim(shell_exec($this->git . ' fetch --tags 2>&1'));
    $this->output[] = trim(shell_exec($this->git . ' checkout ' . $payloadRef['type'] . '/' . $payloadRef['id'] . ' 2>&1'));
  }

  protected function executeScriptStart($branch) {
    $dir = getcwd();
    chdir($branch['git_folder']);

    return $dir;
  }

  protected function executeScriptEnd($branch, $dir) {
    $rsyncCommand = $this->rsync . ' --delete -avze' . $this->rsyncExclusions() . $this->ensureTrailingSlash($branch['git_folder']) . ' ' . $this->ensureTrailingSlash($branch['doc_root']);
    $this->output[] = trim(shell_exec('sudo -u ' . $branch['owner'] . ' ' . $rsyncCommand . ' 2>&1'));
    chdir($dir);
  }

  protected function ensureTrailingSlash($directory) {
    return preg_replace('@([^/]$)@', '$1/', $directory);
  }

  protected function rsyncExclusions() {
    $excludes = ' ';

    foreach ($this->rsyncExcludes as $exclude) {
      $excludes .= '--filter="-rsp_' . $exclude . '" ';
    }

    return $excludes;
  }

  /**
   * Deploys.
   */
  public function deploy() {
    if ($this->checkIP === FALSE || $this->validIP()) {
      $branch = $this->getBranch();

      if ($branch) {
        $this->processPayload($branch);
      }
    }
    else {
      $this->notFound('IP address not recognized: ' . $this->remoteIP);
    }
  }
}
