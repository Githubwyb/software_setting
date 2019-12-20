<?php
/**
 * common.php 有用的公共函数
 *
 * 包括：
 *     公共函数
 *     日志接口
 */

/**
 * ---------------公共函数--------------------
 */

// 一天的秒数
define("COMM_SECONDS_ONEDAY", 24 * 60 * 60);

/**
 * 判断是否是后台命令行模式
 * @return bool 返回true是后台模式，false不是
 */
function comm_is_cli() {
    $sapi_type = php_sapi_name();
    if ($sapi_type == "cli") {
        return true;
    }
    return false;
}

/**
 * 判断是否是来自前端页面的请求
 * @return bool 返回true是来自前端页面的请求，false不是
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
 * 获取命令行参数
 * @return array 返回命令行参数
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
 * 条件为假结束进程
 * @param bool   $cond 判断条件
 * @param string $str  结束日志
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
 * 获取log进程的ID
 * @return int 返回进程ID
 */
function comm_process_id() {
    return getmypid();
}

/**
 * 执行外部程序
 * @param string $command 执行命令
 * @param array  $output  输出信息
 * @param int    $ret     返回值
 * @return string 返回执行结果
 */
function comm_exec($command, &$output, &$ret) {
    pcntl_signal(SIGCHLD, SIG_DFL);
    $data = exec($command, $output, $ret);
    pcntl_signal(SIGCHLD, SIG_IGN);
    return $data;
}

/**
 * 创建目录，解决创建目录时出现文件已存在的错误
 * @param string $dir 目录
 * @return bool 返回成功还是失败
 */
function comm_mkdir($dir, $log_enable = true) {
    $limit = 1000; // 限制1000次
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
        usleep(10000); // 睡眠10毫秒
    }
    return false;
}

/**
 * 递归删除目录
 * @param string $dir 目录
 * @return bool 返回成功还是失败
 */
function comm_rrmdir($dir) {
    // 符号链接要先删除符号链接，再删除真实路径
    if (is_link($dir)) {
        $real_dir = realpath($dir);
        return unlink($dir) && log_rrmdir($real_dir);
    }

    // 如果是文件直接删除
    if (is_file($dir)) {
        return unlink($dir);
    }

    // 如果不是文件不是符号链接也不是目录，直接返回
    if (!is_dir($dir)) {
        return true;
    }

    // 排除.和..
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        if (!comm_rrmdir("$dir/$file")) {
            return false;
        }
    }
    return rmdir($dir);
}

/**
 * 拷贝文件
 * @param string $src 原始文件
 * @param string $dst 目标文件
 * @return bool 返回成功还是失败
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
 * 拷贝目录
 * @param string $src 原始文件或者目录
 * @param string $dst 目标文件或者目录
 * @return bool 返回成功还是失败
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
 * 构造符号连接
 * @param string $link   链接
 * @param strign $target 目标目录
 * @return bool 返回构建成功还是失败
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
 * 给出param1和param2 的差值的绝对值
 * @param int/float $param1 第一个参数
 * @param int/float $param2 第二个参数
 * @return int/float 差值的绝对值
 */
function comm_abs_diff($param1, $param2) {
    return abs($param1 - $param2);
}

/**
 * 统一表路径
 * @param string $path 表路径
 * @return string 返回统一后的表路径
 */
function comm_path_unique($path) {
    return implode(":",
        preg_split("/[\\\\\\/]+/", strtolower(trim($path, "/\\"))));
}

/**
 * 切分后裁剪空白字符
 * @param string $delim 分隔字符
 * @param string $str   字符串
 * @return array 返回切分后的数组
 */
function comm_explode($delim, $str) {
    return array_map('trim',explode($delim,$str));
}


/**
 * 编码转换，可以支持数组递归转换，包括键和值
 * @param mix      $mix  支持数组和字符串
 * @param function $func 回调函数
 * @return mix 返回编码后的数据
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
 * 支持url编码的json编码
 * @param array $arr 需要json编码的数组
 * @return string 返回编码后的字符串
 */
function comm_json_encode($arr) {
    $arr = comm_code_transfer($arr, "urlencode");
    return json_encode($arr);
}

