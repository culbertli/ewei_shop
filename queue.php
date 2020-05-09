<?php
class Queue
{
	public $tableName = 'ewei_shop_queue';
	public $channel = 'queue';
	public $deleteReleased = true;
	private $_workerPid;

	public function __construct()
	{
		spl_autoload_register(array('self', 'autoload'), true, true);
	}

	static public function autoload($className)
	{
		$classFile = EWEI_SHOPV2_PATH . str_replace('\\', '/', $className) . '.php';
		if ($classFile === false || !is_file($classFile)) {
			return NULL;
		}

		include $classFile;
	}

	/**
     * Listens queue and runs each job.
     *
     * @param bool $repeat whether to continue listening when queue is empty.
     * @param int $timeout number of seconds to sleep before next iteration.
     * @return null|int exit code.
     * @internal for worker command only
     * @since 2.0.2
     */
	public function run($repeat, $timeout = 0)
	{
		return $this->runWorker(function( $canContinue) use($repeat, $timeout) {
			while ($canContinue()) {
				if ($payload = $this->reserve()) {
					if ($this->handleMessage($payload['id'], $payload['job'], $payload['ttr'], $payload['attempt'])) {
						$this->release($payload);
					}
				}
				else {
					if (!$repeat) {
						break;
					}

					if ($timeout) {
						sleep($timeout);
					}
				}
			}
		});
	}

	/**
     * Runs worker.
     *
     * @param callable $handler
     * @return null|int exit code
     * @since 2.0.2
     */
	protected function runWorker( $handler)
	{
		$this->_workerPid = getmypid();
		$loop = new SignalLoop($this);
		file_put_contents(EWEI_SHOPV2_CORE . 'queue.pid', $this->_workerPid);

		if (function_exists('chmod')) {
			chmod(EWEI_SHOPV2_CORE . 'queue.pid', 511);
		}
/* [31m * TODO FAST_CALL[0m */

		$exitCode = NULL;
		call_user_func($handler, function() use($loop) {
			return $loop->canContinue();
		});
/* [31m * TODO FAST_RET[0m */

		goto label38;
		$this->_workerPid = NULL;
label38:
	}

	/**
     * Takes one message from waiting list and reserves it for handling.
     *
     * @return array|false payload
     * @throws Exception in case it hasn't waited the lock
     */
	protected function reserve()
	{
		$payload = pdo_fetch('SELECT * FROM ' . tablename($this->tableName) . (' WHERE `channel`=\'' . $this->channel . '\' AND `reserved_at` IS NULL AND `pushed_at`<=:time - delay ORDER BY `priority` ASC, `id` ASC limit 1'), array(':time' => time()));

		if (is_array($payload)) {
			$payload['reserved_at'] = time();
			$payload['attempt'] = (int) $payload['attempt'] + 1;
			pdo_update($this->tableName, array('reserved_at' => $payload['reserved_at'], 'attempt' => $payload['attempt']), array('id' => $payload['id']));

			if (is_resource($payload['job'])) {
				$payload['job'] = stream_get_contents($payload['job']);
			}
		}

		return $payload;
	}

	protected function handleMessage($id, $message, $ttr, $attempt)
	{
		list($job, $error) = $this->unserializeMessage($message);

		if (empty($job)) {
			return false;
		}

		$job->execute($this);
		return true;
	}

	/**
     * @param $serialized
     * @return array
     */
	public function unserializeMessage($serialized)
	{
		try {
			$job = unserialize($serialized);
		}
		catch (Exception $e) {
			return array(NULL, new Exception($serialized, $e->getMessage(), 0, $e));
		}

		return array($job, NULL);
	}

	/**
     * @param array $payload
     */
	protected function release($payload)
	{
		if ($this->deleteReleased) {
			pdo_delete($this->tableName, array('id' => $payload['id']));
		}
		else {
			pdo_update($this->tableName, array('done_at' => time()), array('id' => $payload['id']));
		}
	}

	public function fileGlob($path, $recursive = true)
	{
		$res = array();

		if (substr($path, -1) !== '*') {
			$path = $path . '*';
		}

		foreach (glob($path) as $file) {
			if ($file != '.' && $file != '..') {
				$relative_path = str_replace(EWEI_SHOPV2_PATH, '', $file);

				if (is_dir($file)) {
					if ($recursive) {
						$res = array_merge($res, $this->fileGlob($file . '/*', $recursive));
					}
				}
				else {
					$res[$relative_path] = $file;
				}
			}
		}

		return $res;
	}
}

class SignalLoop
{
	/**
     * @var array of signals to exit from listening of the queue.
     */
	public $exitSignals = array(15, 2, 1);
	/**
     * @var array of signals to suspend listening of the queue.
     * For example: SIGTSTP
     */
	public $suspendSignals = array();
	/**
     * @var array of signals to resume listening of the queue.
     * For example: SIGCONT
     */
	public $resumeSignals = array();
	/**
     * @var Queue
     */
	protected $queue;
	/**
     * @var bool status when exit signal was got.
     */
	static private $exit = false;
	/**
     * @var bool status when suspend or resume signal was got.
     */
	static private $pause = false;

	/**
     * @param Queue $queue
     * @inheritdoc
     */
	public function __construct($queue)
	{
		$this->queue = $queue;
	}

	/**
     * Sets signal handlers.
     *
     * @inheritdoc
     */
	public function init()
	{
		if (extension_loaded('pcntl')) {
			foreach ($this->exitSignals as $signal) {
				pcntl_signal($signal, function() {
					self::$exit = true;
				});
			}

			foreach ($this->suspendSignals as $signal) {
				pcntl_signal($signal, function() {
					self::$pause = true;
				});
			}

			foreach ($this->resumeSignals as $signal) {
				pcntl_signal($signal, function() {
					self::$pause = false;
				});
			}
		}
	}

	/**
     * Checks signals state.
     *
     * @inheritdoc
     */
	public function canContinue()
	{
		if (extension_loaded('pcntl')) {
			pcntl_signal_dispatch();

			while (self::$pause && !self::$exit) {
				usleep(10000);
				pcntl_signal_dispatch();
			}
		}

		return !self::$exit;
	}
}

error_reporting(0);
require dirname(__FILE__) . '/../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
$queue = new Queue();
$queue->run(true, 3);

?>
