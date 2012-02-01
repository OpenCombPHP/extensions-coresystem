<?php
namespace org\opencomb\coresystem\util ;

use org\jecat\framework\lang\Exception ;

class RandomIdCreator
{
	const MIN_RANGE_COUNT = 2 ;
	const MIN_MINLENGTH = 3 ;
	const MAX_MAXLENGTH = 20 ;
	
	public function __construct ( array $arrRange , $nMinLength = 3 , $nMaxLength = 10 ){
		$this->setRange($arrRange);
		$this->setMinLength($nMinLength);
		$this->setMaxLength($nMaxLength);
		srand($this->make_seed());
	}
	
	public function setRange(array $arrRange){
		$nRangeCount = count($arrRange) ;
		if($nRangeCount < self::MIN_RANGE_COUNT ){
			throw new Exception(
				'`%s` : count of arrRange can not be less then `%d` , count is `%d`',
				array(
					__METHOD__,
					self::MIN_RANGE_COUNT,
					$nRangeCount
				)
			);
		}else{
			$this->arrRange = $arrRange ;
		}
	}
	
	public function setMinLength($nMinLength){
		if($nMinLength < self::MIN_MINLENGTH){
			throw new Exception(
				'`%s` : nMinLength can not be smaller then `%d` , nMinLength is `%d`',
				array(
					__METHOD__,
					self::MIN_MINLENGTH,
					$nMinLength
				)
			);
		}else{
			$this->nMinLength = $nMinLength ;
		}
	}
	
	public function setMaxLength($nMaxLength){
		if($nMaxLength > self::MAX_MAXLENGTH){
			throw new Exception(
				'`%s` : nMaxLength can not be bigger then `%d` , nMaxLength is `%d`',
				array(
					__METHOD__,
					self::MAX_MAXLENGTH,
					$nMaxLength
				)
			);
		}else if( $nMaxLength < $this->nMinLength ){
			throw new Exception(
				'`%s` : nMaxLength can not be smaller then nMinLength , nMaxLength is `%d` and nMinLength is `%d`',
				array(
					__METHOD__,
					$nMaxLength,
					$this->nMinLength
				)
			);
		}else{
			$this->nMaxLength = $nMaxLength ;
		}
	}
	
	public function create(){
		$sRet = '';
		while( strlen($sRet) < $this->nMinLength ){
			$nRand = rand(0 , count($this->arrRange)-1 );
			$sRand = $this->arrRange[$nRand];
			$sRet .= $sRand ;
		}
		return $sRet ;
	}
	
	function make_seed()
	{
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
	
	private $arrRange = array() ;
	private $nMinLength = 3 ;
	private $nMaxLength = 10 ;
}
