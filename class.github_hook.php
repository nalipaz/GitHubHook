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

class github_hook {
  protected $remote_ip = '';
  private $notification_emails = array();
  private $payload = '';
  private $debug = FALSE;
  protected $branches = array();
  protected $github_ips = array();
  protected $log_settings = array();

  function __construct() {
    /* Support for EC2 load balancers */
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
      $this->remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $this->remote_ip = $_SERVER['REMOTE_ADDR'];
    }

    if (isset($_POST['payload'])) {
      $this->payload  = json_decode($_POST['payload']);
    }
    else {
      $this->not_found('Payload not available from: ' . $this->remote_ip);
    }
  }

  protected function not_found($reason = NULL) {
    if ($reason !== NULL) {
      $this->log($reason);
      mail($this->payload->repository->owner->email . ',' . $this->payload->pusher->email, '[GitHubHook Error] Not Found', $reason);
    }

    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found.';
    exit;
  }

  public function enable_debug() {
    $this->debug = TRUE;
  }

  public function add_notification_emails($emails) {
    $this->notification_emails = $emails;
  }

  public function add_git($git) {
    $this->git = $git;
  }

  public function add_github_ips($ips) {
    $this->github_ips = array_merge($this->github_ips, $ips);
  }

  public function set_branch_type(&$branch) {
    switch ($branch['branch_type']) {
      case 'tag':
        $branch['branch_type'] = 'tags';
        break;

      default:
        $branch['branch_type'] = 'heads';
        break;
    }
  }

  public function add_branch($branch) {
    $this->set_branch_type($branch);
    $this->branches[] = $branch;
  }
  
  public function set_log_settings($settings) {
    $this->log_settings['directory'] = $settings['directory'];
    $this->log_settings['filename'] = $settings['filename'];
  }

  public function log($message, $branch = array()) {
    if ($this->debug) {
      $log_directory = (!empty($branch['log_directory'])) ? rtrim($branch['log_directory'], '/') : $this->log_settings['directory'];
      $log_filename = (!empty($branch['log_filename'])) ? $branch['log_filename'] : $this->log_settings['filename'];

      file_put_contents($log_directory . '/' . $log_filename . '.log', '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL, FILE_APPEND);
    }
  }

  public function log_head($branch) {
    $this->log('', $branch);
    $this->log('Beginning deployment...', $branch);
    $this->log('Deploying ' . $this->payload->repository->url, $branch);
    $this->log($this->payload->ref . '==' . 'refs/' . $branch['branch_type'] . '/' . $branch['branch_name'], $branch);
    $this->log('Deploying to ' . $branch['branch_title'] . ' server', $branch);
  }

  public function branch_matches($branch) {
    //remove http:// and https:// from the URL. We don't really care about this.
    return (preg_replace('/(https?):\/\//', '', $this->payload->repository->url) == preg_replace('/(https?):\/\//', '', $branch['git_url']));
  }

  public function valid_ip() {
    return (in_array($this->remote_ip, $this->github_ips));
  }

  public function get_branch() {
    foreach ($this->branches as $branch) {
      if ($this->branch_matches($branch)) {
        return $branch;
      }
    }
  }

  public function get_ref_info() {
    $payload_ref = explode('/', $this->payload->ref);
    $payload_ref_info = array(
      'type' => $payload_ref[1],
      'id' => $payload_ref[2],
    );

    return $payload_ref_info;
  }

  public function log_output($branch, $output) {
    foreach ($output as $message) {
      $this->log($message, $branch);
    }
  }

  public function process_payload($branch) {
    $output = array();

    $this->log_head($branch);
    $payload_ref = $this->get_ref_info();
    $method = 'execute_' . $branch['branch_type'] . '_script';
    $this->$method($branch, $payload_ref, $output);
    $this->log_output($branch, $output);
  }

  public function check_payload($branch, $condition) {
    if ($condition) {
      return TRUE;
    }
    else {
      $this->log('This payload did not match a configured site/repo.', $branch);
      
      return FALSE;
    }
  }

  public function execute_heads_script($branch, $payload_ref, &$output) {
    if ($this->check_payload($branch, ($payload_ref['id'] === $branch['branch_name']))) {
      $dir = $this->execute_script_start($branch);
      $this->execute_git_pull($payload_ref, $output);
      $this->execute_script_end($branch, $output, $dir);
    }
  }

  public function execute_git_pull($payload_ref, &$output) {
    // have to avoid conflicts by always overwriting the local.
    $output[] = trim(shell_exec($this->git . ' reset --hard HEAD 2>&1'));
    $output[] = trim(shell_exec($this->git . ' pull origin ' . $payload_ref['id'] . ' 2>&1'));
  }

  public function execute_tags_script($branch, $payload_ref, &$output) {
    // Check that tag name starts with 'stage-' for example.
    if ($this->check_payload($branch, (strpos($payload_ref['id'], $branch['branch_name'] . '-') === 0))) {
      $dir = $this->execute_script_start($branch);
      $this->execute_git_checkout($payload_ref, $output);
      $this->execute_script_end($branch, $output, $dir);
    }
  }

  public function execute_drush_commands($branch, &$output) {
    // Had to ln -s /var/aegir/.drush /var/www/.drush to get it to work.
    // @todo: figure out how to get proper log messages regarding domain rather
    // than hostmaster, then do automated rollback if there is an error.
    if ($branch['domain']) {
//      $output[] = trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush --verbose @hostmaster hosting-task @' . $branch['domain'] . ' backup 2>&1'));
//      $output[] = trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush --verbose @' . $branch['domain'] . ' updatedb 2>&1'));
//      $output[] = trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush --verbose @hostmaster hosting-task @' . $branch['domain'] . ' verify 2>&1'));
      if ($branch['type'] === 'tags') {
        $backup_output = trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush -v @' . $branch['domain'] . ' provision-backup 2>&1'));
        $output[] = $backup_output;
      }
      $output[] = trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush -v @' . $branch['domain'] . ' updatedb 2>&1'));
      $verify_output = trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush -v @' . $branch['domain'] . ' provision-verify 2>&1'));
      $output[] = $verify_output;

      // Check for an error in verification, if we found one then do a restore
      // from the earlier database backup.
      if (strpos($verify_output, '[error]')) {
        preg_match('@Backed up site up to ([\/\w\d\.]+\-[\d]{8}\.[\d]{6}\.tar\.gz).@', $backup_output, $matches);
        if ($matches[1]) {
          trim(shell_exec('sudo -u ' . $branch['owner'] . ' TERM=dumb /usr/bin/drush -v @' . $branch['domain'] . ' provision-restore ' . $matches[1] . ' 2>&1'));
          mail($this->payload->repository->owner->email . ',' . $this->payload->pusher->email, '[GitHubHook Error] Site Verification failed on ' . $branch['domain'] . ' [' . $branch['title'] . ']', 'The site has been restored to the last backup located at ' . $matches[1]);
        }
      }
    }
  }

  public function execute_git_checkout($payload_ref, &$output) {
    $output[] = trim(shell_exec($this->git . ' fetch --tags 2>&1'));
    $output[] = trim(shell_exec($this->git . ' checkout ' . $payload_ref['type'] . '/' . $payload_ref['id'] . ' 2>&1'));
  }

  public function execute_script_start($branch) {
    $dir = getcwd();
    chdir($branch['git_folder']);

    return $dir;
  }

  public function execute_script_end($branch, &$output, $dir) {
    $rsync_command = '/usr/bin/rsync --delete -avze' . $this->rsync_exclusions() . $this->ensure_trailing_slash($branch['git_folder']) . ' ' . $this->ensure_trailing_slash($branch['doc_root']);
//    $rsync_command = '/var/www/GitHubHook/rsync-data.sh ' . $this->ensureTrailingSlash($branch['git_folder']) . ' ' . $this->ensure_trailing_slash($branch['doc_root']);
    $output[] = trim(shell_exec('sudo -u ' . $branch['owner'] . ' ' . $rsync_command . ' 2>&1'));
    chdir($dir);
    $this->execute_drush_commands($branch, $output);
  }

  public function ensure_trailing_slash($directory) {
    return preg_replace('@([^/]$)@', '$1/', $directory);
  }

  public function rsync_exclusions() {
    $exclude_list = array(
      '/.git/',
      '/.gitignore',
      '/drushrc.php',
      '/files/',
      '/modules/development/',
      '/private/',
      '/settings.php',
    );
    $excludes = ' ';

    foreach ($exclude_list as $exclude) {
      $excludes .= '--filter="-rsp_' . $exclude . '" ';
    }

    return $excludes;
  }

  /**
   * Deploys.
   */
  public function deploy() {
    if ($this->valid_ip()) {
      $branch = $this->get_branch();

      if ($branch) {
        $this->process_payload($branch);
      }
    }
    else {
      $this->not_found('IP address not recognized: ' . $this->remote_ip);
    }
  }
}
