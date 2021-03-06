<?php
#   PLEASE DO NOT REMOVE OR CHANGE THIS COPYRIGHT BLOCK
#   ====================================================================
#
#    Quran Analysis (www.qurananalysis.com). Full Semantic Search and Intelligence System for the Quran.
#    Copyright (C) 2015  Karim Ouda
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#    You can use Quran Analysis code, framework or corpora in your website
#	 or application (commercial/non-commercial) provided that you link
#    back to www.qurananalysis.com and sufficient credits are given.
#
#  ====================================================================

define ( "DATABASE_LOCK_ERROR", "Database is being updated ! try again later" );
define ( "DATABASE_CONN_ERROR", "Could not connect to DB !" );
define ( 'SQLITE3_OPEN_SHAREDCACHE', 0x00020000 );

define("MAILING_LIST_TABLE",
"CREATE TABLE IF NOT EXISTS EmailList " .
"(subscriberId INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT,  title TEXT, entity TEXT, email TEXT, UNIQUE(email)  )");

define("FEEDBACK_TABLE",
"CREATE TABLE IF NOT EXISTS Feedback " .
"(feedbackId INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT,email TEXT, type TEXT, feedback_text TEXT, UNIQUE(email,feedback_text)  )");



class SQLite3DataLayer 
{
	public $databaseConn = null;
	
	public function isConnected() 
	{
		return ($this->databaseConn != null);
	}
	
	public function openDB($dbPath, $mode = "ro") 
	{
		if ($this->databaseConn == null) {
			
	
			
			if ($mode == "rw") 
			{
				$mode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
			}
			else if ($mode == "ro") 
			{
				$mode = SQLITE3_OPEN_READONLY | SQLITE3_OPEN_SHAREDCACHE;
			}
			
			try 
			{
				
				$this->databaseConn = new SQLite3 ($dbPath, $mode );
				
				$this->databaseConn->busyTimeout ( 3000 );
				
				$res = $this->execOnewayQuery ( 'PRAGMA temp_store=MEMORY;' );
				$this->execOnewayQuery ( 'PRAGMA journal_mode=MEMORY;' );
				$this->execOnewayQuery ( 'PRAGMA cache_size=10000;' );
				$this->execOnewayQuery ( 'PRAGMA read_uncommitted=1' );
			} 
			catch ( Exception $e )
			{
				
				$this->databaseConn = null;
				return null;
			}
		}
		
		
		return $this->databaseConn;
	}
	
	public function queryDB($sql, $params = null) 
	{
		if (empty ( $sql )) {
			throw new Exception ( "Empty Query" );
		}
		
		if ($this->databaseConn == null) {
			
			$this->databaseConn = $this->getDBConnection ();
		}
		
		if ($this->databaseConn == null) 
		{
			return null;
		}
		
		$resObj = $this->databaseConn->query ( $sql );
		

		if ($pdoResObj !== FALSE) 
		{

			$results = $resObj;
		}
		else 
		{
			
			$results = null;
		}
		

		//$this->onErrorShowDebugformation ( $pdoResObj, $sql );
		
		
		return $results;
	}
	
	public function queryDBSingle($sql)
	{
		if (empty ( $sql )) 
		{
			throw new Exception ( "Empty Query" );
		}
		
		if ($this->databaseConn == null) {
			$this->databaseConn = $this->getDBConnection ();
		}
		
		if ($this->databaseConn)
		{
			
			$results = $this->databaseConn->querySingle ( $sql );

			return $results;
		} 
		else 
		{
			return null;
		}
	}
	
	public function execOnewayQuery($sql) 
	{
		if (empty ( $sql ))
		{
			throw new Exception ( "Empty Query" );
		}
		
		if ($this->databaseConn == null) {
			$this->databaseConn = $this->getDBConnection ();
		}
		
		if ($this->databaseConn) {
			$execRes = $this->databaseConn->exec ( $sql );
		} else {
			return null;
		}
		
		//$this->onErrorShowDebugformation ( $execRes, $sql );
		
		return $execRes;
	}
	public function doesTableExist($tableName) 
	{
		if ($tableName == null) 
		{
			return false;
		}
		
		$results = $this->queryDB ( "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = '$tableName'" );
		
		if (empty ( $results )) 
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	public function onErrorShowDebugformation($execRes, $sql) 
	{
		global $report;
		
		if ($execRes === false)
		{
			
			if ( $_SERVER ['REMOTE_ADDR'] == "127.0.0.1")
			{
		
				
				echo $sql . "\n<br>";
				
				echo "\nError:\n<br> ";
				print $this->databaseConn->lastErrorCode () . " : " . print_r ( $this->databaseConn->lastErrorMsg (), true );
				var_dump ( $execRes );
			}
		}
	}
	
	public function getLastInsertId()
	{
		if ($this->databaseConn)
		{
			return $this->databaseConn->lastInsertRowID ();
		} 
		else 
		{
			return null;
		}
	}
	public function closeDBConnection()
	{
	
		if ($this->databaseConn) 
		{
			
			$this->databaseConn->close ();
		}
		
	}
	
	public function __destruct() {
		if (isset ( $this->databaseConn ) && $this->databaseConn != null) {
			// function not found
			$this->databaseConn = null;
		}
	}
	
	public function lastErrorCode() 
	{
		if (isset ( $this->databaseConn ) && $this->databaseConn != null) 
		{
			// function not found
			return $this->databaseConn->lastErrorCode ();
		}
	}
}

?>
