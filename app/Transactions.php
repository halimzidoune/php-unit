<?php 

namespace App;

use App\Exceptions\RatesAPIException;
use App\Exceptions\RatesAcessKeyException;

class Transactions{

	protected $fileName;
	protected $transactions = [];
	protected $rates;

	public function __construct($fileName = null){
		$this->fileName = $fileName;
	}

	public function setFile($fileName){
		$this->fileName = $fileName;
	}

	public function getFile(){
		return $this->fileName;
	}

	public function getTransactions(){
		return $this->transactions;
	}

	public function readTransactions(){
		$this->transactions = FileReader::ReadFile($this->fileName);
	}

	/**
	* main functionReturn an array of results
	**/
	public function handle(){
		$result = [];
		$this->readTransactions();

		foreach ($this->transactions as $transaction) {
			$card = new Card($transaction->bin);
			$rate = $this->getRate($transaction->currency);
			$fixedAmout = $this->getFixedAmount($transaction->currency, $rate, $transaction->amount);
			$result[] = round($fixedAmout * $card->getCommissionRate(), 2);
		}
		return $result;
	}

	public function getRatesFromApi(){
		/* The Correct Code
		$file_headers = @get_headers(RATE_EXCANGE_API);
		
		if(!$file_headers || 
	    	$file_headers[0] == 'HTTP/1.1  404 NOT FOUND' || 
	    	$file_headers[0] == 'HTTP/1.1 400 Bad Request'
	    ){
	    	throw new RatesAPIException;
	    }

		return file_get_contents(RATE_EXCANGE_API);*/

		// mocked Result
		$mockedRatesResult = [
			"rates" => [
				"EUR" => 0,
				"USD" => 1.0827,
				"JPY" => 120.67,
				"GBP" => 0.91503
			]
		];
		return json_encode($mockedRatesResult);
	}

	public function getRate($currency){
		if($this->rates == null){
			$this->rates = $this->getRatesFromApi();
		}

		$result = @json_decode($this->rates, true);

		if(array_key_exists("error", $result)){
			if($result["error"]["type"] == "missing_access_key"){
				throw new RatesAcessKeyException;
			};
		}
		
		return $result['rates'][$currency];
	}

	public function getFixedAmount($currency, $rate, $amount){
		$amntFixed = 0;
		if ($currency == 'EUR' or $rate == 0) {
	        $amntFixed = $amount;
	    }
	    if ($currency != 'EUR' or $rate > 0) {
	        $amntFixed = $amount / $rate;
	    }
	    return $amntFixed;
	}
}