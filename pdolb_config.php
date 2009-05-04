<?php
/**
 * PDOLB_CONFIG class
 */
class PDOLB_CONFIG {

	/**
	 * query mode, possible values: PDOLB::MASTER_SLAVE, PDOLB::MASTER, PDOLB::SLAVE
	 *
	 * @var int $queryMode
	 */
	public static $queryMode = PDOLB::MASTER_SLAVE;

	/**
	 * databases (masters and slaves) list
	 * each database contains 5 parameters which same as the PDO::__construct method except the last one.
	 *
	 * @var array $databases
	 */
	public static $databases = array(

		// masters (1 or more)
		'master' => array(
			array(
				'dsn'            => 'mysql:host=localhost;dbname=test',
				'username'       => 'root',
				'password'       => '',
				'driver_options' => array(),
				'weight'         => 1
			)
		),

		// slaves (1 or more)
		'slave'  => array(

			// slave 1
			array(
				'dsn'            => 'mysql:host=127.0.0.1;dbname=test',
				'username'       => 'root',
				'password'       => '',
				'driver_options' => array(),
				'weight'         => 1
			),

			// slave 2
			array(
				'dsn'            => 'mysql:host=lighttpd;dbname=test',
				'username'       => 'root',
				'password'       => '',
				'driver_options' => array(),
				'weight'         => 1
			)

			// can add more slaves below ...
		)
	);
}
