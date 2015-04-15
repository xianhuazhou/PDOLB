<?php
/**
 *
 * A Load balance class for the PHP PDO extension, can be used in the master/slave mode of MySQL.
 *
 * Basically, the PDOLB can dispatch the query of changing data to the master(s), and dispatch the select query to the slave(s), master(s) or both, which depends on the PDOLB_CONFIG::$queryMode variable.
 *
 * @author xianhua.zhou@gmail.com
 * @version 0.1 beta
 *
 */
require realpath(__DIR__) . '/pdolb_config.php';

/**
 *
 * PDOLB class
 *
 * Examples: 
 *
 * $pdo = PDOLB::getInstance();
 * foreach ($pdo->query("SELECT * FROM users") as $row) {
 *   echo $row['name'];
 * }
 *
 * $pdo = PDOLB::getInstance();
 * $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
 * $stmt->execute(array(10));
 * while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 * }
 *
 * $pdo = PDOLB::getInstance();
 * $pdo->exec("DROP TABLE users");
 *
 * $pdo = PDOLB::getInstance();
 * $stm = $pdo->prepare("UPDATE users SET name = ?");
 * $stmt->execute(array('user name'));
 *
 */
class PDOLB extends PDO {

	/**
	 * PDOLB connections
	 *
	 * @var private $connections
	 */
	private static $connections = array();

	/**
	 * databases list for PDO
	 *
	 * @var private $databases
	 */
	private static $databases = array();

	/**
	 * for the PDOLB_CONFIG::$queryMode variable
	 *
	 * MASTER_SLAVE: query data from master(s) and slave(s) 
	 * MASTER:       query data from master(s) only
	 * SLAVE:        query data from slave(s) only
	 *
	 */
    const MASTER_SLAVE = 0;
    const MASTER = 1;
    const SLAVE = 2;

    /**
     *
     * get PDOLB instance from master(s)
     *
     * @param array $databases
     *
     * @access public
     * @return PDOLB
     *
     */
    public static function getInstance($databases = array()) {
        self::$databases = $databases ? $databases : PDOLB_CONFIG::$databases;
        return self::getMaster();
    }

    /**
     *
     * shuffle databases for the method getRandomPDOLB
     *
     * @param array $databases
     *
     * @access private
     * @return null
     * @see PDOLB::getRandomPDOLB
     *
     */
    private static function shuffleDatabases(&$databases) {
        $size = count($databases);
        $database = null;
        for($i = 0; $i < $size; $i++) {
            $database = $databases[$i];
            for ($weight = 1; $weight <= $database['weight']; $weight++) {
                $databases[] = $database;
            }
        }
        shuffle($databases);
    }

    /**
     *
     * get a random PDOLB instance
     *
     * @param int $type
     *
     * @access private
     * @return PDOLB
     *
     */
    private static function getRandomPDOLB($type = self::MASTER_SLAVE) {
        switch($type) {
        case self::MASTER_SLAVE:
            $databases = array_merge(self::$databases['master'], self::$databases['slave']);
            break;
        case self::MASTER:
            $databases = self::$databases['master'];
            break;
        case self::SLAVE:
            $databases = self::$databases['slave'];
            break;
        }

        self::shuffleDatabases($databases);

        $pdolb = null;
        $exceptions = array();
        foreach ($databases as $database) {
            if (isset(self::$connections[$database['dsn']])) {
                return self::$connections[$database['dsn']];
            }

            try {
                $pdolb = new PDOLB(
                    $database['dsn'], 
                    $database['username'], 
                    $database['password'], 
                    $database['driver_options']
                );
            } catch (Exception $e) {
                $exceptions[$database['dsn']] = $e->getMessage();
                continue;
            }

            if ($pdolb && '' == $pdolb->errorCode()) {
                return self::$connections[$database['dsn']] = $pdolb;
            } else {
                $exceptions[$database['dsn']] = $pdolb->errorInfo();
            }
        }

        throw new PDOLBException("No available database!\n" . print_r($exceptions, true));
    }

    /**
     *
     * get a PDOLB instance from master(s) and slave(s)
     *
     * @access private
     * @return PDOLB
     *
     */
    private static function getMasterSlave() {
        return self::getRandomPDOLB(self::MASTER_SLAVE);
    }

    /**
     *
     * get a PDOLB instance from master(s) only
     *
     * @access private
     * @return PDOLB
     *
     */
    private static function getMaster() {
        return self::getRandomPDOLB(self::MASTER);
    }

    /**
     *
     * get a PDOLB instance from slave(s) only
     *
     * @access private
     * @return PDOLB
     *
     */
    private static function getSlave() {
        return self::getRandomPDOLB(self::SLAVE);
    }

    /**
     *
     * get a PDOLB instance depends on the PDOLB_CONFIG::$queryMode variable
     *
     * @access private
     * @return PDOLB
     *
     */
    private static function getPDOLB() {
        switch (PDOLB_CONFIG::$queryMode) {
        case self::MASTER_SLAVE:
            return self::getMasterSlave();
        case self::MASTER:
            return self::getMaster();
        case self::SLAVE:
            return self::getSlave();
        }
    }

    /**
     * overwriten PDO::query
     */
    public function query($statement) {
        return self::getPDOLB()->_query($statement);
    }
    private function _query($statement) {
        return parent::query($statement);
    }

    /**
     * overwriten PDO::prepare
     */
    public function prepare($statement, $driverOptions = array()) {
        return self::getPDOLB()->_prepare($statement, $driverOptions);
    }
    private function _prepare($statement, $driverOptions = array()) {
        return parent::prepare($statement, $driverOptions);
    }

    /**
     * overwriten PDO::exec
     */
    public function exec($statement) {
        return self::getMaster()->_exec($statement);
    }
    private function _exec($statement) {
        return parent::exec($statement);
    }
}

/**
 * PDOLBException
 */
class PDOLBException extends Exception {}
