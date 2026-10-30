<?php
// Local-only Laravel installer backend. Delete this folder (or lock it) after use.

set_time_limit(0);
ini_set('display_errors', '0');
ini_set('zlib.output_compression', '0');

const LOCK_FILE = __DIR__ . '/installer.lock';
const COMMAND_TIMEOUT = 1800;
const MAX_OUTPUT_BYTES = 200000;
const RESPONSE_OUTPUT_CHARS = 8000;
const JOB_CHUNK_BYTES = 65536;
const JOB_START_GRACE_SECONDS = 120;
const STATE_MAX_BYTES = 20000;
const STATE_TTL_SECONDS = 86400;
const MIN_DISK_FAIL_BYTES = 314572800;   // 300 MB
const MIN_DISK_WARN_BYTES = 1073741824;  // 1 GB

const LARAVEL_CONSTRAINTS = [
    '13.x' => '^13.0',
    '12.x' => '^12.0',
    '11.x' => '^11.0',
    '10.x' => '^10.0',
    '9.x'  => '^9.0',
    '8.x'  => '^8.0',
];

const LARAVEL_MIN_PHP = [
    '13.x' => '8.3',
    '12.x' => '8.2',
    '11.x' => '8.2',
    '10.x' => '8.1',
    '9.x'  => '8.0',
    '8.x'  => '7.3',
];

const REQUIRED_EXTENSIONS = [
    'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'json',
    'mbstring', 'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml',
];

const DB_DRIVER_EXTENSIONS = [
    'mysql'  => 'pdo_mysql',
    'pgsql'  => 'pdo_pgsql',
    'sqlite' => 'pdo_sqlite',
    'sqlsrv' => 'pdo_sqlsrv',
];

// Runs under the PHP CLI (the same PHP that runs artisan) so the check reflects the real environment.
// Credentials arrive through environment variables, never through the command line.
const DB_TOOL_SCRIPT = <<<'DBTOOL'
<?php
function out($ok, $message, $missing = false, $tables = null)
{
    echo PHP_EOL . json_encode(['ok' => $ok, 'message' => $message, 'db_missing' => $missing, 'tables' => $tables]) . PHP_EOL;
    exit($ok ? 0 : 1);
}

function countTables($pdo, $driver, $name)
{
    try {
        if ($driver === 'mysql') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
            $stmt->execute([$name]);
            return (int) $stmt->fetchColumn();
        }
        if ($driver === 'pgsql') {
            return (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema NOT IN ('pg_catalog', 'information_schema')")->fetchColumn();
        }
        return (int) $pdo->query('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES')->fetchColumn();
    } catch (Exception $e) {
        return null;
    }
}

function dsn($driver, $host, $port, $db)
{
    if ($driver === 'mysql') {
        return "mysql:host={$host};port={$port}" . ($db !== '' ? ";dbname={$db}" : '') . ';charset=utf8mb4';
    }
    if ($driver === 'pgsql') {
        return "pgsql:host={$host};port={$port};dbname=" . ($db !== '' ? $db : 'postgres');
    }
    return "sqlsrv:Server={$host},{$port}" . ($db !== '' ? ";Database={$db}" : '');
}

$driver = (string) getenv('INST_DRIVER');
$action = (string) getenv('INST_ACTION');
$host = (string) getenv('INST_HOST');
$port = (string) getenv('INST_PORT');
$name = (string) getenv('INST_DB');
$user = (string) getenv('INST_USER');
$pass = (string) getenv('INST_PASS');

if (!in_array($driver, ['mysql', 'pgsql', 'sqlsrv'], true)) {
    out(false, 'Unsupported database driver');
}
if (!preg_match('/\A[A-Za-z0-9_.$-]{1,64}\z/', $name)) {
    out(false, 'Invalid database name');
}
if (!extension_loaded('pdo_' . $driver)) {
    out(false, "The PHP CLI extension pdo_{$driver} is not enabled");
}

$options = [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    if ($action === 'test') {
        $pdo = new PDO(dsn($driver, $host, $port, $name), $user, $pass, $options);
        out(true, "Connected to {$name} successfully", false, countTables($pdo, $driver, $name));
    }

    if ($action === 'create') {
        if ($driver === 'sqlsrv') {
            out(false, 'Automatic database creation is not supported for SQL Server');
        }
        $pdo = new PDO(dsn($driver, $host, $port, ''), $user, $pass, $options);
        if ($driver === 'mysql') {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } else {
            $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
            $stmt->execute([$name]);
            if (!$stmt->fetchColumn()) {
                $pdo->exec('CREATE DATABASE "' . $name . '"');
            }
        }
        out(true, "Database {$name} is ready");
    }

    out(false, 'Unknown action');
} catch (PDOException $e) {
    $driverCode = (int) ($e->errorInfo[1] ?? 0);
    $missing = ($driver === 'mysql' && $driverCode === 1049)
        || ($driver === 'sqlsrv' && $driverCode === 4060)
        || ($driver === 'pgsql' && preg_match('/database ".*" does not exist/i', $e->getMessage()) === 1);
    out(false, 'Database error: ' . $e->getMessage(), $missing);
}
DBTOOL;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// ===== Helpers =====

function isWindows()
{
    return DIRECTORY_SEPARATOR === '\\';
}

// Logged data must never contain secrets (DB passwords, tokens).
function logDebug($message, $data = null)
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($data !== null) {
        $line .= ' | ' . json_encode($data);
    }
    @error_log($line . PHP_EOL, 3, sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel-installer.log');
}

