<?php
/**
 * common.php ���õĹ�������
 *
 * ������
 *     ��������
 *     ��־�ӿ�
 */

/**
 * ---------------��������--------------------
 */

// һ�������
define("COMM_SECONDS_ONEDAY", 24 * 60 * 60);

/**
 * �ж��Ƿ��Ǻ�̨������ģʽ
 * @return bool ����true�Ǻ�̨ģʽ��false����
 */
function comm_is_cli() {
    $sapi_type = php_sapi_name();
    if ($sapi_type == "cli") {
        return true;
    }
    return false;
}

/**
 * �ж��Ƿ�������ǰ��ҳ�������
 * @return bool ����true������ǰ��ҳ�������false����
 */
function comm_is_bgs_request() {
    if (comm_is_cli()) {
        return false;
    }
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '127.0.0.2') {
        return true;
    }
    return false;
}

/**
 * ��ȡ�����в���
 * @return array ���������в���
 */
function comm_argv_get() {
    if (comm_is_cli()) {
        global $argv;
        return $argv;
    }
    $args = array($_SERVER['PHP_SELF']);
    return $args;
}

/**
 * ����Ϊ�ٽ�������
 * @param bool   $cond �ж�����
 * @param string $str  ������־
 * @return
 */
function comm_assert($cond, $str) {
    if ($cond) {
        return;
    }
    $trace = debug_backtrace();
    $file  = str_replace(realpath(WEB_PHP_ROOT),
        "", $trace[0]["file"]);
    $line  = $trace[0]["line"];
    log_warn("assert $file:$line");
    log_error($str);
    die;
}

/**
 * ��ȡlog���̵�ID
 * @return int ���ؽ���ID
 */
function comm_process_id() {
    return getmypid();
}

/**
 * ִ���ⲿ����
 * @param string $command ִ������
 * @param array  $output  �����Ϣ
 * @param int    $ret     ����ֵ
 * @return string ����ִ�н��
 */
function comm_exec($command, &$output, &$ret) {
    pcntl_signal(SIGCHLD, SIG_DFL);
    $data = exec($command, $output, $ret);
    pcntl_signal(SIGCHLD, SIG_IGN);
    return $data;
}

/**
 * ����Ŀ¼���������Ŀ¼ʱ�����ļ��Ѵ��ڵĴ���
 * @param string $dir Ŀ¼
 * @return bool ���سɹ�����ʧ��
 */
function comm_mkdir($dir, $log_enable = true) {
    $limit = 1000; // ����1000��
    for ($i = 0; $i < $limit; ++$i) {
        if (is_dir($dir) || @mkdir($dir, 0777, true)) {
            return true;
        }
        $err = log_get_last_error();
        if (!strstr($err, "File exists")
            && !strstr($err, "No such file or directory")) {
            if ($log_enable) log_warn("mkdir $dir error, $err");
            return false;
        }
        usleep(10000); // ˯��10����
    }
    return false;
}

/**
 * �ݹ�ɾ��Ŀ¼
 * @param string $dir Ŀ¼
 * @return bool ���سɹ�����ʧ��
 */
function comm_rrmdir($dir) {
    // ��������Ҫ��ɾ���������ӣ���ɾ����ʵ·��
    if (is_link($dir)) {
        $real_dir = realpath($dir);
        return unlink($dir) && log_rrmdir($real_dir);
    }

    // ������ļ�ֱ��ɾ��
    if (is_file($dir)) {
        return unlink($dir);
    }

    // ��������ļ����Ƿ�������Ҳ����Ŀ¼��ֱ�ӷ���
    if (!is_dir($dir)) {
        return true;
    }

    // �ų�.��..
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        if (!comm_rrmdir("$dir/$file")) {
            return false;
        }
    }
    return rmdir($dir);
}

/**
 * �����ļ�
 * @param string $src ԭʼ�ļ�
 * @param string $dst Ŀ���ļ�
 * @return bool ���سɹ�����ʧ��
 */