/**
 * 对字符串进行解码，通过url编码然后在进行77进行异或处理
 * @param string $string 操作字符串
 * @return string 返回解密后的字符串
 */
function comm_dectrypt($string) {
    /*bug52764 前端为了绕过mod_security误判，进行了2次编码，对应的这里要弄成2次解码*/
    $data = rawurldecode(rawurldecode(rawurldecode($string)));
    $key = 77;
    $dst = '';
    for( $i = 0; $i<strlen($data); $i++ ) {
        $dst .= chr( ord($data[$i]) ^ $key  );
    }
    return rawurldecode($dst);
}

/**
 * 对字符串进行编码
 * @param string $string 操作字符串
 * @return string 返回加密后的字符串
 */
function comm_enctrypt($string){
    $data = rawurlencode($string);
    $key = 77;
    $dst = '';
    for( $i = 0; $i<strlen($data); $i++ ){
        $dst .= chr( ord($data[$i]) ^ $key  );
    }
    /*bug52764 前端为了绕过mod_security误判，进行了2次解码，对应的这里要弄成2次编码*/
    return rawurlencode(rawurlencode(rawurlencode($dst)));
}

/**
 * 支持url解码的json解码
 * @param string $str 需要json解码的字符串
 * @return array 返回解码后的数组，如果不是合法json返回null
 */
function comm_json_decode($str) {
    $arr = json_decode($str, true);
    if (is_null($arr)) {
        return null;
    }
    return comm_code_transfer($arr, "urldecode");
}

/**
 * 解析ini配置文件，避免ini文件中存在特殊字符
 * @param string    $ini_file ini配置文件
 * @param bool      $process_sections 是否处理section信息
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
        // 跳过注释行
        $one_line = trim($one_line);
        if (empty($one_line)) {
            continue;
        }
        if (preg_match("/^[;#].*/", $one_line) > 0) {
            continue;
        }
        // 一级下标
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
                // 是否要处理section
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
 * 保存ini文件
 * @param array $ini_val value数组
 * @param string $ini_conf ini文件
 * @return true代表成功，false代表失败
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
 * 写INI文件
 * $param array  $ini  两级数组, 第一级section, 第二级key/value对
 * @param string $path 输出路径
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
 *  判断是否为合法的日期格式
 * @param string $date 日期
 * @return bool 返回是否为合法日期
 */
function comm_is_valid_date($date) {
    return preg_match('/^[0-9]{8}$/', $date);
}

/**
 * 获取指定日期的下一天日期
 * @param string $date 日期
 * @return string 如果日期不合法返回false
 */
function comm_tomorrow($date) {
    if (!comm_is_valid_date($date)) {
        return false;
    }
    return date("Ymd", strtotime($date) + COMM_SECONDS_ONEDAY);
}

/**
 * 切分数组
 * @param array $list 切分的数组
 * @param int   $part 切分数
 * @return array 返回切分后的数组，包含$part个子数组
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
 * ---------------日志接口--------------------
 */
define("WEB_PHP_ROOT", "/home/wangyubo/work/codeFiles/php/www");    // 网站php根目录
define("LOG_FLAG_PATH", "/home/wangyubo");                          // 日志标记文件目录
define("LOG_FILE_PATH", "/home/wangyubo/phplog");                   // 日志文件储存目录
define("MAX_BACKTRACE_DEEP", 10);                                   // 最大堆栈大小
define("CONSOLE_LOG_PARAM", "-log");                                // 终端日志打印参数
define("log_MAX_LOGSIZE", 5 * 1024 * 1024);                         // 日志文件大小，最多保存5M，多于5M，保存最早产生的5M日志，以及最新产生的日志（5M以下）

/**
 * 全局调试开关，默认依赖于运行php所在目录是否存在debug_flag文件
 */
$log_debug_enable = false;

/**
 * 检查是否要调试
 */
function log_debug_check() {
    global $log_debug_enable;
    $log_debug_enable = file_exists(LOG_FLAG_PATH."/debug_flag");
}
log_debug_check();

