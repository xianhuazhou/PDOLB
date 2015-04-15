== Introduction
A Load Balance class for the PHP PDO extension with master/slave mode of MySQL. if one of the slaves or masters failed, will try to connect others automatically.

== Examples

using PDO
```php
	<?php 
		$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
		$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
		$stmt->execute(array(10));
	?>
```

using PDOLB
1. configure the pdolb_config.php file
```php
	<?php
	class PDOLB_CONFIG {
		public static $queryMode = PDOLB::MASTER_SLAVE;
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
					'dsn'            => 'mysql:host=192.168.0.4;dbname=test',
					'username'       => 'root',
					'password'       => '',
					'driver_options' => array(),
					'weight'         => 1
				)

				// can add more slaves below ...
			)
		);
	}
```

2. example
```php
	<?php
        # see example.php as well.
		# get a PDO instance. exactly, it is a instance of the PDOLB class, however,the PDOLB class extends the PDO class.
        require 'path/to/pdolb.php';
		$pdo = PDOLB::getInstance();
		$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
		$stmt->execute(array(10));
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        $stmt->closeCursor();
        $stmt = null;
        $pdo = null;
```

== Author
xianhua.zhou@gmail.com 