function comm_copy_file($src, $dst, $overWrite = false) {
    if (!file_exists($src)) {
        return false;
    }
    if (file_exists($dst) && $overWrite == false) {
        return false;
    } elseif (file_exists($dst) && $overWrite == true) {
        if (!unlink($dst)) {
            return false;
        }
    }
    $dst_dir = dirname($dst);
    if (!comm_mkdir($dst_dir)) {
        return false;
    }
    if (!copy($src, $dst)) {
        return false;
    }
    return true;
}


/**
 * ����Ŀ¼
 * @param string $src ԭʼ�ļ�����Ŀ¼
 * @param string $dst Ŀ���ļ�����Ŀ¼
 * @return bool ���سɹ�����ʧ��
 */
function comm_copy_dir($src, $dst, $overWrite = false) {
    if (!is_dir($src)) {
        return false;
    }
    if (!file_exists($dst)) {
        if (!log_mkdir($dst)) {
            return false;
        }
    }
    $files = array_diff(scandir($src), array('.', '..'));
    foreach ($files as $file) {
        if (!is_dir("$src/$file")) {
            if (!comm_copy_file("$src/$file", "$dst/$file", $overWrite)) {
                return false;
            }
        } else {
            if (!comm_copy_dir("$src/$file", "$dst/$file", $overWrite)) {
                return false;
            }
        }
    }
    return true;
}

/**
 * �����������
 * @param string $link   ����
 * @param strign $target Ŀ��Ŀ¼
 * @return bool ���ع����ɹ�����ʧ��
 */
function comm_mklink($link, $target) {
    if (file_exists($link)) {
        return true;
    }
    if (!file_exists($target) && !log_mkdir($target)) {
        return false;
    }

    $cmd = "ln -s \"$target\" \"$link\"";
    comm_exec($cmd, $output, $ret);
    if ($ret != 0) {
        log_info("mklink failed, $link, $target, ".implode("|", $output));
    }
    return ($ret == 0);
}

/**
 * ����param1��param2 �Ĳ�ֵ�ľ���ֵ
 * @param int/float $param1 ��һ������
 * @param int/float $param2 �ڶ�������
 * @return int/float ��ֵ�ľ���ֵ
 */
function comm_abs_diff($param1, $param2) {
    return abs($param1 - $param2);
}

/**
 * ͳһ��·��
 * @param string $path ��·��
 * @return string ����ͳһ��ı�·��
 */
function comm_path_unique($path) {
    return implode(":",
        preg_split("/[\\\\\\/]+/", strtolower(trim($path, "/\\"))));
}

/**
 * �зֺ�ü��հ��ַ�
 * @param string $delim �ָ��ַ�
 * @param string $str   �ַ���
 * @return array �����зֺ������
 */
function comm_explode($delim, $str) {
    return array_map('trim',explode($delim,$str));
}


/**
 * ����ת��������֧������ݹ�ת������������ֵ
 * @param mix      $mix  ֧��������ַ���
 * @param function $func �ص�����
 * @return mix ���ر���������
 */
function comm_code_transfer($mix, $func) {
    if (!is_array($mix)) {
        if (is_string($mix)) {
            return $func($mix);
        } else {
            return $mix;
        }
    }
    $out = array();
    foreach ($mix as $key => $value) {
        $key = $func($key);
        $out[$key] = comm_code_transfer($value, $func);
    }
    return $out;
}

/**
 * ֧��url�����json����
 * @param array $arr ��Ҫjson���������
 * @return string ���ر������ַ���
 */
function comm_json_encode($arr) {
    $arr = comm_code_transfer($arr, "urlencode");
    return json_encode($arr);
}

/**
 * ���ַ������н��룬ͨ��url����Ȼ���ڽ���77���������
 * @param string $string �����ַ���
 * @return string ���ؽ��ܺ���ַ���
 */
function comm_dectrypt($string) {
    /*bug52764 ǰ��Ϊ���ƹ�mod_security���У�������2�α��룬��Ӧ������ҪŪ��2�ν���*/
    $data = rawurldecode(rawurldecode(rawurldecode($string)));
    $key = 77;
    $dst = '';
    for( $i = 0; $i<strlen($data); $i++ ) {
        $dst .= chr( ord($data[$i]) ^ $key  );
    }
    return rawurldecode($dst);
}

