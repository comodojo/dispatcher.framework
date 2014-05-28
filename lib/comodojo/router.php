<?php

/**
	 * Record some statistic info on database
	 * 
	 * @return	bool	Record status
	 */
	private function recordStat() {
		if (GLOBAL_STATISTICS_ENABLED) {
			try {
				$dbh = $this->createDatabaseHandler(STATISTICS_DB_DATA_MODEL, STATISTICS_DB_HOST, STATISTICS_DB_PORT, STATISTICS_DB_NAME, STATISTICS_DB_USER, STATISTICS_DB_PASSWORD);
				$example_result = $this->query($dbh, "INSERT INTO `comodojo_statistics` (id,timestamp,service,address,userAgent) VALUES (0,".strtotime('now').",'".$this->service."','".$_SERVER["REMOTE_ADDR"]."','".$_SERVER["HTTP_USER_AGENT"]."')", STATISTICS_DB_DATA_MODEL);
			}
			catch (Exception $e) {
				$this->debug("Statistics error: " . $e->getMessage());
				return false;
			}
			$this->closeDatabaseHandler($dbh, STATISTICS_DB_DATA_MODEL);
			return true;
		}
	}

?>