function sendResponse($success, $message, $output = '', array $extra = [], $httpCode = 200)
{
    $output = (string) $output;
    if (strlen($output) > RESPONSE_OUTPUT_CHARS) {
        $output = '...' . substr($output, -RESPONSE_OUTPUT_CHARS);
    }

    http_response_code($httpCode);
    echo json_encode(
        array_merge(['success' => $success, 'message' => $message, 'output' => $output], $extra),
        JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

// The PHP chosen in the UI, already checked against the detected installations.
function selectedPhpPath($set = null)
{
    static $path = null;
    if ($set !== null) {
        $path = $set === '' ? null : $set;
    }
    return $path;
}

function phpBinary()
{
    $selected = selectedPhpPath();
    if ($selected) {
        return escapeshellarg($selected);
    }
    // Under Apache PHP_BINARY is httpd.exe, so fall back to the PATH entry.
    if (PHP_BINARY && preg_match('/^php(-cli)?(\.exe)?$/i', basename(PHP_BINARY))) {
        return escapeshellarg(PHP_BINARY);
    }
    return 'php';
}

// Puts the chosen PHP first on PATH so Composer and its @php scripts use it too.
function withPhpOnPath(array $env)
{
    $php = selectedPhpPath();
    if (!$php) {
        return $env;
    }
    $key = 'PATH';
    foreach ($env as $name => $value) {
        if (strtolower($name) === 'path') {
            $key = $name;
            break;
        }
    }
    $env[$key] = dirname($php) . (isWindows() ? ';' : ':') . ($env[$key] ?? '');
    return $env;
}

function commandEnv()
{
    $env = [
        'COMPOSER_NO_INTERACTION' => '1',
        'COMPOSER_PROCESS_TIMEOUT' => '0',
    ];
    if (!getenv('COMPOSER_HOME') && !getenv('HOME') && !getenv('APPDATA')) {
        $env['COMPOSER_HOME'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'composer-home';
    }
    return $env;
}

function readTail($file, $maxBytes)
{
    clearstatcache(true, $file);
    $size = @filesize($file);
    if (!$size) {
        return '';
    }
    $handle = fopen($file, 'rb');
    if ($size > $maxBytes) {
        fseek($handle, -$maxBytes, SEEK_END);
    }
    $data = stream_get_contents($handle);
    fclose($handle);
    return (string) $data;
}

function killProcessTree($process, $pid)
{
    if (isWindows()) {
        @exec('taskkill /F /T /PID ' . (int) $pid . ' 2>&1');
    } else {
        proc_terminate($process, 9);
    }
}

/**
 * Runs a short shell command and returns its combined stdout/stderr and real exit code.
 * Output goes to a temp file so polling works on Windows, where pipes cannot be non-blocking.
 */
function executeCommand($command, $workingDir = null, $timeout = COMMAND_TIMEOUT, array $extraEnv = [])
{
    logDebug('executeCommand', ['command' => $command, 'cwd' => $workingDir]);

    $logFile = tempnam(sys_get_temp_dir(), 'inst');
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['file', $logFile, 'w'],
        2 => ['redirect', 1],
    ];

    $env = getenv();
    if (!is_array($env)) {
        $env = [];
    }
    $env = withPhpOnPath(array_merge($env, commandEnv(), $extraEnv));

    // The extra outer quotes stop cmd.exe from mangling commands with several quoted arguments.
    $fullCommand = isWindows() ? 'cmd /C "' . $command . '"' : $command;

    $process = proc_open($fullCommand, $descriptors, $pipes, $workingDir, $env);
    if (!is_resource($process)) {
        @unlink($logFile);
        return ['output' => 'Failed to start the command', 'return_code' => -1];
    }
    fclose($pipes[0]);

    $start = time();
    $exitCode = -1;
    $timedOut = false;

    while (true) {
        $status = proc_get_status($process);
        if (!$status['running']) {
            // proc_get_status reports the exit code only once; proc_close would return -1 afterwards.
            $exitCode = $status['exitcode'];
            break;
        }
        if (time() - $start > $timeout) {
            $timedOut = true;
            killProcessTree($process, $status['pid']);
            break;
        }
        usleep(100000);
    }

    $closeCode = proc_close($process);
    if ($exitCode === -1) {
        $exitCode = $closeCode;
    }

    $output = readTail($logFile, MAX_OUTPUT_BYTES);
    @unlink($logFile);

    if ($timedOut) {
        $exitCode = -1;
        $output .= PHP_EOL . 'Command timed out after ' . $timeout . ' seconds.';
    }

    logDebug('executeCommand finished', ['return_code' => $exitCode, 'seconds' => time() - $start]);

    return ['output' => $output, 'return_code' => $exitCode];
}

function removeDirectory($dir, $parentDir)
{
    $realDir = realpath($dir);
    $realParent = realpath($parentDir);
    if ($realDir === false || $realParent === false || $realDir === $realParent
        || strpos($realDir, rtrim($realParent, '\\/') . DIRECTORY_SEPARATOR) !== 0) {
        return;
    }

    $command = isWindows()
        ? 'rmdir /s /q ' . escapeshellarg($realDir)
        : 'rm -rf ' . escapeshellarg($realDir);
    executeCommand($command);
}

// ===== Background jobs =====

function jobsRoot()
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel-installer-jobs';
}

function jobDir($id)
{
    if (!is_string($id) || !preg_match('/\A[a-f0-9]{16}\z/', $id)) {
        throw new Exception('Invalid job id');
    }
    return jobsRoot() . DIRECTORY_SEPARATOR . $id;
}

function cleanOldJobs()
{
    foreach (glob(jobsRoot() . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $dir) {
        if (filemtime($dir) < time() - 86400) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
    }
}

/**
 * Starts a command detached from the web request. Output goes to output.log and the
 * exit code is written to exit.code when the command ends.
 */
function startJob($type, $command, $workingDir, array $meta = [])
{
    cleanOldJobs();

    // A job runs one command, or a list of ['label' => ..., 'cmd' => ...] that stops at the first failure.
    $steps = is_array($command) ? $command : [['label' => '', 'cmd' => $command]];

    $id = bin2hex(random_bytes(8));
    $dir = jobsRoot() . DIRECTORY_SEPARATOR . $id;
    if (!mkdir($dir, 0700, true)) {
        throw new Exception('Could not create the job directory');
    }

    $log = $dir . DIRECTORY_SEPARATOR . 'output.log';
    $codeFile = $dir . DIRECTORY_SEPARATOR . 'exit.code';
    file_put_contents($log, '');
    file_put_contents(
        $dir . DIRECTORY_SEPARATOR . 'meta.json',
        json_encode(array_merge($meta, ['type' => $type, 'started' => time()]))
    );

    if (isWindows()) {
        $esc = function ($value) {
            return str_replace('%', '%%', $value);
        };
        $body = "@echo off\r\n";
        foreach (commandEnv() as $key => $value) {
            $body .= 'set "' . $key . '=' . $esc($value) . "\"\r\n";
        }
        if (selectedPhpPath()) {
            $body .= 'set "PATH=' . $esc(dirname(selectedPhpPath())) . ";%PATH%\"\r\n";
        }
        $body .= 'cd /d "' . $esc($workingDir) . "\"\r\n";
        $body .= "set RC=0\r\n";
        foreach ($steps as $step) {
            if ($step['label'] !== '') {
                $body .= 'echo === ' . preg_replace('/[^A-Za-z0-9 ._:\/-]/', '', $step['label']) . ' === >> "' . $log . "\"\r\n";
            }
            // "call" would double the ^ in version constraints, so a nested cmd is used instead.
            $body .= 'cmd /C ' . $esc($step['cmd']) . ' >> "' . $log . '" 2>&1' . "\r\n";
            $body .= "set RC=%ERRORLEVEL%\r\n";
            $body .= "if not \"%RC%\"==\"0\" goto :finish\r\n";
        }
        $body .= ":finish\r\n";
        // The space before ">" matters: "0>" would be parsed as a handle redirect.
        $body .= 'echo %RC% > "' . $codeFile . "\"\r\n";
        $script = $dir . DIRECTORY_SEPARATOR . 'run.bat';
        file_put_contents($script, $body);
        pclose(popen('start "" /B cmd /C "' . $script . '"', 'r'));
    } else {
        $body = "#!/bin/sh\n";
        $body .= 'echo $$ > ' . escapeshellarg($dir . DIRECTORY_SEPARATOR . 'run.pid') . "\n";
        foreach (commandEnv() as $key => $value) {
            $body .= 'export ' . $key . '=' . escapeshellarg($value) . "\n";
        }
        if (selectedPhpPath()) {
            $body .= 'export PATH=' . escapeshellarg(dirname(selectedPhpPath())) . ":\"\$PATH\"\n";
        }
        $body .= 'cd ' . escapeshellarg($workingDir) . ' || { echo 1 > ' . escapeshellarg($codeFile) . "; exit 1; }\n";
        foreach ($steps as $step) {
            if ($step['label'] !== '') {
                $body .= 'echo ' . escapeshellarg('=== ' . $step['label'] . ' ===') . ' >> ' . escapeshellarg($log) . "\n";
            }
            $body .= $step['cmd'] . ' >> ' . escapeshellarg($log) . " 2>&1\n";
            $body .= "RC=\$?\n";
            $body .= 'if [ $RC -ne 0 ]; then echo $RC > ' . escapeshellarg($codeFile) . "; exit \$RC; fi\n";
        }
        $body .= 'echo 0 > ' . escapeshellarg($codeFile) . "\n";
        $script = $dir . DIRECTORY_SEPARATOR . 'run.sh';
        file_put_contents($script, $body);
        // setsid gives the job its own process group so a cancel can stop Composer's children too.
        $setsid = trim((string) @shell_exec('command -v setsid 2>/dev/null')) !== '' ? 'setsid ' : '';
        exec($setsid . 'nohup sh ' . escapeshellarg($script) . ' > /dev/null 2>&1 &');
    }

    logDebug('Job started', ['id' => $id, 'type' => $type]);
    return $id;
}

function readJobChunk($log, $offset)
{
    clearstatcache(true, $log);
    $size = @filesize($log);
    if ($size === false || $offset >= $size) {
        return ['', $offset];
    }
    $handle = fopen($log, 'rb');
    fseek($handle, $offset);
    $data = (string) fread($handle, JOB_CHUNK_BYTES);
    fclose($handle);
    return [$data, $offset + strlen($data)];
}

function finishJob($dir, array $meta, $exitCode)
{
    $ok = $exitCode === 0;
    switch ($meta['type'] ?? '') {
        case 'create_project':
            $ok = $ok && is_file($meta['projectDir'] . DIRECTORY_SEPARATOR . 'artisan');
            if (!$ok) {
                // The folder did not exist before the job started, so removing it allows a clean retry.
                removeDirectory($meta['projectDir'], $meta['parentDir']);
            }
            $message = $ok ? 'Laravel project created successfully' : 'Laravel project creation failed';
            break;
        case 'run_migrations':
            $message = $ok ? 'Database migrated successfully' : 'Database migration failed';
            break;
        default:
            $labels = [
                'starter_kit' => 'Starter kit',
                'pest' => 'Pest',
                'packages' => 'Packages',
                'npm' => 'Frontend build',
                'sail' => 'Docker (Sail) setup',
                'git' => 'Git setup',
            ];
            $label = $labels[$meta['type'] ?? ''] ?? 'Command';
            $message = $ok ? $label . ' finished' : $label . ' failed';
    }

    $result = ['success' => $ok, 'message' => $message];
    file_put_contents($dir . DIRECTORY_SEPARATOR . 'result.json', json_encode($result));
    return $result;
}

function jobStatus($id, $offset)
{
    $dir = jobDir($id);
    if (!is_dir($dir)) {
        sendResponse(false, 'Job not found. It may have expired.');
    }

    $log = $dir . DIRECTORY_SEPARATOR . 'output.log';
    $codeFile = $dir . DIRECTORY_SEPARATOR . 'exit.code';
    $resultFile = $dir . DIRECTORY_SEPARATOR . 'result.json';
    $meta = json_decode((string) @file_get_contents($dir . DIRECTORY_SEPARATOR . 'meta.json'), true) ?: [];

    $result = is_file($resultFile) ? json_decode((string) file_get_contents($resultFile), true) : null;
    $rawCode = trim((string) @file_get_contents($codeFile));
    $ended = $result !== null || preg_match('/\A-?\d+\z/', $rawCode) === 1;

    // Read the exit code first so that a finished job's output is complete.
    $chunk = '';
    do {
        [$part, $offset] = readJobChunk($log, $offset);
        $chunk .= $part;
    } while ($ended && $part !== '' && strlen($chunk) < 1000000);

    if (!$ended) {
        $age = time() - (int) ($meta['started'] ?? time());
        clearstatcache(true, $log);
        if ($age > JOB_START_GRACE_SECONDS && !filesize($log)) {
            sendResponse(false, 'The background process did not start or produced no output', '', [
                'running' => false, 'chunk' => $chunk, 'offset' => $offset,
            ]);
        }
        sendResponse(true, 'Running', '', ['running' => true, 'chunk' => $chunk, 'offset' => $offset]);
    }

    if ($result === null) {
        $result = finishJob($dir, $meta, (int) $rawCode);
    }

    sendResponse(
        $result['success'],
        $result['message'],
        $result['success'] ? '' : readTail($log, RESPONSE_OUTPUT_CHARS),
        [
            'running' => false,
            'job_done' => true,
            'cancelled' => !empty($result['cancelled']),
            'chunk' => $chunk,
            'offset' => $offset,
        ]
    );
}

function killJob($dir, $id)
{
    if (isWindows()) {
        // Matches only the cmd.exe that runs this job's run.bat, then kills its whole process tree.
        $script = $dir . DIRECTORY_SEPARATOR . 'cancel.ps1';
        $body = "\$pattern = '*" . $id . "\\run.bat*'\r\n"
            . "Get-CimInstance Win32_Process | Where-Object { \$_.Name -eq 'cmd.exe' -and \$_.CommandLine -like \$pattern } | ForEach-Object { & taskkill.exe /F /T /PID \$_.ProcessId 2>&1 | Out-Null }\r\n";
        file_put_contents($script, $body);
        executeCommand('powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script), null, 60);
        return;
    }

    $pid = (int) trim((string) @file_get_contents($dir . DIRECTORY_SEPARATOR . 'run.pid'));
    if ($pid > 1) {
        @exec('kill -TERM -- -' . $pid . ' 2>/dev/null');
        @exec('kill -TERM ' . $pid . ' 2>/dev/null');
    }
}

function jobCancel($id)
{
    $dir = jobDir($id);
    if (!is_dir($dir)) {
        sendResponse(false, 'Job not found. It may have expired.');
    }

    $rawCode = trim((string) @file_get_contents($dir . DIRECTORY_SEPARATOR . 'exit.code'));
    if (is_file($dir . DIRECTORY_SEPARATOR . 'result.json') || preg_match('/\A-?\d+\z/', $rawCode) === 1) {
        sendResponse(true, 'The job had already finished', '', ['already_finished' => true]);
    }

    $meta = json_decode((string) @file_get_contents($dir . DIRECTORY_SEPARATOR . 'meta.json'), true) ?: [];
    killJob($dir, $id);

    // A cancelled create-project leaves a half-written folder; file handles can take a moment to release.
    if (($meta['type'] ?? '') === 'create_project' && !empty($meta['projectDir']) && !empty($meta['parentDir'])) {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            removeDirectory($meta['projectDir'], $meta['parentDir']);
            if (!is_dir($meta['projectDir'])) {
                break;
            }
            sleep(1);
        }
    }

    file_put_contents(
        $dir . DIRECTORY_SEPARATOR . 'result.json',
        json_encode(['success' => false, 'message' => 'Cancelled by user', 'cancelled' => true])
    );
    logDebug('Job cancelled', ['id' => $id]);
    sendResponse(true, 'Job cancelled', '', ['cancelled' => true]);
}

// ===== Saved wizard state (no passwords) =====

function stateFile()
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel-installer-state.json';
}

function saveWizardState($state)
{
    if (!is_array($state)) {
        throw new Exception('Invalid state');
    }
    if (isset($state['db']) && is_array($state['db'])) {
        unset($state['db']['password']);
    }
    $state['saved_at'] = time();

    $json = json_encode($state, JSON_UNESCAPED_SLASHES);
    if ($json === false || strlen($json) > STATE_MAX_BYTES) {
        throw new Exception('State is too large');
    }
    file_put_contents(stateFile(), $json, LOCK_EX);
}

function loadWizardState()
{
    $file = stateFile();
    if (!is_file($file)) {
        return null;
    }
    $state = json_decode((string) file_get_contents($file), true);
    if (!is_array($state) || (time() - (int) ($state['saved_at'] ?? 0)) > STATE_TTL_SECONDS) {
        return null;
    }
    return $state;
}

// ===== Input validation =====

function getInstallPath($data)
{
    if (!isset($data['installPath']) || !is_string($data['installPath'])) {
        throw new Exception('Installation path is required');
    }

    $path = trim($data['installPath']);
    if ($path === '' || preg_match('/[\x00-\x1F]/', $path)) {
        throw new Exception('Installation path is invalid');
    }
    if (preg_match('/[%!^&|<>"`]/', $path)) {
        throw new Exception('Installation path contains characters that are not allowed');
    }
    if (preg_match('#^[\\\\/]{2}#', $path)) {
        throw new Exception('Network (UNC) paths are not allowed');
    }
    if (preg_match('#(^|[\\\\/])\.\.([\\\\/]|$)#', $path)) {
        throw new Exception('Installation path cannot contain ".." segments');
    }
    if (!preg_match('#^([A-Za-z]:[\\\\/]|/)#', $path) && !preg_match('/^[A-Za-z]:$/', $path)) {
        throw new Exception('Installation path must be absolute');
    }

    $path = rtrim($path, "\\/");
    if ($path === '') {
        return DIRECTORY_SEPARATOR;
    }
    if (preg_match('/^[A-Za-z]:$/', $path)) {
        $path .= DIRECTORY_SEPARATOR;
    }

    return $path;
}

function getProjectPaths($data)
{
    if (!isset($data['projectName']) || !is_string($data['projectName']) ||
        !preg_match('/\A[a-z0-9][a-z0-9-]{1,48}[a-z0-9]\z/', $data['projectName'])) {
        throw new Exception('Project name must be 3-50 lowercase letters, numbers, or hyphens and cannot start or end with a hyphen');
    }

    $parentDir = realpath(getInstallPath($data));
    if ($parentDir === false || !is_dir($parentDir)) {
        throw new Exception('Installation directory does not exist');
    }

    return [$parentDir, rtrim($parentDir, '\\/') . DIRECTORY_SEPARATOR . $data['projectName']];
}

function getLaravelVersion($data)
{
    $requested = !empty($data['laravelVersion']) ? $data['laravelVersion'] : '13.x';
    if (!is_string($requested) || !isset(LARAVEL_CONSTRAINTS[$requested])) {
        throw new Exception('Selected Laravel version is not supported');
    }
    return $requested;
}

function getDbConfig($data)
{
    $db = (isset($data['db']) && is_array($data['db'])) ? $data['db'] : [];

    $driver = $db['connection'] ?? 'mysql';
    if (!is_string($driver) || !isset(DB_DRIVER_EXTENSIONS[$driver])) {
        throw new Exception('Unsupported database connection');
    }
    if ($driver === 'sqlite') {
        return ['connection' => 'sqlite'];
    }

    $host = $db['host'] ?? '';
    $port = (string) ($db['port'] ?? '');
    $name = $db['database'] ?? '';
    $user = $db['username'] ?? '';
    $pass = $db['password'] ?? '';

    if (!is_string($host) || !preg_match('/\A[A-Za-z0-9._:-]{1,255}\z/', $host)) {
        throw new Exception('Database host is invalid');
    }
    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new Exception('Database port must be between 1 and 65535');
    }
    if (!is_string($name) || !preg_match('/\A[A-Za-z0-9_.$-]{1,64}\z/', $name)) {
        throw new Exception('Database name is invalid');
    }
    if (!is_string($user) || $user === '' || strlen($user) > 128 || preg_match('/[\x00-\x1F\x7F]/', $user)) {
        throw new Exception('Database username is invalid');
    }
    if (!is_string($pass) || strlen($pass) > 256 || preg_match('/[\x00-\x1F\x7F]/', $pass)) {
        throw new Exception('Database password is invalid');
    }

    return [
        'connection' => $driver,
        'host' => $host,
        'port' => $port,
        'database' => $name,
        'username' => $user,
        'password' => $pass,
    ];
}

