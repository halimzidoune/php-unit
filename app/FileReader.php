<?php 

namespace App;

use App\Exceptions\NoFileException;
use App\Exceptions\FileNotExistException;
use App\Exceptions\TransactionFieldsException;

class FileReader{
	
	/*
	* Return an array of transaction readed from a file gived as parameter
	*/
	public static function ReadFile($fileName){
		if($fileName == null){
			throw new NoFileException;
		}

		if(!file_exists($fileName)){
			throw new FileNotExistException;
		}

		$result = [];

		$content = file_get_contents($fileName);
		$content = preg_replace("/(\R){2,}/", "$1", $content);//remove empty lines
		$lines = explode("\n", $content);
		foreach ($lines as $line) { //ligne par ligne
		    if (!empty($line)){
		    	// Transform a line into object ex: line = {"bin":"45717360","amount":"100.00","currency":"EUR"}
		    	$transaction = json_decode($line);
		    	
		    	if( !property_exists($transaction, "bin") || 
		    		!property_exists($transaction, "amount") || 
		    		!property_exists($transaction, "currency") 
		    	){
		    		throw new TransactionFieldsException;
		    	}
		    	$result[] = $transaction;
		    }
		    
		}

		return $result;
	}
}