/**
 * 全局终端日志开关，依赖于运行php有无带有-app参数
 */
$log_has_console = in_array(CONSOLE_LOG_PARAM, comm_argv_get());

/**
 * 仅输出类型为字符串的参数
 * @param string $str 输出日志
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
 * 判断是否是调试模式
 * @return bool 调试模式返回true,否则返回False
 */
function log_is_debug() {
    if (file_exists(LOG_FLAG_PATH . "/debug_flag")) {
        return true;
    }
    return false;
}

/**
 * 打印日志以及回溯信息
 * @param int $deep 堆栈深度
 * @return
 */
function log_bt($deep = MAX_BACKTRACE_DEEP) {
    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();
    // 加中括号保证不查错
    $trace = str_replace("[" . realpath(WEB_PHP_ROOT), "[", $trace);
    log_echo("---backtrace---" . PHP_EOL . $trace . PHP_EOL);
}

/**
 * 打印日志并结束进程
 * @param string $str 输出日志
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
 * 打印调试日志
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
 * 打印信息日志，支持变化参数，仅输出类型为字符串的参数
 * @return
 */
function log_info() {
    $input = func_get_args();
    log_print("I", $input);
}

/**
 * 打印错误日志，支持变化参数，仅输出类型为字符串的参数
 * @return
 */
function log_error() {
    $input = func_get_args();
    log_print("E", $input);
    log_bt(MAX_BACKTRACE_DEEP);
}

/**
 * 打印告警日志，支持变化参数，仅输出类型为字符串的参数
 * @return
 */
function log_warn() {
    $input = func_get_args();
    log_print("W", $input);
}

/**
 * 日志写入的目录
 * @return string 日志目录路径
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
 * 日志文件路径
 * @return string 日志文件路径
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
 * 全局日志句柄
 */
$log_handle = NULL;

function log_ext_redirect_stderr($handle) {
    if ($handle) {
        @fclose(STDERR);
        $STDERR = $handle;
    }
}

/**
 * 打印日志
 * @param string $type   类型, i/e/w/d
 * @param array  $inputs 打印内容
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
        // 如果打印的数据超过64K，压缩后打印
        if (strlen($value) > 65536) {
            $value = base64_encode(gzcompress($value));
        }
        log_echo($value);
    }
    log_echo(PHP_EOL);
}

/**
 * 打印日志
 * @param string $str 日志
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
        // 日志文件大小超过5M，保存最早的5M日志
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
        // 跨天切换日志文件
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
 * 全局的最后错误
 */
$log_error_msg = null;

/**
 * 获取php的最后错误
 * @return string
 */
function log_get_last_error() {
    global $log_error_msg;
    return $log_error_msg;
}

/**
 * 初始化log的错误处理
 * @return
 */
function log_error_handler_init() {
    set_error_handler("log_error_handle");
    set_exception_handler("log_exception_handle");
    register_shutdown_function("log_fatal_error_handler");
}

/**
 * 获取最后的告警和错误信息
 * @return array 返回日志数组
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
 * PHP错误处理函数
 * @param int $errno 错误类别
 * @param string $errmsg 错误信息
 * @param string $file 错误发生的文件
 * @param int $line 错误行号
 * @return bool
 */
function log_error_handle($errno, $errmsg, $file, $line) {
    global $log_error_msg;
    $log_error_msg = $errmsg;

    // 如果错误报告已经关闭或者执行语句前面加了@，则error_reporting()返回0，这是要忽略这个错误
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
 * PHP异常处理函数
 * @param mixed $exception 异常
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
 * 处理fatal error，如内存不足，脚本错误等等
 * @return
 */
function log_fatal_error_handler() {
    $err = error_get_last();
    if ($err) {
        log_die("fatal error: ".serialize($err));
    }
}

/**
 * 写syslog
 * @param string $addr      发送地址，包括IP地址和端口，以:分隔，如"127.0.0.1:514"
 * @param int    $priority  优先级
 * @param string $tag       标签
 * @param string $msg       消息，表示一行数据
 * @param string $err       如果发生错误，返回错误信息
 * @return bool 写入成功返回true，写入失败返回false
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