// ===== .env =====

function formatEnvValue($value)
{
    if ($value === '') {
        return '';
    }
    if (preg_match('#\A[A-Za-z0-9_./:@-]+\z#', $value)) {
        return $value;
    }
    if (strpos($value, "'") === false) {
        return "'" . $value . "'";
    }
    return '"' . str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value) . '"';
}

function setEnvValues($envFile, array $values)
{
    $content = file_get_contents($envFile);
    if ($content === false) {
        throw new Exception('Could not read .env');
    }

    foreach ($values as $key => $value) {
        $line = $key . '=' . formatEnvValue($value);
        $pattern = '/^[ \t]*#?[ \t]*' . preg_quote($key, '/') . '=[^\r\n]*/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace_callback($pattern, function () use ($line) {
                return $line;
            }, $content, 1);
        } else {
            $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
        }
    }

    if (file_put_contents($envFile, $content) === false) {
        throw new Exception('Could not write .env');
    }
}

// ===== Folder browser =====

// Returns sub-folder names only (never files), so the UI can offer a server-side folder picker.
function listDirectories($path)
{
    $limit = 500;

    if ($path === '' && isWindows()) {
        $drives = [];
        foreach (range('A', 'Z') as $letter) {
            if (@is_dir($letter . ':\\')) {
                $drives[] = ['name' => $letter . ':\\', 'path' => $letter . ':\\'];
            }
        }
        return ['path' => '', 'parent' => null, 'writable' => false, 'dirs' => $drives];
    }

    $real = realpath(getInstallPath(['installPath' => $path === '' ? '/' : $path]));
    if ($real === false || !is_dir($real)) {
        throw new Exception('That folder does not exist');
    }

    $dirs = [];
    foreach (@scandir($real) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..' || $entry[0] === '.' || $entry[0] === '$') {
            continue;
        }
        $full = rtrim($real, '\\/') . DIRECTORY_SEPARATOR . $entry;
        if (@is_dir($full)) {
            $dirs[] = ['name' => $entry, 'path' => $full];
        }
        if (count($dirs) >= $limit) {
            break;
        }
    }
    usort($dirs, function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    $parent = dirname($real);
    if ($parent === $real) {
        // At a root: Windows goes back to the drive list, Unix has nothing above.
        $parent = isWindows() ? '' : null;
    }

    return ['path' => $real, 'parent' => $parent, 'writable' => is_writable($real), 'dirs' => $dirs];
}

// ===== Installed PHP versions =====

function normalizePath($path)
{
    return isWindows() ? strtolower($path) : $path;
}

// Paths only, nothing is executed here, so this is cheap enough to use as an allowlist.
function phpCandidatePaths()
{
    $found = [];
    $add = function ($path) use (&$found) {
        $real = @realpath($path);
        if (!$real || !is_file($real) || (isWindows() && !preg_match('/\.exe\z/i', $real))) {
            return;
        }
        $found[normalizePath($real)] = $real;
    };

    $lines = [];
    if (isWindows()) {
        $roots = ['C:/wamp64', 'C:/wamp'];
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        if ($docRoot) {
            $roots[] = str_replace('\\', '/', dirname($docRoot));
        }
        foreach (array_unique($roots) as $root) {
            foreach (glob($root . '/bin/php/php*/php.exe') ?: [] as $path) {
                $add($path);
            }
        }
        foreach (glob('C:/laragon/bin/php/php-*/php.exe') ?: [] as $path) {
            $add($path);
        }
        $add('C:/xampp/php/php.exe');
        $add(PHP_BINDIR . '/php.exe');
        @exec('where php 2>NUL', $lines);
    } else {
        foreach (['/usr/bin/php[0-9]*', '/usr/local/bin/php[0-9]*', '/opt/homebrew/bin/php*'] as $pattern) {
            foreach (glob($pattern) ?: [] as $path) {
                if (preg_match('#/php\d*(\.\d+)*\z#', $path)) {
                    $add($path);
                }
            }
        }
        @exec('which -a php 2>/dev/null', $lines);
    }

    $default = null;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $add($line);
        if ($default === null && @realpath($line)) {
            $default = realpath($line);
        }
    }

    return [array_values($found), $default];
}

