<?php

require(__DIR__.'../../config/config.php');

class TransactionsTest extends \PHPUnit\Framework\TestCase{
	
	public function testInstanceOfClass(){
		$transactions = new App\Transactions;
		$this->assertInstanceOf(App\Transactions::class, $transactions);
	}

	public function testInstanceWithFile(){
		$file = "input.txt";
		$transactions = new App\Transactions($file);
		$this->assertInstanceOf(App\Transactions::class, $transactions);
	}

	public function testSetAndGetFile(){
		$file = "input.txt";
		$transactions = new App\Transactions;
		$transactions->setFile($file);
		$this->assertEquals($transactions->getFile(), $file);
	}

	/** Working with File Tests **/
	public function testNullFileExceptionWhenFileNotSetted(){
		$this->expectException(\App\Exceptions\NoFileException::class);
		$transactions = new App\Transactions;
		$transactions->readTransactions();
	}

	public function testNullFileExceptionWhenFileSettedAsNull(){
		$this->expectException(\App\Exceptions\NoFileException::class);
		$transactions = new App\Transactions;
		$transactions->setFile(null);
		$transactions->readTransactions();
	}

	public function testFileNotExistException(){
		$this->expectException(\App\Exceptions\FileNotExistException::class);
		$transactions = new App\Transactions("testNotExist.txt");
		$transactions->readTransactions();
	}

	/** Reusable Instance **/

	protected function getInstanceWithFile($file){
		$transactions = new App\Transactions($file);
		return $transactions;
	}

	public function testFileReadeFiledNumberOfItems(){

		$transactions = $this->getInstanceWithFile(dirname(__FILE__)."/inputs/testInputNumber.txt");
		
		$this->assertEmpty($transactions->getTransactions());
		$transactions->readTransactions();
		$this->assertCount(5, $transactions->getTransactions());
	}

	public function testFileReadeFiledFormatItems(){
		$this->expectException(\App\Exceptions\TransactionFieldsException::class);
		$transactions = $this->getInstanceWithFile(dirname(__FILE__)."/inputs/testObjectFormatException.txt");
		$transactions->readTransactions();
	}

	/** Card **/
	public function testGetCardInstanceAndGetter(){
		$card = new \App\Card(45717360);
		$this->assertInstanceOf(App\Card::class, $card);
		$this->assertEquals(45717360, $card->getBin());
	}

	public function testGetCardInfoBadBinException(){
		$card = new \App\Card(00000);
		$this->expectException(\App\Exceptions\CardInfosException::class);
		$card->getInfos();
	}
	
	/*
	public function testGetCardInfoCountry(){
		//{"bin":"45717360","amount":"100.00","currency":"EUR"}
		$card = new \App\Card(45717360);
		$infos = $card->getInfos();
		$this->assertEquals("DK", $infos->country->alpha2);
	}*/

	public function testIfCardInEuroCurrencies(){
		//{"bin":"45717360","amount":"100.00","currency":"EUR"}
		$card = new \App\Card(45717360);
		$this->assertTrue($card->isEuroCard());
	}

	public function testIfCardInEuroCurrenciesWithFailedTest(){
		//{"bin":"4745030","amount":"2000.00","currency":"GBP"}
		$card = new \App\Card(4745030);
		$this->assertFalse($card->isEuroCard());
	}

	public function testCommissionRate(){

		$euroCard = new App\Card(45717360);
		//	{"bin":"45717360","amount":"100.00","currency":"EUR"}
		$this->assertEquals(0.01, $euroCard->getCommissionRate());


		$nonEuroCard = new App\Card(4745030);
		//{"bin":"4745030","amount":"2000.00","currency":"GBP"}

		$this->assertEquals(0.02, $nonEuroCard->getCommissionRate());
	}

	/**** Rate Excange ***/
	/*public function testApiRatesException(){
		$transactions = new App\Transactions;
		$this->expectException(\App\Exceptions\RatesAcessKeyException::class);
		$transactions->getRate("EUR");
	}*/

	public function testApiRatesMocked(){
		$mockedResult = [
			"rates" => [
				"EUR" => 3
			]
		];

		$transactions = $this->getMockBuilder(App\Transactions::class)
			->setMethods(['getRatesFromApi'])->getMock();
		$transactions->method('getRatesFromApi')->willReturn(json_encode($mockedResult));

		$rate = $transactions->getRate("EUR");
		$this->assertEquals(3, $rate);
	}

	// fixed Amount
	public function testGetFixedAmout(){
		$transactions = new App\Transactions;

		//	getFixedAmount($currency, $rate, $amount)
		$this->assertEquals(1000, $transactions->getFixedAmount("EUR", 0, 1000));

		$this->assertEquals(100, $transactions->getFixedAmount("EUR", 10, 1000));
		$this->assertEquals(100, $transactions->getFixedAmount("DZ", 10, 1000));
	}

	

	/**** Test All function **/

	public function testHandleOneItemInFile(){
		$mockedResult = [
			"rates" => [
				"EUR" => 0
			]
		];

		$transactions = $this->getMockBuilder(App\Transactions::class)
			->setMethods(['getRatesFromApi'])->getMock();
		$transactions->method('getRatesFromApi')->willReturn(json_encode($mockedResult));
		$transactions->setFile(dirname(__FILE__)."/inputs/testOneItem.txt");
		//$this->expectOutputString("1");
		$result = $transactions->handle();
		$this->assertCount(1, $result);
		$this->assertEquals($result[0], 1);

	}

	public function testHandle(){
		$mockedRatesResult = [
			"rates" => [
				"EUR" => 0,
				"USD" => 1.0827,
				"JPY" => 120.67,
				"GBP" => 0.91503
			]
		];

		$expectResult = [
			1,
    		0.46,
    		1.66,
    		2.4,
    		43.71,
		];

		$transactions = $this->getMockBuilder(App\Transactions::class)
			->setMethods(['getRatesFromApi'])->getMock();
		$transactions->method('getRatesFromApi')->willReturn(json_encode($mockedRatesResult));
		$transactions->setFile(dirname(__FILE__)."/../input.txt");
		//$this->expectOutputString("1");
		$result = $transactions->handle();
		$this->assertCount(5, $result);
		$this->assertEquals($expectResult, $result);

	}


}