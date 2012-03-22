<?php
	class Database {
		private $database, $username, $password, $pdo;
		
		function __construct() {
			$this->database = "*****";
			$this->username = "*****";
			$this->password = "*****";
			$this->pdo = new PDO('mysql:host=*****;dbname='.$this->database, $this->username, $this->password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}
		
		public function doQuery($sql, $param){
			try{
				$stm = $this->pdo->prepare($sql);
				$stm->execute($param);
				$results = $stm->fetchAll();
				return $results;
			}
			catch(PDOException $e) 
			{ 
				echo '<pre>'; 
				echo 'Regelnummer: '.$e->getLine().'<br>'; 
				echo 'Bestand: '.$e->getFile().'<br>'; 
				echo 'Foutmelding: '.$e->getMessage().'<br>'; 
				echo '</pre>';
			}
		}
		
		public function rowCount($sql, $param){
			try{
				$stm = $this->pdo->prepare($sql);
				$stm->execute($param);
				$rows = $stm->rowCount();
				return $rows;
			} catch(PDOException $e){
				echo '<pre>'; 
				echo 'Regelnummer: '.$e->getLine().'<br>'; 
				echo 'Bestand: '.$e->getFile().'<br>'; 
				echo 'Foutmelding: '.$e->getMessage().'<br>'; 
				echo '</pre>';
			}
		}
		public function execQuery($sql, $parameters){
				
			try{
				$stm = $this->pdo->prepare($sql);
				$stm->execute($parameters);
				return true;
			}
			catch(PDOException $e) 
			{ 
				echo '<pre>'; 
				echo 'Regelnummer: '.$e->getLine().'<br>'; 
				echo 'Bestand: '.$e->getFile().'<br>'; 
				echo 'Foutmelding: '.$e->getMessage().'<br>'; 
				echo '</pre>'; 
			}
		}
		public function delQuery($sql){
			try{
				$this->pdo->exec($sql); 
			}
			catch(PDOException $e) 
			{ 
				echo '<pre>'; 
				echo 'Regelnummer: '.$e->getLine().'<br>'; 
				echo 'Bestand: '.$e->getFile().'<br>'; 
				echo 'Foutmelding: '.$e->getMessage().'<br>'; 
				echo '</pre>';
			}
		}
		
		public function getRows($sql) {
			try{
				$stm = $this->pdo->prepare($sql);
				$stm->execute();
				return $stm->rowCount();
			}
			catch(PDOException $e) 
			{ 
				echo '<pre>'; 
				echo 'Regelnummer: '.$e->getLine().'<br>'; 
				echo 'Bestand: '.$e->getFile().'<br>'; 
				echo 'Foutmelding: '.$e->getMessage().'<br>'; 
				echo '</pre>';
			}
		}
		public function getRowsParam($sql, $param) {
			try{
				$stm = $this->pdo->prepare($sql);
				$stm->execute($param);
				return $stm->rowCount();
			}
			catch(PDOException $e) 
			{ 
				echo '<pre>'; 
				echo 'Regelnummer: '.$e->getLine().'<br>'; 
				echo 'Bestand: '.$e->getFile().'<br>'; 
				echo 'Foutmelding: '.$e->getMessage().'<br>'; 
				echo '</pre>';
			}
		}
		
	}
?>