function phpSourceLabel($path)
{
    foreach (['wamp' => 'WAMP', 'laragon' => 'Laragon', 'xampp' => 'XAMPP', 'homebrew' => 'Homebrew'] as $needle => $label) {
        if (stripos($path, $needle) !== false) {
            return $label;
        }
    }
    return 'System';
}

function listPhpVersions()
{
    [$paths, $default] = phpCandidatePaths();
    $items = [];
    foreach ($paths as $path) {
        // Running every php.exe is slow (xdebug etc.), so read the version from the folder name when possible.
        $version = preg_match('#php-?(\d+\.\d+\.\d+)#i', basename(dirname($path)), $m) ? $m[1] : null;
        if ($version === null) {
            $result = executeCommand(escapeshellarg($path) . ' -v', null, 10);
            if ($result['return_code'] !== 0 || !preg_match('/PHP (\d+\.\d+\.\d+)/', $result['output'], $m)) {
                continue;
            }
            $version = $m[1];
        }
        $items[] = [
            'path' => $path,
            'version' => $version,
            'source' => phpSourceLabel($path),
            'is_default' => $default !== null && normalizePath($path) === normalizePath($default),
        ];
    }
    usort($items, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });
    return $items;
}

// The UI may only pick from detected installations, never an arbitrary executable.
function applyPhpChoice($data)
{
    $choice = $data['phpBinary'] ?? '';
    if ($choice === '' || $choice === null) {
        selectedPhpPath('');
        return;
    }
    if (!is_string($choice)) {
        throw new Exception('Invalid PHP selection');
    }

    $real = @realpath($choice);
    [$paths] = phpCandidatePaths();
    foreach ($paths as $path) {
        if ($real && normalizePath($path) === normalizePath($real)) {
            selectedPhpPath($path);
            return;
        }
    }
    throw new Exception('The selected PHP is not one of the detected installations');
}

// ===== Application settings =====

