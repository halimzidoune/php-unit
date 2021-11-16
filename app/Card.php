<?php 

namespace App;

use App\Exceptions\CardInfosException;;

class Card{
	protected $bin;

	public function __construct($bin){
		$this->bin = $bin;
	}

	public function getBin(){
		return $this->bin;
	}

	/*
	* the result should be like this for bin = 45717360
		// $binResults = {
		    "number":{"length":16,"luhn":true},
		    "scheme":"visa",
		    .....
		    "country":{
		    	"numeric":"208",
		    	"alpha2":"DK",
		    	"name":"Denmark",
		    	"emoji":"🇩🇰",
		    	"currency":"DKK",
		    	"latitude":56,
		    	"longitude":10
		    },
		   "bank":{"name":"Jyske Bank",
		    			"url":"www.jyskebank.dk",
		    			"phone":"+4589893300",
		    			"city":"Hjørring"
		    		}
		// }
	*/

	public function getInfos(){
	    $binResults = null;
	    $url = CARD_INFO_API . $this->bin;
	    $file_headers = @get_headers($url);

	    if(!$file_headers || 
	    	$file_headers[0] == 'HTTP/1.1  404 NOT FOUND' || 
	    	$file_headers[0] == 'HTTP/1.1 400 Bad Request'
	    ){
	    	throw new CardInfosException;
	    }

	    try{
	    	$binResults = file_get_contents($url);
	    } catch(Exception $e){
	    	throw new CardInfosException;
	    }

	    if (!$binResults)
	    	throw new CardInfosException;

	    return json_decode($binResults);
	}

	public function isEuroCard(){
		/* Corect Code */
		/*$infoCard = $this->getInfos();
		$alpha2 = $infoCard->country->alpha2;
		return in_array($alpha2, EURO_CURRENCIES);*/

		/** Mocked Code */
		// bin => isEuroCard
		$map = [
			"45717360" => true,
			"516793" => true,
			"45417360" => false,
			"41417360" => false,			
			"4745030" => false
		];

		return $map[$this->bin];
	}

	public function getCommissionRate(){
		return ($this->isEuroCard($this->bin) ? 0.01 : 0.02);
	}
}