/**
 * ���ַ������б���
 * @param string $string �����ַ���
 * @return string ���ؼ��ܺ���ַ���
 */
function comm_enctrypt($string){
    $data = rawurlencode($string);
    $key = 77;
    $dst = '';
    for( $i = 0; $i<strlen($data); $i++ ){
        $dst .= chr( ord($data[$i]) ^ $key  );
    }
    /*bug52764 ǰ��Ϊ���ƹ�mod_security���У�������2�ν��룬��Ӧ������ҪŪ��2�α���*/
    return rawurlencode(rawurlencode(rawurlencode($dst)));
}

/**
 * ֧��url�����json����
 * @param string $str ��Ҫjson������ַ���
 * @return array ���ؽ��������飬������ǺϷ�json����null
 */
function comm_json_decode($str) {
    $arr = json_decode($str, true);
    if (is_null($arr)) {
        return null;
    }
    return comm_code_transfer($arr, "urldecode");
}

/**
 * ����ini�����ļ�������ini�ļ��д��������ַ�
 * @param string    $ini_file ini�����ļ�
 * @param bool      $process_sections �Ƿ���section��Ϣ
 * @return array
 */
function comm_parse_ini_file($ini_file, $process_sections = false) {
    $result = array();
    if (!file_exists($ini_file)) {
        return $result;
    }
    $content = file_get_contents($ini_file);
    $content = str_replace("\r", "", $content);
    $lines = explode("\n", $content);
    $key_first = "";
    foreach($lines as $one_line) {
        // ����ע����
        $one_line = trim($one_line);
        if (empty($one_line)) {
            continue;
        }
        if (preg_match("/^[;#].*/", $one_line) > 0) {
            continue;
        }
        // һ���±�
        if (preg_match("/^\[.*\].*/", $one_line) > 0) {
            $key_first = trim($one_line, "]");
            $key_first = trim($key_first, "[");
        } else{
            $key_vals = explode("=", $one_line);
            $key = array_shift($key_vals);
            $value = implode("=", $key_vals);
            $value = trim($value);
            $key = trim($key);
            if ($key_first == "") {
               $result["$key"] = $value;
            } else{
                // �Ƿ�Ҫ����section
                if ($process_sections == true) {
                    $result["$key_first"]["$key"] = $value;
                } else {
                    $result["$key"] = $value;
                }
            }
        }
    }
    return $result;
}

/**
 * ����ini�ļ�
 * @param array $ini_val value����
 * @param string $ini_conf ini�ļ�
 * @return true����ɹ���false����ʧ��
*/
function comm_ini_write($ini_val, $ini_conf) {
    $res = array();
    if (!file_exists(dirname($ini_conf))) {
        return false;
    }
    foreach($ini_val as $key => $val){
        if(is_array($val)) {
            $res[] = "[$key]";
            foreach($val as $skey => $sval){
                $res[] = "$skey = ".(is_numeric($sval) ? $sval : ''.$sval.'');
            }
        } else {
             $res[] = "$key = ".(is_numeric($val) ? $val : ''.$val.'');
        }
    }

    $make_line_chars = PHP_EOL;
    $ret = file_put_contents($ini_conf, implode($make_line_chars, $res));
    if ($ret === false) {
        return false;
    }
    return true;
}

/**
 * дINI�ļ�
 * $param array  $ini  ��������, ��һ��section, �ڶ���key/value��
 * @param string $path ���·��
 * @return bool
 */
function comm_write_ini($ini, $path) {
    $handle = fopen($path, "w");
    if (!$handle) {
        return false;
    }
    foreach ($ini as $section => $pairs) {
        if (false === fprintf($handle, "[%s]\r\n", $section)) {
            fclose($handle);
            return false;
        }
        foreach ($pairs as $key => $val) {
            if (false === fprintf($handle, "%s = %s\r\n", $key, $val)) {
                fclose($handle);
                return false;
            }
        }
    }
    fclose($handle);
    return true;
}

