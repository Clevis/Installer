<?php

new Installer;

/**
 * Helps to run application on first time
 */
class Installer
{

	private $foldersToChmod = array(
							'temp/' => 0777,
							'log/' => 0777,
						);

	/** @var  array */
	private $config = array();

	/** @var string */
	private $folderName;

	/** @var string */
	private $databaseName;


	public function __construct()
	{
		$this->folderName = basename(getcwd());
		$this->readDatabaseConfig();
		$this->createDatabase();
		$this->createConfig();
		$this->setPermissions();
		$this->warning("Please run migrations and 'composer update --dev --prefer-dist' if you're starting a new project.");
	}

	/**
	 * Reads database config from standard input
	 */
	private function readDatabaseConfig()
	{
		$values = array(
			'host' => 'localhost',
			'username' => 'root',
			'password' => '',
			'database' => $this->folderName,
		);
		foreach ($values as $field => $default)
		{
			if (!extension_loaded('readline')) {
				echo 'Enter database ' . $field . ' [' . $default . ']: ';
				$input = stream_get_line(STDIN, 1024, PHP_EOL);
			} else {
				$input = readline('Enter database ' . $field . ' [' . $default . ']: ');
			}

			if ($input === '')
			{
				$this->config['db'][$field] = $default;
			}
			else
			{
				$this->config['db'][$field] = $input;
			}
		}
	}

	/**
	 * Creates database with name based on folder
	 */
	private function createDatabase()
	{
		$connection = @mysqli_connect($this->config['db']['host'], $this->config['db']['username'], $this->config['db']['password']);
		if (mysqli_connect_errno())
		{
			$this->error('Failed to connect to MySQL: ' . mysqli_connect_error());
		}

		$sql = 'CREATE DATABASE ' . $this->config['db']['database'];
		if (!mysqli_query($connection, $sql))
		{
			$this->error('Error creating database: ' . mysqli_error($connection));
		}

		$this->success('Database created ...');
	}

	/**
	 * Sets permissions on specified folders.
	 */
	private function setPermissions()
	{
		foreach ($this->foldersToChmod as $folder => $permissions)
		{
			chmod(__DIR__ . '/' . $folder, $permissions);
		}

		$this->success('Permissions changed ...');
	}

	/**
	 * Copies example config and edits it accroding to credentials
	 */
	private function createConfig()
	{
		$configFilename = __DIR__ . '/app/config/config.local.neon';

		copy(__DIR__ . '/app/config/config.local.example.neon', $configFilename);

		$lines = file($configFilename);

		foreach ($lines as $key => $line)
		{
			foreach (array('username', 'password', 'database') as $subject)
			{
				if (strpos($line, "\t" . $subject))
				{
					$lines[$key] = $this->deleteNewline($line, "\n") . ' ' . $this->config['db'][$subject] . "\n";
				}
			}
		}

		if (file_put_contents($configFilename, $lines))
		{
			$this->success('Local config file created ...');
		}
	}

	/**
	 * Removes new line from string
	 * @param  string
	 * @return string
	 */
	private function deleteNewline($string)
	{
		return trim($string, "\n");
	}

	/**
	 * Prints error message and dies.
	 * @param  string
	 */
	private function error($string)
	{
		echo "\033[0;31m" . $string . "\033[0m\n";
		exit(1);
	}

	/**
	 *
	 * Prints success message.
	 * @param  string
	 */
	private function success($string)
	{
		echo "\033[0;32m" . $string . "\033[0m\n";
	}

	/**
	 *
	 * Prints warning message.
	 * @param  string
	 */
	private function warning($string)
	{
		echo "\033[0;36m" . $string . "\033[0m\n";
	}

}