function getAppConfig($data)
{
    if (!isset($data['app']) || !is_array($data['app'])) {
        return null;
    }
    $app = $data['app'];

    $name = $app['name'] ?? '';
    if (!is_string($name) || $name === '' || strlen($name) > 100 || preg_match('/[\x00-\x1F\x7F]/', $name)) {
        throw new Exception('Application name must be 1-100 characters');
    }

    $url = $app['url'] ?? '';
    if (!is_string($url) || strlen($url) > 200
        || !preg_match('#\Ahttps?://[A-Za-z0-9.-]+(:\d{1,5})?(/[A-Za-z0-9._~/-]*)?\z#', $url)) {
        throw new Exception('Application URL must look like http://localhost:8000');
    }

    $env = $app['env'] ?? 'local';
    if (!is_string($env) || !in_array($env, ['local', 'development', 'staging', 'production', 'testing'], true)) {
        throw new Exception('Unsupported application environment');
    }

    $timezone = $app['timezone'] ?? 'UTC';
    if (!is_string($timezone) || !in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
        throw new Exception('Unknown timezone');
    }

    $locale = $app['locale'] ?? 'en';
    if (!is_string($locale) || !preg_match('/\A[a-z]{2,3}(_[A-Z]{2})?\z/', $locale)) {
        throw new Exception('Locale is invalid');
    }

    return [
        'name' => $name,
        'url' => $url,
        'env' => $env,
        'debug' => filter_var($app['debug'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'timezone' => $timezone,
        'locale' => $locale,
    ];
}

// Laravel's config/app.php hardcodes timezone (and locale before v11), so let it read the .env values.
function patchAppConfig($projectDir)
{
    $file = $projectDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
    $content = @file_get_contents($file);
    if ($content === false) {
        return;
    }

    $patched = preg_replace_callback(
        '/([\'"]timezone[\'"]\s*=>\s*)[\'"]UTC[\'"]/',
        function ($m) {
            return $m[1] . "env('APP_TIMEZONE', 'UTC')";
        },
        $content,
        1
    );
    $patched = preg_replace_callback(
        '/([\'"]locale[\'"]\s*=>\s*)[\'"]en[\'"]/',
        function ($m) {
            return $m[1] . "env('APP_LOCALE', 'en')";
        },
        $patched,
        1
    );

    if ($patched !== null && $patched !== $content) {
        file_put_contents($file, $patched);
    }
}

// ===== Database tool (runs on the PHP CLI) =====

function runDbTool($action, array $db)
{
    $script = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel-installer-dbtool.php';
    file_put_contents($script, DB_TOOL_SCRIPT);

    $result = executeCommand(
        phpBinary() . ' -d display_errors=0 ' . escapeshellarg($script),
        null,
        30,
        [
            'INST_ACTION' => $action,
            'INST_DRIVER' => $db['connection'],
            'INST_HOST' => $db['host'],
            'INST_PORT' => $db['port'],
            'INST_DB' => $db['database'],
            'INST_USER' => $db['username'],
            'INST_PASS' => $db['password'],
        ]
    );

    $json = null;
    if (preg_match_all('/^\{.*\}\s*$/m', $result['output'], $matches) && $matches[0]) {
        $json = json_decode(trim(end($matches[0])), true);
    }
    if (!is_array($json)) {
        return [
            'ok' => false,
            'message' => 'The PHP CLI could not run the database check',
            'db_missing' => false,
            'tables' => null,
            'output' => $result['output'],
        ];
    }

    return [
        'ok' => (bool) $json['ok'],
        'message' => (string) $json['message'],
        'db_missing' => !empty($json['db_missing']),
        'tables' => isset($json['tables']) ? (int) $json['tables'] : null,
        'output' => '',
    ];
}

// ===== Environment checks =====

function getCliPhpVersion()
{
    $result = executeCommand(phpBinary() . ' -v', null, 15);
    return preg_match('/PHP (\d+\.\d+\.\d+)/', $result['output'], $m) ? $m[1] : null;
}

function getComposerInfo()
{
    $result = executeCommand('composer --version', null, 30);
    if ($result['return_code'] !== 0) {
        return [null, $result['output']];
    }
    $lines = preg_split('/\R/', trim($result['output']));
    foreach ($lines as $line) {
        if (stripos($line, 'Composer version') === 0) {
            return [$line, $result['output']];
        }
    }
    return [$lines[0] ?? 'installed', $result['output']];
}

function getCliModules()
{
    $result = executeCommand(phpBinary() . ' -m', null, 15);
    $modules = [];
    foreach (preg_split('/\R/', $result['output']) as $line) {
        $line = strtolower(trim($line));
        if ($line !== '' && $line[0] !== '[') {
            $modules[$line] = true;
        }
    }
    return array_keys($modules);
}

function addCheck(array &$checks, $id, $label, $status, $detail = '')
{
    $checks[] = ['id' => $id, 'label' => $label, 'status' => $status, 'detail' => $detail];
}

function toolVersionCheck(array &$checks, $id, $label, $command, $missingDetail)
{
    $result = executeCommand($command, null, 15);
    if ($result['return_code'] === 0) {
        $line = trim(strtok(trim($result['output']), "\n"));
        addCheck($checks, $id, $label, 'ok', $line);
    } else {
        addCheck($checks, $id, $label, 'warn', $missingDetail);
    }
}

function collectEnvironment($data)
{
    $checks = [];
    $cliVersion = getCliPhpVersion();
    [$composerVersion] = getComposerInfo();

    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $extra = [
        'php_web' => PHP_VERSION,
        'php_cli' => $cliVersion,
        'composer' => $composerVersion,
        'default_path' => $docRoot ?: '',
        'php_path' => selectedPhpPath(),
    ];

    // PHP CLI version
    if ($cliVersion === null) {
        addCheck($checks, 'php', 'PHP CLI', 'fail', 'PHP was not found in PATH');
    } else {
        $detail = 'PHP ' . $cliVersion . ' (web server runs ' . PHP_VERSION . ')';
        $status = 'ok';
        if (!empty($data['laravelVersion'])) {
            $version = getLaravelVersion($data);
            $min = LARAVEL_MIN_PHP[$version];
            if (version_compare($cliVersion, $min, '<')) {
                $status = 'fail';
                $detail = "Laravel {$version} requires PHP {$min}+, but the CLI has {$cliVersion}";
            }
        }
        addCheck($checks, 'php', 'PHP CLI', $status, $detail);
    }

    // Composer
    addCheck($checks, 'composer', 'Composer', $composerVersion ? 'ok' : 'fail', $composerVersion ?: 'Composer was not found in PATH');

    // PHP extensions on the CLI
    $modules = [];
    if ($cliVersion !== null) {
        $modules = getCliModules();
        $extra['cli_modules'] = $modules;

        if (!$modules) {
            addCheck($checks, 'extensions', 'PHP extensions (CLI)', 'warn', 'Could not read the CLI module list');
        } else {
            $missing = array_values(array_diff(REQUIRED_EXTENSIONS, $modules));
            addCheck(
                $checks,
                'extensions',
                'PHP extensions (CLI)',
                $missing ? 'fail' : 'ok',
                $missing ? 'Missing: ' . implode(', ', $missing) : 'All required extensions are enabled'
            );

            $driver = (isset($data['db']['connection']) && is_string($data['db']['connection'])) ? $data['db']['connection'] : 'mysql';
            if (isset(DB_DRIVER_EXTENSIONS[$driver])) {
                $driverExt = DB_DRIVER_EXTENSIONS[$driver];
                $has = in_array($driverExt, $modules, true);
                addCheck($checks, 'db_ext', 'Database driver (' . $driverExt . ')', $has ? 'ok' : 'fail', $has ? 'Enabled in the PHP CLI' : 'Not enabled in the PHP CLI');
            }

            $hasZip = in_array('zip', $modules, true);
            addCheck($checks, 'zip', 'Zip extension', $hasZip ? 'ok' : 'warn', $hasZip ? 'Enabled' : 'Missing. Composer will fall back to slower downloads');

            foreach (selectedPackageIds(getOpts($data)) as $packageId) {
                $def = PACKAGE_DEFS[$packageId];
                $missingExt = array_values(array_diff($def['ext'] ?? [], $modules));
                if (!empty($def['unix']) && isWindows()) {
                    addCheck($checks, 'pkg_' . $packageId, $def['label'], 'fail', 'Not supported on Windows (needs pcntl)');
                } elseif ($missingExt) {
                    addCheck($checks, 'pkg_' . $packageId, $def['label'], 'fail', 'Needs the PHP extension: ' . implode(', ', $missingExt));
                }
            }
        }
    }

    toolVersionCheck($checks, 'git', 'Git', 'git --version', 'Not found. Needed only for git-based packages and git init');
    toolVersionCheck($checks, 'node', 'Node.js', 'node --version', 'Not found. Needed only for building front-end assets');
    toolVersionCheck($checks, 'npm', 'npm', 'npm --version', 'Not found. Needed only for building front-end assets');

    // Disk space
    $diskDir = $docRoot ?: sys_get_temp_dir();
    try {
        $path = realpath(getInstallPath($data));
        if ($path !== false && is_dir($path)) {
            $diskDir = $path;
        }
    } catch (Exception $e) {
        // Fall back to the document root.
    }
    $free = @disk_free_space($diskDir);
    if ($free === false) {
        addCheck($checks, 'disk', 'Free disk space', 'warn', 'Could not be determined');
    } else {
        $gb = round($free / 1073741824, 1);
        $status = $free < MIN_DISK_FAIL_BYTES ? 'fail' : ($free < MIN_DISK_WARN_BYTES ? 'warn' : 'ok');
        addCheck($checks, 'disk', 'Free disk space', $status, $gb . ' GB available');
    }

    // Internet access (Composer needs Packagist)
    $socket = @fsockopen('repo.packagist.org', 443, $errno, $errstr, 4);
    if ($socket) {
        fclose($socket);
        addCheck($checks, 'internet', 'Internet (Packagist)', 'ok', 'repo.packagist.org is reachable');
    } else {
        addCheck($checks, 'internet', 'Internet (Packagist)', 'warn', 'repo.packagist.org is not reachable. Composer needs it unless packages are cached');
    }

    return [$checks, $extra];
}

// ===== Starter kits, packages and tooling =====

const STARTER_KITS = [
    'react' => ['package' => 'laravel/react-starter-kit', 'version' => null],
    'vue' => ['package' => 'laravel/vue-starter-kit', 'version' => null],
    'livewire' => ['package' => 'laravel/livewire-starter-kit', 'version' => null],
    // The Svelte kit has no tagged release on Packagist yet.
    'svelte' => ['package' => 'laravel/svelte-starter-kit', 'version' => 'dev-main'],
];

const BREEZE_STACKS = ['blade', 'livewire', 'livewire-functional', 'react', 'vue', 'api'];
const JETSTREAM_STACKS = ['livewire', 'inertia'];
const SAIL_SERVICES = ['mysql', 'pgsql', 'mariadb', 'redis', 'memcached', 'meilisearch', 'mailpit', 'selenium'];
const GIT_IGNORE_GROUPS = [
    'ide' => ['.idea/', '.vscode/'],
    'os' => ['.DS_Store', 'Thumbs.db'],
];

const RUNTIME_CHOICES = [
    'cache' => ['default', 'file', 'database', 'redis', 'array'],
    'queue' => ['default', 'sync', 'database', 'redis'],
    'session' => ['default', 'file', 'database', 'redis'],
    'mail' => ['default', 'log', 'mailpit'],
];

// 'min' is the lowest Laravel major the install steps support; 'post' lists artisan commands run afterwards.
const PACKAGE_DEFS = [
    'sanctum' => ['label' => 'Laravel Sanctum', 'require' => [], 'dev' => false, 'min' => 11,
        'post' => [['install:api', '--without-migration-prompt']]],
    'socialite' => ['label' => 'Laravel Socialite', 'require' => ['laravel/socialite'], 'dev' => false, 'post' => []],
    'fortify' => ['label' => 'Laravel Fortify', 'require' => ['laravel/fortify'], 'dev' => false, 'min' => 11,
        'post' => [['fortify:install']]],
    'livewire' => ['label' => 'Livewire', 'require' => ['livewire/livewire'], 'dev' => false, 'post' => []],
    'filament' => ['label' => 'Filament', 'require' => ['filament/filament'], 'dev' => false, 'min' => 11, 'ext' => ['intl'],
        'post' => [['filament:install', '--panels']]],
    'permission' => ['label' => 'Spatie Permission', 'require' => ['spatie/laravel-permission'], 'dev' => false,
        'post' => [['vendor:publish', '--provider=Spatie\\Permission\\PermissionServiceProvider']]],
    'debugbar' => ['label' => 'Debugbar', 'require' => ['barryvdh/laravel-debugbar'], 'dev' => true, 'post' => []],
    'ide-helper' => ['label' => 'IDE Helper', 'require' => ['barryvdh/laravel-ide-helper'], 'dev' => true, 'post' => []],
    'telescope' => ['label' => 'Laravel Telescope', 'require' => ['laravel/telescope'], 'dev' => false,
        'post' => [['telescope:install']]],
    'horizon' => ['label' => 'Laravel Horizon', 'require' => ['laravel/horizon'], 'dev' => false, 'unix' => true,
        'ext' => ['pcntl', 'posix'], 'post' => [['horizon:install']]],
];

function getOpts($data)
{
    return (isset($data['opts']) && is_array($data['opts'])) ? $data['opts'] : [];
}

function getKitOption($opts)
{
    $kit = $opts['kit'] ?? 'none';
    if (!is_string($kit) || !in_array($kit, array_merge(['none', 'breeze', 'jetstream'], array_keys(STARTER_KITS)), true)) {
        throw new Exception('Unsupported starter kit');
    }

    $stack = '';
    if ($kit === 'breeze' || $kit === 'jetstream') {
        $stack = $opts['kitStack'] ?? '';
        $allowed = $kit === 'breeze' ? BREEZE_STACKS : JETSTREAM_STACKS;
        if (!is_string($stack) || !in_array($stack, $allowed, true)) {
            throw new Exception('Unsupported ' . $kit . ' stack');
        }
    }

    return ['kit' => $kit, 'stack' => $stack, 'teams' => $kit === 'jetstream' && !empty($opts['kitTeams'])];
}

function wantsPest($opts)
{
    return ($opts['testing'] ?? 'phpunit') === 'pest';
}

function selectedPackageIds($opts)
{
    $ids = [];
    foreach (is_array($opts['packages'] ?? null) ? $opts['packages'] : [] as $id) {
        if (is_string($id) && isset(PACKAGE_DEFS[$id]) && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }
    return $ids;
}

function getRuntimeChoices($opts)
{
    $runtime = is_array($opts['runtime'] ?? null) ? $opts['runtime'] : [];
    $choices = [];
    foreach (RUNTIME_CHOICES as $key => $allowed) {
        $value = $runtime[$key] ?? 'default';
        $choices[$key] = (is_string($value) && in_array($value, $allowed, true)) ? $value : 'default';
    }
    return $choices;
}

function usesRedis($opts)
{
    $choices = getRuntimeChoices($opts);
    return in_array('redis', [$choices['cache'], $choices['queue'], $choices['session']], true);
}

function hasPhpRedis()
{
    return in_array('redis', getCliModules(), true);
}

function projectLaravelMajor($projectDir)
{
    $composer = json_decode((string) @file_get_contents($projectDir . DIRECTORY_SEPARATOR . 'composer.json'), true);
    $constraint = $composer['require']['laravel/framework'] ?? '';
    return preg_match('/(\d+)/', (string) $constraint, $m) ? (int) $m[1] : 12;
}

function installedLaravelVersion($projectDir)
{
    $lock = json_decode((string) @file_get_contents($projectDir . DIRECTORY_SEPARATOR . 'composer.lock'), true);
    foreach ($lock['packages'] ?? [] as $package) {
        if (($package['name'] ?? '') === 'laravel/framework') {
            return ltrim((string) $package['version'], 'v');
        }
    }
    return null;
}

// .env values for the cache, queue, session and mail choices.
function runtimeEnvValues($opts, $projectDir)
{
    $choices = getRuntimeChoices($opts);
    $values = [];

    if ($choices['cache'] !== 'default') {
        $values[projectLaravelMajor($projectDir) >= 11 ? 'CACHE_STORE' : 'CACHE_DRIVER'] = $choices['cache'];
    }
    if ($choices['queue'] !== 'default') {
        $values['QUEUE_CONNECTION'] = $choices['queue'];
    }
    if ($choices['session'] !== 'default') {
        $values['SESSION_DRIVER'] = $choices['session'];
    }
    if ($choices['mail'] === 'log') {
        $values['MAIL_MAILER'] = 'log';
    } elseif ($choices['mail'] === 'mailpit') {
        $values['MAIL_MAILER'] = 'smtp';
        $values['MAIL_HOST'] = '127.0.0.1';
        $values['MAIL_PORT'] = '1025';
    }
    if (usesRedis($opts)) {
        $values['REDIS_CLIENT'] = hasPhpRedis() ? 'phpredis' : 'predis';
    }

    return $values;
}

function artisanLine(array $tokens)
{
    $parts = array_map(function ($token) {
        return preg_match('/\A[A-Za-z0-9:_.=,\/-]+\z/', $token) ? $token : escapeshellarg($token);
    }, $tokens);
    return phpBinary() . ' artisan ' . implode(' ', $parts) . ' --no-interaction --no-ansi';
}

function composerLine($args)
{
    return 'composer ' . $args . ' --no-interaction --no-progress --no-ansi';
}

function requireProject($data)
{
    [, $projectDir] = getProjectPaths($data);
    if (!is_file($projectDir . DIRECTORY_SEPARATOR . 'artisan')) {
        throw new Exception('The Laravel project was not found');
    }
    return $projectDir;
}

function findFreePort()
{
    for ($port = 8000; $port < 8100; $port++) {
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.3);
        if ($socket) {
            fclose($socket);
            continue;
        }
        return $port;
    }
    throw new Exception('No free port between 8000 and 8099');
}

function scaffoldBootstrap($projectDir)
{
    $sep = DIRECTORY_SEPARATOR;
    $pkgFile = $projectDir . $sep . 'package.json';
    if (!is_file($pkgFile)) {
        throw new Exception('package.json was not found. Bootstrap needs Laravel 10 or newer');
    }

    $pkg = json_decode((string) file_get_contents($pkgFile), true);
    if (!is_array($pkg)) {
        throw new Exception('package.json could not be read');
    }
    $pkg['devDependencies'] = array_merge($pkg['devDependencies'] ?? [], [
        'bootstrap' => '^5.3.0',
        '@popperjs/core' => '^2.11.0',
        'sass' => '^1.77.0',
    ]);
    file_put_contents($pkgFile, json_encode($pkg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    $sassDir = $projectDir . $sep . 'resources' . $sep . 'sass';
    if (!is_dir($sassDir)) {
        mkdir($sassDir, 0775, true);
    }
    file_put_contents($sassDir . $sep . 'app.scss', "@import 'bootstrap/scss/bootstrap';\n");

    $appJs = $projectDir . $sep . 'resources' . $sep . 'js' . $sep . 'app.js';
    $js = (string) @file_get_contents($appJs);
    if (strpos($js, "import 'bootstrap'") === false) {
        file_put_contents($appJs, rtrim($js) . "\nimport 'bootstrap';\n");
    }

    $viteFiles = [$projectDir . $sep . 'vite.config.js', $projectDir . $sep . 'vite.config.ts'];
    $views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $projectDir . $sep . 'resources' . $sep . 'views',
        FilesystemIterator::SKIP_DOTS
    ));
    foreach ($views as $file) {
        if (substr($file->getFilename(), -10) === '.blade.php') {
            $viteFiles[] = $file->getPathname();
        }
    }
    foreach ($viteFiles as $file) {
        $content = @file_get_contents($file);
        if ($content !== false && strpos($content, 'resources/css/app.css') !== false) {
            file_put_contents($file, str_replace('resources/css/app.css', 'resources/sass/app.scss', $content));
        }
    }

    // The stock welcome page is styled with Tailwind classes, so replace it with a Bootstrap one.
    $welcome = <<<'BLADE'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">
    <main class="container py-5">
        <div class="p-5 bg-body rounded-4 shadow-sm text-center">
            <h1 class="display-5 fw-bold">{{ config('app.name', 'Laravel') }}</h1>
            <p class="lead text-secondary">Laravel {{ app()->version() }} &middot; PHP {{ PHP_VERSION }} &middot; Bootstrap 5</p>
            <a class="btn btn-danger btn-lg" href="https://laravel.com/docs">Documentation</a>
        </div>
    </main>
</body>
</html>
BLADE;
    $welcomeFile = $projectDir . $sep . 'resources' . $sep . 'views' . $sep . 'welcome.blade.php';
    if (is_file($welcomeFile)) {
        file_put_contents($welcomeFile, $welcome . "\n");
    }
}

function gitSetupSteps($projectDir, $opts)
{
    $git = is_array($opts['git'] ?? null) ? $opts['git'] : [];
    $branch = $git['branch'] ?? 'main';
    if (!is_string($branch) || !preg_match('#\A[A-Za-z0-9._/-]{1,50}\z#', $branch) || strpos($branch, '..') !== false) {
        throw new Exception('Git branch name is invalid');
    }

    $lines = [];
    foreach (is_array($git['ignore'] ?? null) ? $git['ignore'] : [] as $group) {
        if (is_string($group) && isset(GIT_IGNORE_GROUPS[$group])) {
            $lines = array_merge($lines, GIT_IGNORE_GROUPS[$group]);
        }
    }
    $ignoreFile = $projectDir . DIRECTORY_SEPARATOR . '.gitignore';
    if ($lines && is_file($ignoreFile)) {
        $existing = preg_split('/\R/', (string) file_get_contents($ignoreFile));
        $missing = array_values(array_diff($lines, array_map('trim', $existing)));
        if ($missing) {
            file_put_contents($ignoreFile, rtrim((string) file_get_contents($ignoreFile)) . "\n\n# Added by Laravel Installer\n" . implode("\n", $missing) . "\n");
        }
    }

    $steps = [
        ['label' => 'Initialise repository', 'cmd' => 'git init'],
        ['label' => 'Set branch ' . $branch, 'cmd' => 'git symbolic-ref HEAD ' . escapeshellarg('refs/heads/' . $branch)],
    ];

    if (!empty($git['commit'])) {
        $name = trim(executeCommand('git config user.name', null, 10)['output']);
        $email = trim(executeCommand('git config user.email', null, 10)['output']);
        $identity = ($name !== '' && $email !== '')
            ? ''
            : ' -c ' . escapeshellarg('user.name=Laravel Installer') . ' -c ' . escapeshellarg('user.email=installer@localhost');
        $steps[] = ['label' => 'Stage files', 'cmd' => 'git add -A'];
        $steps[] = ['label' => 'Create first commit', 'cmd' => 'git' . $identity . ' commit -q -m ' . escapeshellarg('Initial commit')];
    }

    return $steps;
}

// ===== Saved templates and install history =====

function dataDir()
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'installer-data';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
        file_put_contents(
            $dir . DIRECTORY_SEPARATOR . '.htaccess',
            "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n"
        );
    }
    return $dir;
}