/**
 *  �ж��Ƿ�Ϊ�Ϸ������ڸ�ʽ
 * @param string $date ����
 * @return bool �����Ƿ�Ϊ�Ϸ�����
 */
function comm_is_valid_date($date) {
    return preg_match('/^[0-9]{8}$/', $date);
}

/**
 * ��ȡָ�����ڵ���һ������
 * @param string $date ����
 * @return string ������ڲ��Ϸ�����false
 */
function comm_tomorrow($date) {
    if (!comm_is_valid_date($date)) {
        return false;
    }
    return date("Ymd", strtotime($date) + COMM_SECONDS_ONEDAY);
}

/**
 * �з�����
 * @param array $list �зֵ�����
 * @param int   $part �з���
 * @return array �����зֺ�����飬����$part��������
 */
function comm_partition($list, $part) {
    $list_len   = count($list);
    $part_len   = floor($list_len / $part);
    $part_rem   = $list_len % $part;
    $partition  = array();
    $mark = 0;
    for ($i = 0;$i < $part;$i++) {
        $incr = ($i < $part_rem) ? $part_len + 1 : $part_len;
        $partition[$i] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    return $partition;
}

/**
 * ---------------��־�ӿ�--------------------
 */
define("WEB_PHP_ROOT", "/home/wangyubo/work/codeFiles/php/www");    // ��վphp��Ŀ¼
define("LOG_FLAG_PATH", "/home/wangyubo");                          // ��־����ļ�Ŀ¼
define("LOG_FILE_PATH", "/home/wangyubo/phplog");                   // ��־�ļ�����Ŀ¼
define("MAX_BACKTRACE_DEEP", 10);                                   // ����ջ��С
define("CONSOLE_LOG_PARAM", "-log");                                // �ն���־��ӡ����
define("log_MAX_LOGSIZE", 5 * 1024 * 1024);                         // ��־�ļ���С����ౣ��5M������5M���������������5M��־���Լ����²�������־��5M���£�

/**
 * ȫ�ֵ��Կ��أ�Ĭ������������php����Ŀ¼�Ƿ����debug_flag�ļ�
 */
$log_debug_enable = false;

/**
 * ����Ƿ�Ҫ����
 */
function log_debug_check() {
    global $log_debug_enable;
    $log_debug_enable = file_exists(LOG_FLAG_PATH."/debug_flag");
}
log_debug_check();

/**
 * ȫ���ն���־���أ�����������php���޴���-app����
 */
$log_has_console = in_array(CONSOLE_LOG_PARAM, comm_argv_get());

/**
 * ���������Ϊ�ַ����Ĳ���
 * @param string $str �����־
 * @return
 */
function log_echo($str) {
    global $log_has_console;
    if ($log_has_console) {
        echo $str;
    }
    log_echo_file($str);
}

/**
 * �ж��Ƿ��ǵ���ģʽ
 * @return bool ����ģʽ����true,���򷵻�False
 */
function log_is_debug() {
    if (file_exists(LOG_FLAG_PATH . "/debug_flag")) {
        return true;
    }
    return false;
}

/**
 * ��ӡ��־�Լ�������Ϣ
 * @param int $deep ��ջ���
 * @return
 */
function log_bt($deep = MAX_BACKTRACE_DEEP) {
    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();
    // �������ű�֤�����
    $trace = str_replace("[" . realpath(WEB_PHP_ROOT), "[", $trace);
    log_echo("---backtrace---" . PHP_EOL . $trace . PHP_EOL);
}

/**
 * ��ӡ��־����������
 * @param string $str �����־
 * @return
 */
function log_die($str) {
    $trace = debug_backtrace();
    $file  = str_replace(realpath(WEB_PHP_ROOT),
        "", $trace[0]["file"]);
    $line  = $trace[0]["line"];
    log_warn("die $file:$line");
    log_error($str." error ".log_get_last_error());
    die;
}

/**
 * ��ӡ������־
 * @return
 */
function log_debug() {
    global $log_debug_enable;
    if (!$log_debug_enable) {
        return;
    }
    $input = func_get_args();
    log_print("D", $input);
}

/**
 * ��ӡ��Ϣ��־��֧�ֱ仯���������������Ϊ�ַ����Ĳ���
 * @return
 */
function log_info() {
    $input = func_get_args();
    log_print("I", $input);
}

/**
 * ��ӡ������־��֧�ֱ仯���������������Ϊ�ַ����Ĳ���
 * @return
 */
function log_error() {
    $input = func_get_args();
    log_print("E", $input);
    log_bt(MAX_BACKTRACE_DEEP);
}

/**
 * ��ӡ�澯��־��֧�ֱ仯���������������Ϊ�ַ����Ĳ���
 * @return
 */
function log_warn() {
    $input = func_get_args();
    log_print("W", $input);
}

/**
 * ��־д���Ŀ¼
 * @return string ��־Ŀ¼·��
 */
function log_output_dir() {
    $dir = LOG_FILE_PATH;
    if (!file_exists($dir) && !comm_mkdir($dir, false)) {
        if (log_is_cli()) {
            fprintf(STDERR, "make dir $dir failed\n");
        }
        return null;
    }
    return $dir;
}

/**
 * ��־�ļ�·��
 * @return string ��־�ļ�·��
 */
function log_filename() {
    $dir = log_output_dir();
    if ($dir === null) {
        $argv = comm_argv_get();
        return basename($argv[0], ".php")."_"
            .log_process_id().".log";
    }
    return "$dir/".date('Ymd').".log";
}

/**
 * ȫ����־���
 */
$log_handle = NULL;

function log_ext_redirect_stderr($handle) {
    if ($handle) {
        @fclose(STDERR);
        $STDERR = $handle;
    }
}

/**
 * ��ӡ��־
 * @param string $type   ����, i/e/w/d
 * @param array  $inputs ��ӡ����
 * @return
 */
function log_print($type, $inputs) {
    $trace = debug_backtrace(3);
    for ($i = 1; $i < count($trace); ++$i) {
        if (array_key_exists("file", $trace[$i])
            && array_key_exists("line", $trace[$i])) {
            break;
        }
    }
    if ($i < count($trace)) {
        $file  = str_replace(realpath(WEB_PHP_ROOT),
            "", $trace[$i]["file"]);
        $line  = $trace[$i]["line"];
        $func = isset($trace[$i + 1]) ? $trace[$i + 1]["function"] : "null";
    }
    if (comm_is_cli()) {
        log_echo(date("Y-m-d H:i:s")."[$type][$file:$line $func] ");
    } else {
        $pid = comm_process_id();
        log_echo(date("Y-m-d H:i:s")."[$type][$pid][$file:$line $func] ");
    }

    foreach ($inputs as $value) {
        if (!is_string($value)) {
            continue;
        }
        // �����ӡ�����ݳ���64K��ѹ�����ӡ
        if (strlen($value) > 65536) {
            $value = base64_encode(gzcompress($value));
        }
        log_echo($value);
    }
    log_echo(PHP_EOL);
}

/**
 * ��ӡ��־
 * @param string $str ��־
 * @return
 */
function log_echo_file($str) {
    global $log_handle;
    static $file = "";
    static $file_size = 0;
    if (is_null($log_handle)) {
        $file = log_filename();
        $log_handle = fopen($file, "a");
        if (file_exists($file)) {
            $file_size = filesize($file);
        } else {
            $file_size = 0;
        }
        log_ext_redirect_stderr($log_handle);
    } else {
        // ��־�ļ���С����5M�����������5M��־
        if ($file_size > log_MAX_LOGSIZE) {
            fclose($log_handle);
            $file = log_filename();
            $clone = dirname($file)."/".basename($file, ".log")."_clone.log";
            if (file_exists($file) && !file_exists($clone)) {
                @rename($file, $clone);
            }
            $log_handle = fopen($file, "w");
            log_ext_redirect_stderr($log_handle);
            $file_size = 0;
        }
        // �����л���־�ļ�
        if (basename($file, ".log") != date("Ymd")) {
            fclose($log_handle);
            $file = log_filename();
            $log_handle = fopen($file, "w");
            log_ext_redirect_stderr($log_handle);
            $file_size = 0;
        }
    }
    if ($log_handle === false) {
        if (log_is_cli()) {
            fprintf(STDERR, "open $file fail, ".serialize(error_get_last()));
        }
        $log_handle = null;
        return;
    }
    $file_size += strlen($str);
    fprintf($log_handle, "%s", $str);
    fflush($log_handle);
}

/**
 * ȫ�ֵ�������
 */
$log_error_msg = null;

/**
 * ��ȡphp��������
 * @return string
 */
function log_get_last_error() {
    global $log_error_msg;
    return $log_error_msg;
}

/**
 * ��ʼ��log�Ĵ�����
 * @return
 */
function log_error_handler_init() {
    set_error_handler("log_error_handle");
    set_exception_handler("log_exception_handle");
    register_shutdown_function("log_fatal_error_handler");
}

/**
 * ��ȡ���ĸ澯�ʹ�����Ϣ
 * @return array ������־����
 */
function log_get_last_exception_log() {
    $log_file = log_filename();
    if (!file_exists($log_file)) {
        return array();
    }
    $rows = file($log_file, FILE_IGNORE_NEW_LINES);
    $rets = array();
    foreach ($rows as $row) {
        if (preg_match('/\d{2}\s[e|w]\s/i', $row)) {
            $rets[] = $row;
        }
    }
    return array_slice($rets, -2, 2);
}

/**
 * PHP��������
 * @param int $errno �������
 * @param string $errmsg ������Ϣ
 * @param string $file ���������ļ�
 * @param int $line �����к�
 * @return bool
 */
function log_error_handle($errno, $errmsg, $file, $line) {
    global $log_error_msg;
    $log_error_msg = $errmsg;

    // ������󱨸��Ѿ��رջ���ִ�����ǰ�����@����error_reporting()����0������Ҫ�����������
    if (0 == error_reporting()) {
        return true;
    }
    $msg = "$file:$line $errmsg";
    switch ($errno) {
    case E_ERROR:
        log_die($msg);
        break;
    case E_WARNING:
        log_warn($msg);
        break;
    case E_NOTICE:
        log_warn($msg);
        break;
    default:
        break;
    }
    return true;
}

/**
 * PHP�쳣������
 * @param mixed $exception �쳣
 * @return
 */
function log_exception_handle($exception) {
    $file = $exception->getFile();
    $line = $exception->getLine();
    $msg  = $exception->getMessage();
    log_error("$file:$line, $msg");
    log_error("Stack trace:");
    $trace = $exception->getTraceAsString();
    $trace = explode("\n", $trace);
    foreach ($trace as $item) {
        log_error($item);
    }
}

/**
 * ����fatal error�����ڴ治�㣬�ű�����ȵ�
 * @return
 */
function log_fatal_error_handler() {
    $err = error_get_last();
    if ($err) {
        log_die("fatal error: ".serialize($err));
    }
}

/**
 * дsyslog
 * @param string $addr      ���͵�ַ������IP��ַ�Ͷ˿ڣ���:�ָ�����"127.0.0.1:514"
 * @param int    $priority  ���ȼ�
 * @param string $tag       ��ǩ
 * @param string $msg       ��Ϣ����ʾһ������
 * @param string $err       ����������󣬷��ش�����Ϣ
 * @return bool д��ɹ�����true��д��ʧ�ܷ���false
 */
function log_syslog($addr, $priority, $tag, $msg, &$err) {
    $socket = @stream_socket_client("tcp://$addr", $eno, $estr, 60);
    if (!$socket) {
        $err = "[$eno]:$estr";
        return false;
    }
    $hostname = gethostname();
    if (!$hostname) {
        $hostname = "localhost";
    }
    $msg = rtrim($msg, "\n");
    $timestamp = date('M d H:i:s');
    fprintf($socket, "<%d>%s %s %s[%d]: %s%s",
        $priority, $timestamp, $hostname, $tag, getmypid(), $msg, "\n");
    fclose($socket);
    return true;
}

?>
