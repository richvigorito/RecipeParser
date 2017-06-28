<?php


namespace rv\RecipeParser\Exceptions;

/**
 * Define a custom exception class
 */
class MeasurementUnitNotFound extends \Exception
{
		
		const PRECISE 	= 100 ;
		const IMPRECISE = 200 ;
		
    public function __construct($message = null, $code = 100, Exception $previous = null) 		
		{
        if(!$message){
					switch ($code){
						case 100:
							$message = "Precise measure not found";
							break;
						case 200:
							$message = "Imprecise measure not found";
							break;
					}
				}
    
        parent::__construct($message, $code, $previous);
    }

		public function isPrecise(){
			return ($code == self::PRECISE);
		}

		public function isImprecise(){
			return ($code == self::IMPRECISE);
		}

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