function readList($name)
{
    $list = json_decode((string) @file_get_contents(dataDir() . DIRECTORY_SEPARATOR . $name), true);
    return is_array($list) ? $list : [];
}

function writeList($name, array $list)
{
    file_put_contents(dataDir() . DIRECTORY_SEPARATOR . $name, json_encode(array_values($list), JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function sanitizeTemplateConfig($config)
{
    if (!is_array($config)) {
        throw new Exception('Template settings are invalid');
    }
    $clean = [];
    foreach (['laravelVersion', 'app', 'runMigrations', 'opts'] as $key) {
        if (isset($config[$key])) {
            $clean[$key] = $config[$key];
        }
    }
    if (isset($config['db']) && is_array($config['db'])) {
        $clean['db'] = array_intersect_key($config['db'], array_flip(['connection', 'host', 'port', 'database', 'username']));
    }
    if (strlen((string) json_encode($clean)) > 8000) {
        throw new Exception('Template is too large');
    }
    return $clean;
}

function saveTemplate($name, $config)
{
    if (!is_string($name) || !preg_match('/\A[\p{L}\p{N} ._()+-]{1,60}\z/u', trim($name))) {
        throw new Exception('Template name must be 1-60 letters, numbers or spaces');
    }
    $name = trim($name);
    $list = array_filter(readList('templates.json'), function ($t) use ($name) {
        return ($t['name'] ?? '') !== $name;
    });
    $list[] = ['name' => $name, 'config' => sanitizeTemplateConfig($config), 'saved_at' => date('c')];
    if (count($list) > 50) {
        throw new Exception('Too many templates. Delete one first');
    }
    writeList('templates.json', $list);
}

function addHistory($data, $projectDir, $laravelVersion)
{
    $opts = getOpts($data);
    $db = (isset($data['db']['connection']) && is_string($data['db']['connection'])) ? $data['db']['connection'] : '';
    $php = (isset($data['phpVersion']) && is_string($data['phpVersion']) && preg_match('/\A\d+\.\d+(\.\d+)?\z/', $data['phpVersion'])) ? $data['phpVersion'] : null;
    $kit = getKitOption($opts)['kit'];

    $list = readList('history.json');
    array_unshift($list, [
        'id' => bin2hex(random_bytes(6)),
        'name' => basename($projectDir),
        'path' => $projectDir,
        'laravel' => $laravelVersion,
        'php' => $php,
        'db' => $db,
        'kit' => $kit,
        'packages' => selectedPackageIds($opts),
        'date' => date('c'),
    ]);
    writeList('history.json', array_slice($list, 0, 100));
}

// ===== Health report =====

function extractJson($text)
{
    if (preg_match('/^\s*([\[{].*[\]}])\s*$/m', $text, $m)) {
        $decoded = json_decode($m[1], true);
        if ($decoded !== null) {
            return $decoded;
        }
    }
    $start = min(array_filter([strpos($text, '{'), strpos($text, '[')], function ($v) {
        return $v !== false;
    }) ?: [false]);
    $end = max(strrpos($text, '}'), strrpos($text, ']'));
    if ($start === false || $end === false || $end < $start) {
        return null;
    }
    return json_decode(substr($text, $start, $end - $start + 1), true);
}

// ===== Security gates =====

function isLocalHostHeader($host)
{
    return (bool) preg_match('/\A(localhost|127\.0\.0\.1|\[::1\])(:\d+)?\z/i', $host);
}

function enforceLocalRequest()
{
    if (is_file(LOCK_FILE)) {
        sendResponse(false, 'Installer is locked. Delete installer.lock to unlock it.', '', ['locked' => true], 403);
    }

    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
        sendResponse(false, 'Installer requests are only allowed from localhost', '', [], 403);
    }

    // Blocks DNS rebinding: the browser must have reached us as localhost.
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!isLocalHostHeader($host)) {
        sendResponse(false, 'Open the installer through http://localhost/', '', [], 403);
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '' && preg_replace('#\Ahttps?://#i', '', rtrim($origin, '/')) !== $host) {
        sendResponse(false, 'Cross-origin installer requests are not allowed', '', [], 403);
    }
}

function startInstallerSession()
{
    $cookiePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    session_name('laravel_installer');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath === '' ? '/' : $cookiePath,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ===== Request handling =====

try {
    enforceLocalRequest();
    startInstallerSession();

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET' && ($_GET['action'] ?? '') === 'token') {
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['token'];
        session_write_close();
        sendResponse(true, 'Token issued', '', ['token' => $token]);
    }

    if ($method !== 'POST') {
        sendResponse(false, 'Method not allowed', '', [], 405);
    }

    $sentToken = $_SERVER['HTTP_X_INSTALLER_TOKEN'] ?? '';
    $sessionToken = $_SESSION['token'] ?? '';
    session_write_close();
    if ($sessionToken === '' || !hash_equals($sessionToken, $sentToken)) {
        sendResponse(false, 'Invalid or missing installer token. Reload the page.', '', [], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data) || !isset($data['step']) || !is_string($data['step'])) {
        sendResponse(false, 'Invalid request data');
    }

    $usesPhp = ['check_requirements', 'check_environment', 'test_database', 'create_database', 'create_project', 'generate_key', 'run_migrations'];
    if (in_array($data['step'], $usesPhp, true)) {
        applyPhpChoice($data);
    }

    switch ($data['step']) {
        case 'check_requirements':
            $cliVersion = getCliPhpVersion();
            [$composerVersion, $composerOutput] = getComposerInfo();

            $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
            $extra = [
                'php_web' => PHP_VERSION,
                'php_cli' => $cliVersion,
                'composer' => $composerVersion,
                'default_path' => $docRoot ?: '',
            ];
            $summary = 'PHP CLI: ' . ($cliVersion ?? 'not found') . PHP_EOL
                . 'Composer: ' . ($composerVersion ?? 'not found');

            if ($cliVersion === null) {
                sendResponse(false, 'PHP CLI was not found in PATH', $summary, $extra);
            }
            if ($composerVersion === null) {
                sendResponse(false, 'Composer was not found in PATH', $summary . PHP_EOL . $composerOutput, $extra);
            }
            if (!empty($data['laravelVersion'])) {
                $version = getLaravelVersion($data);
                $min = LARAVEL_MIN_PHP[$version];
                if (version_compare($cliVersion, $min, '<')) {
                    sendResponse(false, "Laravel {$version} requires PHP {$min}+, but the CLI has {$cliVersion}", $summary, $extra);
                }
            }
            sendResponse(true, 'System requirements checked', $summary, $extra);
            break;

        case 'check_environment':
            [$checks, $extra] = collectEnvironment($data);
            $extra['checks'] = $checks;

            $lines = [];
            $failed = [];
            foreach ($checks as $check) {
                $lines[] = '[' . strtoupper($check['status']) . '] ' . $check['label'] . ': ' . $check['detail'];
                if ($check['status'] === 'fail') {
                    $failed[] = $check['label'];
                }
            }
            $summary = implode(PHP_EOL, $lines);

            if ($failed) {
                sendResponse(false, 'System check failed: ' . implode(', ', $failed), $summary, $extra);
            }
            sendResponse(true, 'System check passed', $summary, $extra);
            break;

        case 'list_dirs':
            $browse = isset($data['path']) && is_string($data['path']) ? trim($data['path']) : '';
            sendResponse(true, 'Folders listed', '', listDirectories($browse));
            break;

        case 'list_php':
            sendResponse(true, 'PHP installations listed', '', ['versions' => listPhpVersions()]);
            break;

        case 'check_name':
            try {
                [, $projectDir] = getProjectPaths($data);
            } catch (Exception $e) {
                sendResponse(true, $e->getMessage(), '', ['status' => 'invalid']);
            }
            if (file_exists($projectDir)) {
                sendResponse(true, 'Already exists', '', ['status' => 'exists', 'kind' => is_dir($projectDir) ? 'folder' : 'file']);
            }
            sendResponse(true, 'Available', '', ['status' => is_writable(dirname($projectDir)) ? 'free' : 'not_writable']);
            break;

        case 'check_directory':
            $dir = realpath(getInstallPath($data));
            if ($dir === false || !is_dir($dir)) {
                sendResponse(false, 'Directory does not exist');
            }
            if (!is_writable($dir)) {
                sendResponse(false, 'Directory is not writable: ' . $dir);
            }
            sendResponse(true, 'Directory validated', $dir);
            break;

        case 'test_database':
            $db = getDbConfig($data);
            if ($db['connection'] === 'sqlite') {
                sendResponse(true, 'SQLite needs no connection test');
            }
            $result = runDbTool('test', $db);
            sendResponse($result['ok'], $result['message'], $result['output'], [
                'db_missing' => $result['db_missing'],
                'tables' => $result['tables'],
            ]);
            break;

        case 'create_database':
            $db = getDbConfig($data);
            if ($db['connection'] === 'sqlite') {
                sendResponse(true, 'SQLite creates its own file');
            }
            $result = runDbTool('create', $db);
            sendResponse($result['ok'], $result['message'], $result['output']);
            break;

        case 'create_project':
            [$parentDir, $projectDir] = getProjectPaths($data);

            if (!is_writable($parentDir)) {
                sendResponse(false, 'Installation directory is not writable');
            }
            if (file_exists($projectDir)) {
                sendResponse(false, 'A file or directory already exists with that project name');
            }

            $kitChoice = getKitOption(getOpts($data));
            if (isset(STARTER_KITS[$kitChoice['kit']])) {
                $kit = STARTER_KITS[$kitChoice['kit']];
                $createCommand = 'composer create-project ' . $kit['package'] . ' ' . escapeshellarg($projectDir)
                    . ($kit['version'] ? ' ' . escapeshellarg($kit['version']) : '')
                    . ' --prefer-dist --no-interaction --no-progress --no-ansi';
            } else {
                $constraint = LARAVEL_CONSTRAINTS[getLaravelVersion($data)];
                $createCommand = 'composer create-project laravel/laravel ' . escapeshellarg($projectDir)
                    . ' ' . escapeshellarg($constraint) . ' --prefer-dist --no-interaction --no-progress --no-ansi';
            }
            $jobId = startJob(
                'create_project',
                $createCommand,
                $parentDir,
                ['projectDir' => $projectDir, 'parentDir' => $parentDir]
            );
            sendResponse(true, 'Composer started', '', ['job_id' => $jobId]);
            break;

        case 'setup_environment':
            [, $projectDir] = getProjectPaths($data);
            if (!is_dir($projectDir)) {
                throw new Exception('Project directory not found');
            }

            $envFile = $projectDir . DIRECTORY_SEPARATOR . '.env';
            $envExample = $projectDir . DIRECTORY_SEPARATOR . '.env.example';
            if (!is_file($envFile) && (!is_file($envExample) || !copy($envExample, $envFile))) {
                throw new Exception('Failed to create .env file');
            }

            $db = getDbConfig($data);
            if ($db['connection'] === 'sqlite') {
                $sqliteFile = $projectDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
                if (!is_dir(dirname($sqliteFile))) {
                    mkdir(dirname($sqliteFile), 0775, true);
                }
                if (!is_file($sqliteFile)) {
                    touch($sqliteFile);
                }
                $values = [
                    'DB_CONNECTION' => 'sqlite',
                    'DB_DATABASE' => str_replace('\\', '/', $sqliteFile),
                ];
            } else {
                $values = [
                    'DB_CONNECTION' => $db['connection'],
                    'DB_HOST' => $db['host'],
                    'DB_PORT' => $db['port'],
                    'DB_DATABASE' => $db['database'],
                    'DB_USERNAME' => $db['username'],
                    'DB_PASSWORD' => $db['password'],
                ];
            }
            $app = getAppConfig($data);
            if ($app) {
                $values = array_merge([
                    'APP_NAME' => $app['name'],
                    'APP_ENV' => $app['env'],
                    'APP_DEBUG' => $app['debug'] ? 'true' : 'false',
                    'APP_URL' => $app['url'],
                    'APP_TIMEZONE' => $app['timezone'],
                    'APP_LOCALE' => $app['locale'],
                ], $values);
                patchAppConfig($projectDir);
            }
            $values = array_merge($values, runtimeEnvValues(getOpts($data), $projectDir));
            setEnvValues($envFile, $values);

            sendResponse(true, 'Environment configured for ' . $db['connection']);
            break;

        case 'generate_key':
            [, $projectDir] = getProjectPaths($data);
            if (!is_file($projectDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
                sendResponse(false, 'Dependencies are not installed (vendor/autoload.php is missing)');
            }

            $result = executeCommand(phpBinary() . ' artisan key:generate --force --no-interaction', $projectDir);
            if ($result['return_code'] !== 0) {
                sendResponse(false, 'Failed to generate application key', $result['output']);
            }
            sendResponse(true, 'Application key generated successfully', $result['output']);
            break;

        case 'run_migrations':
            [, $projectDir] = getProjectPaths($data);
            if (!is_file($projectDir . DIRECTORY_SEPARATOR . 'artisan')) {
                sendResponse(false, 'artisan was not found in the project');
            }
            $jobId = startJob(
                'run_migrations',
                phpBinary() . ' artisan migrate --force --no-interaction --no-ansi',
                $projectDir,
                ['projectDir' => $projectDir]
            );
            sendResponse(true, 'Migrations started', '', ['job_id' => $jobId]);
            break;

        case 'job_status':
            jobStatus($data['jobId'] ?? '', max(0, (int) ($data['offset'] ?? 0)));
            break;

        case 'job_cancel':
            jobCancel($data['jobId'] ?? '');
            break;

        case 'finalize':
            [, $projectDir] = getProjectPaths($data);
            if (!is_dir($projectDir)) {
                throw new Exception('Project directory not found');
            }

            if (!isWindows()) {
                foreach (['storage', 'bootstrap' . DIRECTORY_SEPARATOR . 'cache'] as $sub) {
                    $target = $projectDir . DIRECTORY_SEPARATOR . $sub;
                    if (is_dir($target)) {
                        executeCommand('chmod -R 775 ' . escapeshellarg($target));
                    }
                }
            }

            $projectInfo = [];
            $composerJson = $projectDir . DIRECTORY_SEPARATOR . 'composer.json';
            if (is_file($composerJson)) {
                $composer = json_decode(file_get_contents($composerJson), true);
                $projectInfo = [
                    'name' => $composer['name'] ?? basename($projectDir),
                    'laravel_version' => installedLaravelVersion($projectDir) ?? ($composer['require']['laravel/framework'] ?? 'Unknown'),
                ];
            }

            try {
                addHistory($data, $projectDir, $projectInfo['laravel_version'] ?? 'Unknown');
            } catch (Throwable $e) {
                logDebug('Could not write history', ['message' => $e->getMessage()]);
            }

            sendResponse(true, 'Installation completed successfully', '', [
                'project_info' => $projectInfo,
                'project_path' => $projectDir,
            ]);
            break;

        case 'starter_kit':
            $projectDir = requireProject($data);
            $opts = getOpts($data);
            $kit = getKitOption($opts);
            if (!in_array($kit['kit'], ['breeze', 'jetstream'], true)) {
                sendResponse(true, 'This starter kit was installed with the project');
            }
            $pest = wantsPest($opts) ? ['--pest'] : [];
            if ($kit['kit'] === 'breeze') {
                $steps = [
                    ['label' => 'Install Breeze', 'cmd' => composerLine('require laravel/breeze --dev')],
                    ['label' => 'Scaffold Breeze (' . $kit['stack'] . ')', 'cmd' => artisanLine(array_merge(['breeze:install', $kit['stack']], $pest))],
                ];
            } else {
                $steps = [
                    ['label' => 'Install Jetstream', 'cmd' => composerLine('require laravel/jetstream')],
                    ['label' => 'Scaffold Jetstream (' . $kit['stack'] . ')', 'cmd' => artisanLine(array_merge(
                        ['jetstream:install', $kit['stack']],
                        $kit['teams'] ? ['--teams'] : [],
                        $pest
                    ))],
                ];
            }
            sendResponse(true, 'Starter kit started', '', ['job_id' => startJob('starter_kit', $steps, $projectDir)]);
            break;

        case 'testing_pest':
            $projectDir = requireProject($data);
            $steps = [
                ['label' => 'Install Pest', 'cmd' => composerLine('require pestphp/pest pestphp/pest-plugin-laravel --dev --with-all-dependencies')],
                ['label' => 'Initialise Pest', 'cmd' => phpBinary() . ' vendor/bin/pest --init --no-ansi'],
            ];
            sendResponse(true, 'Pest install started', '', ['job_id' => startJob('pest', $steps, $projectDir)]);
            break;

        case 'install_packages':
            $projectDir = requireProject($data);
            $opts = getOpts($data);
            $major = projectLaravelMajor($projectDir);
            $require = [];
            $requireDev = [];
            $post = [];
            foreach (selectedPackageIds($opts) as $packageId) {
                $def = PACKAGE_DEFS[$packageId];
                if (!empty($def['unix']) && isWindows()) {
                    throw new Exception($def['label'] . ' is not supported on Windows');
                }
                if (isset($def['min']) && $major < $def['min']) {
                    throw new Exception($def['label'] . ' needs Laravel ' . $def['min'] . ' or newer');
                }
                if ($def['dev']) {
                    $requireDev = array_merge($requireDev, $def['require']);
                } else {
                    $require = array_merge($require, $def['require']);
                }
                foreach ($def['post'] as $tokens) {
                    $post[] = ['label' => $def['label'] . ': ' . $tokens[0], 'cmd' => artisanLine($tokens)];
                }
            }
            if (usesRedis($opts) && !hasPhpRedis()) {
                $require[] = 'predis/predis';
            }

            $steps = [];
            if ($require) {
                $steps[] = ['label' => 'Install packages', 'cmd' => composerLine('require ' . implode(' ', $require))];
            }
            if ($requireDev) {
                $steps[] = ['label' => 'Install dev packages', 'cmd' => composerLine('require --dev ' . implode(' ', $requireDev))];
            }
            $steps = array_merge($steps, $post);
            if (!$steps) {
                sendResponse(true, 'No packages to install');
            }
            sendResponse(true, 'Package install started', '', ['job_id' => startJob('packages', $steps, $projectDir)]);
            break;

        case 'frontend_bootstrap':
            $projectDir = requireProject($data);
            scaffoldBootstrap($projectDir);
            sendResponse(true, 'Bootstrap 5 is wired into Vite and the welcome page');
            break;

        case 'npm_build':
            $projectDir = requireProject($data);
            if (!is_file($projectDir . DIRECTORY_SEPARATOR . 'package.json')) {
                sendResponse(true, 'No package.json, nothing to build');
            }
            $steps = [
                ['label' => 'npm install', 'cmd' => 'npm install --no-fund --no-audit --no-progress'],
                ['label' => 'npm run build', 'cmd' => 'npm run build'],
            ];
            sendResponse(true, 'Frontend build started', '', ['job_id' => startJob('npm', $steps, $projectDir)]);
            break;

        case 'sail_install':
            $projectDir = requireProject($data);
            $opts = getOpts($data);
            $sail = is_array($opts['sail'] ?? null) ? $opts['sail'] : [];
            $services = [];
            $driver = $data['db']['connection'] ?? '';
            if ($driver === 'mysql' || $driver === 'pgsql') {
                $services[] = $driver;
            }
            foreach (is_array($sail['services'] ?? null) ? $sail['services'] : [] as $service) {
                if (is_string($service) && in_array($service, SAIL_SERVICES, true) && !in_array($service, $services, true)) {
                    $services[] = $service;
                }
            }
            $installTokens = ['sail:install'];
            if ($services) {
                $installTokens[] = '--with=' . implode(',', $services);
            }
            $steps = [
                ['label' => 'Install Sail', 'cmd' => composerLine('require laravel/sail --dev')],
                ['label' => 'Publish Sail files', 'cmd' => artisanLine($installTokens)],
            ];
            sendResponse(true, 'Sail install started', '', ['job_id' => startJob('sail', $steps, $projectDir)]);
            break;

        case 'git_setup':
            $projectDir = requireProject($data);
            $steps = gitSetupSteps($projectDir, getOpts($data));
            sendResponse(true, 'Git setup started', '', ['job_id' => startJob('git', $steps, $projectDir)]);
            break;

        case 'serve_start':
            $projectDir = requireProject($data);
            $port = findFreePort();
            $jobId = startJob('serve', phpBinary() . ' artisan serve --host=127.0.0.1 --port=' . $port . ' --no-ansi', $projectDir);
            sendResponse(true, 'Server starting', '', ['job_id' => $jobId, 'port' => $port, 'url' => 'http://127.0.0.1:' . $port]);
            break;

        case 'health_about':
            $projectDir = requireProject($data);
            $result = executeCommand(phpBinary() . ' artisan about --json --no-ansi', $projectDir, 120);
            $about = extractJson($result['output']);
            if ($result['return_code'] !== 0 || !is_array($about)) {
                sendResponse(false, 'Could not read the application summary', $result['output']);
            }
            sendResponse(true, 'About loaded', '', ['about' => $about]);
            break;

        case 'health_routes':
            $projectDir = requireProject($data);
            $result = executeCommand(phpBinary() . ' artisan route:list --json --no-ansi', $projectDir, 120);
            $routes = extractJson($result['output']);
            if ($result['return_code'] !== 0 || !is_array($routes)) {
                sendResponse(false, 'Could not list the routes', $result['output']);
            }
            $sample = [];
            foreach (array_slice($routes, 0, 12) as $route) {
                $sample[] = [
                    'method' => (string) ($route['method'] ?? ''),
                    'uri' => (string) ($route['uri'] ?? ''),
                    'name' => (string) ($route['name'] ?? ''),
                ];
            }
            sendResponse(true, 'Routes loaded', '', ['count' => count($routes), 'sample' => $sample]);
            break;

        case 'health_migrations':
            $projectDir = requireProject($data);
            $result = executeCommand(phpBinary() . ' artisan migrate:status --no-ansi', $projectDir, 120);
            if ($result['return_code'] !== 0) {
                sendResponse(false, 'Migration status is not available', $result['output']);
            }
            $ran = preg_match_all('/\bRan\b/', $result['output']);
            $pending = preg_match_all('/\bPending\b/', $result['output']);
            sendResponse(true, 'Migration status loaded', '', ['ran' => $ran, 'pending' => $pending]);
            break;

        case 'template_list':
            sendResponse(true, 'Templates listed', '', ['templates' => readList('templates.json')]);
            break;

        case 'template_save':
            saveTemplate($data['templateName'] ?? '', $data['config'] ?? null);
            sendResponse(true, 'Template saved', '', ['templates' => readList('templates.json')]);
            break;

        case 'template_delete':
            $templateName = (string) ($data['templateName'] ?? '');
            writeList('templates.json', array_filter(readList('templates.json'), function ($t) use ($templateName) {
                return ($t['name'] ?? '') !== $templateName;
            }));
            sendResponse(true, 'Template deleted', '', ['templates' => readList('templates.json')]);
            break;

        case 'history_list':
            $history = readList('history.json');
            foreach ($history as &$entry) {
                $entry['exists'] = isset($entry['path']) && is_dir($entry['path']);
            }
            unset($entry);
            sendResponse(true, 'History listed', '', ['history' => $history]);
            break;

        case 'history_delete':
            $historyId = (string) ($data['historyId'] ?? '');
            writeList('history.json', array_filter(readList('history.json'), function ($h) use ($historyId) {
                return ($h['id'] ?? '') !== $historyId;
            }));
            sendResponse(true, 'History entry removed');
            break;

        case 'state_save':
            saveWizardState($data['state'] ?? null);
            sendResponse(true, 'State saved');
            break;

        case 'state_load':
            $state = loadWizardState();
            sendResponse(true, $state ? 'State found' : 'No saved state', '', ['state' => $state]);
            break;

        case 'state_clear':
            @unlink(stateFile());
            sendResponse(true, 'State cleared');
            break;

        case 'lock':
            if (file_put_contents(LOCK_FILE, 'locked ' . date('c')) === false) {
                sendResponse(false, 'Could not write installer.lock');
            }
            sendResponse(true, 'Installer locked');
            break;

        default:
            sendResponse(false, 'Unknown step');
    }
} catch (Throwable $e) {
    logDebug('Installer error', ['message' => $e->getMessage()]);
    sendResponse(false, $e->getMessage());
}
