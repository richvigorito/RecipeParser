<?php /* vim:set filetype=php tabstop=4 softtabstop=4 shiftwidth=4 noexpandtab smartindent: */

namespace rv\RecipeParser;


use rv\Lexer\LexicalScanner;
use rv\Lexer\ExpressionTree;

use rv\RecipeParser\Exceptions\MeasurementUnitNotFound;

class RecipeParser
{

  private $user_string ;
  private $parsed_string ;
  private $measurement_unit ;
  private $measurement_quantity ;
  private $whole_food_quantity;
  private $food ;
  private $grammar ;
  private $scanner ;
  private $precisely_entered; 
  private $multiplier;


 private $ignore_words = array('fresh','organic','natural','raw','regular','uncooked','grass-fed');


  public function __construct() 
  {

    $pathinfo = pathinfo(__FILE__);
    require $pathinfo['dirname'].DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.php";

	$this->is_precise = false;
    $this->grammar    = $defaults['grammar'];
    $this->scanner    = new LexicalScanner($defaults['grammar']);
    $this->multiplier = 1 ;
    $this->measurement_quantity = 1;
    $this->measurement_to_quantity = null;
  }
 
  
  public function prep($string) 
  {
    $string = str_replace("(", " ( ",$string);
    $string = str_replace(")", " ) ",$string);
    $string = str_replace(",", " , ",$string);
    $string = str_replace('"', ' " ',$string);
    $string = trim($string);

    $pattern =  "/(\d)(lb|mg|cg|dg|g|kg|ml|cl|dl|l|kl|oz|tbsp|tsp|ts|t|c|lg|sm|m)(\.|\w*)/i";
    $string =  trim(preg_replace($pattern,"$1 $2$3",$string));
   
    $pattern =  "/(\d)(\ )?(lb[s]+)(\.|\w*)/i";
    $string =  trim(preg_replace($pattern,"$1 $3",$string));
    
    $pattern =  "/(\d)(\ )?(pkg|package)(s)?(\.|\w*)/i";
    $string =  trim(preg_replace($pattern,"$1 $3",$string));

    $pattern =  "/(\d)(\ )+(teaspoon[s]+)(\.)(\w*)/i";
    $string =  trim(preg_replace($pattern,"$1 $3 $5",$string));

    $pattern =  "/(\d)(\ )+(tablespoon[s]+)(\.)(\w*)/i";
    $string =  trim(preg_replace($pattern,"$1 $3 $5",$string));
    $string =  trim(preg_replace("/^(.*)( of | a )(.*)$/i","$1 $3",$string));
    $string =  trim(preg_replace("/^(a )(.*)/i","$2",$string));
		// 2x is special, ie 2x cookies
    $string =  trim(preg_replace("/(\d)([abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ].*)/","$1 $2",$string));
    $string = self::convert_fractions($string);
    if( ! preg_match("/\d/", $string) > 0) 
      $string = "1 $string";
    $string = preg_replace('/(\ )+/',' ',$string);
    $string = str_replace(' . ', ' ',$string);
    return trim($string);
  }
 
  public function parse($string,$debug = null) 
  {
    $this->user_string = $string;

    $string = $this->prep($string);
    $error = false;

    $l = $this->scanner;
    $tree = $l::parse($string, $debug);

    $err  = $tree->getNode('ERROR');

    if ( false != $err) {
       $this->error($err);
       $error = true;
    } else {
		try {
			$this->expr($tree->getNode('T_TERM'));
		} catch (MeasurementUnitNotFound $e){
			$error = true;
			$this->error($tree);
			/* food should be set, but not be if error occured where we couldnt parse so remove [0-9] and roll w/ that */
			if(!isset($this->food)){
				$this->food = str_replace($e->getMessage(),'',$string);
				$this->food = preg_replace('/[0-9]+/', '', $this->food);
			}

		}
    }
 
	return $this->composeReturn($error);  
  }

  private function composeReturn($error = false)
  {
    if($error) $return['error']		= true;
    $return['user_string']	= $this->user_string;

    $return['food'] = trim($this->food);
    $return['food'] = str_replace(" , ", ", ",$return['food']);

    if(isset($this->measurement_quantity))   $return['measurement_quantity'] = $this->measurement_quantity;
    if(isset($this->measurement_unit))       
				$return['measurement_unit']     = $this->measurement_unit;

    if(isset($this->whole_food_quantity))    $return['whole_food_quantity']  = $this->whole_food_quantity;

    $this->parse_string =   $this->measurement_quantity  
                    . " " . $this->measurement_unit 
                    . " " . $return['food'];

    $this->parse_string = preg_replace('/(\ )+/',' ',$this->parse_string);
    $return['parse_string'] = $this->parse_string ;

    $this->parse_string = preg_replace('/(\ )+/',' ',$this->parse_string);
    $return['parse_string'] = $this->parse_string ;

    $return['is_precise']	= ($this->is_precise) ? 'true' : 'false';



	if( ($return['is_precise'] == 'false' ) and isset($this->fuzzy_measurement_unit)){
		if(isset($this->container))  {
			$this->fuzzy_parse_string = $this->fuzzy_quantity . ' '. $this->fuzzy_measurement_unit; 
			$this->fuzzy_parse_string = $this->fuzzy_parse_string . " " . $this->container ;
		} else {
			$this->fuzzy_parse_string = $this->multiplier . ' '. $this->fuzzy_measurement_unit; 
		}
	
		$this->fuzzy_parse_string .= " " . $return['food'];
	
                   
		$this->fuzzy_parse_string = preg_replace('/\s+/', ' ', $this->fuzzy_parse_string);
		$return['fuzzy_parse_string'] = $this->fuzzy_parse_string;
	}
		

	if($this->measurement_to_quantity){
		$return['is_precise']	= 'false';
		$return['measurement_to_quantity']= $this->measurement_to_quantity;
	}

	if(isset($this->copy_to_fuzzy) && $this->copy_to_fuzzy){
		$return['fuzzy_parse_string'] = $this->parse_string;
	}

    return json_encode($return);
  }

  private function expr(ExpressionTree $expr){
    $recipe_ingredient_mult = $expr->getNode('T_RECIPE_INGREDIENT_MULT');
    if ( $recipe_ingredient_mult != false) {
      $recipe_ingredient			= $recipe_ingredient_mult->getNode('T_RECIPE_INGREDIENT');
      $food				  = $recipe_ingredient_mult->getNode('T_FOOD');
    
      $multiplier       = $recipe_ingredient_mult->getNode('T_MULTIPLIER');
      $number         	= $recipe_ingredient_mult->getNode('T_NUMBER');
    	if ( $number != false) {
			$this->multiplier         *= $this->number($number);
    	} 
			if ( $multiplier != false) {
				$this->multiplier         *= $this->multiplier($multiplier);
			}

	if ( $recipe_ingredient != false) {
    		$this->recipe_ingredient($recipe_ingredient);
	} elseif ( $food != false) {
    		$this->food($food);
	} 

//print_R($this);
//print_R("----");
//exit;

    } else if (false !=   $backwards_recipe_ingredient  = $expr->getNode('T_BACKWARDS_RECIPE_INGREDIENT')){
		$this->recipe_ingredient($backwards_recipe_ingredient);
		$this->copy_to_fuzzy = 1;
    } else {
      $recipe_ingredient = $expr->getNode('T_RECIPE_INGREDIENT');
    	$this->recipe_ingredient($recipe_ingredient);
    }

    $this->measurement_quantity = ($this->measurement_quantity * $this->multiplier);
    // apply multipler
  }

  /**
   This is kind of a catch all, if input doesnt match a grammar, then we hope we can find 
   something find nodes that we can act on 
  */
  private function error(ExpressionTree $p)
  {
      	$recipe_ingredient_mult 	= $p->getNode('T_RECIPE_INGREDIENT_MULT');
		$recipe_ingredient			= $p->getNode('T_RECIPE_INGREDIENT');
    	$precise_measure			= $p->getNode('T_PRECISE_MEASURE');
    	$imprecise_measure			= $p->getNode('T_IMPRECISE_MEASURE');
		$food						= $p->getNode('T_FOOD');
    	$number						= $p->getNode('T_NUMBER');
   

      if 	( false != $recipe_ingredient_mult ) 	$this->recipe_ingredient_mult($recipe_ingredient_mult);
      elseif 	( false != $recipe_ingredient ) 	$this->recipe_ingredient($recipe_ingredient);
      elseif 	( false != $precise_measure ) 		$this->precise_measure($precise_measure);
      elseif 	( false != $imprecise_measure )		$this->imprecise_measure($imprecise_measure);
      elseif 	( false != $food)			$this->food($food);
      elseif 	( false != $number)			$this->number($number);
 
 }


  private function recipe_ingredient(ExpressionTree $ri){
    $food				= $ri->getNode('T_FOOD');
    $number				= $ri->getNode('T_NUMBER');

    $to_measurement		= $ri->getNode('T_OR_CONJUNCT');

    $precise_measure 	= $ri->getNode('T_PRECISE_MEASURE');
    $precise_unit		= $ri->getNode('T_PRECISE_UNIT');
    $imprecise_measure 	= $ri->getNode('T_IMPRECISE_MEASURE');
    
    $container_mult 	= $ri->getNode('T_CONTAINER_MULT');
    $container			= $ri->getNode('T_CONTAINER');
    $recipe_ingredient 	= $ri->getNode('T_RECIPE_INGREDIENT');

    if (  $recipe_ingredient != false &&  $container_mult != false) {
      $this->recipe_ingredient($recipe_ingredient);
      $this->container_mult($container_mult);
    } elseif (  $recipe_ingredient != false &&  $container != false) {
      $this->container($container);
      $this->recipe_ingredient($recipe_ingredient);
    } else if (  $precise_unit != false &&  $food != false && $number != false) {
      $this->precise_unit($precise_unit);
      $this->food($food);
      $this->number($number);
	  $this->is_precise = true;
	  $this->measurement_quantity = $this->number($number);
    } else if (  $precise_measure != false &&  $food != false) {
      $this->precise_measure($precise_measure);
      $this->food($food);
    } elseif ( false != $imprecise_measure  && false != $number && false != $food){
      $this->measurement_quantity = $this->number($number);
      $this->imprecise_measure($imprecise_measure);
      $this->food($food);
    } elseif ( false != $imprecise_measure  && false != $number){
      $this->measurement_quantity = $this->number($number);
      $this->imprecise_measure($imprecise_measure);
    } elseif ( false != $imprecise_measure  && false != $food){
      $this->imprecise_measure($imprecise_measure);
      $this->food($food);
    } elseif ( false != $number && false != $food){
      $this->food($food);
      $this->measurement_quantity = $this->number($number);
	 } elseif ( false != $container_mult && false != $food){
      $this->food($food);
      $this->container_mult($container_mult);
   } elseif ( false != $container && false != $food){
      $this->food($food);
      $this->container($container);
    } else {
      throw new Exception ('todo, figure error handling');
    } 

	

    if($to_measurement && $number){
		$this->measurement_to_quantity = $this->measurement_quantity;
		$this->measurement_quantity = $this->measurement_quantity;
	}
  }  


  private function recipe_ingredient_mult(ExpressionTree $ri){
    $food				= $ri->getNode('T_FOOD');
    $number				= $ri->getNode('T_NUMBER');
	$multiplier			= $ri->getNode('T_MULTIPLIER');
	$recipe_ingredient	= $ri->getNode('T_RECIPE_INGREDIENT');

	if ( false != $recipe_ingredient && false != $food){
      $this->food($food);
      $this->recipe_ingredient($recipe_ingredient);
	} elseif ( false != $number && false != $food && false != $multiplier){ 
	  $this->multiplier *= $this->multiplier($t->getNode('T_MULTIPLIER'));
      $this->food($food);
	  $this->multiplier *= $this->multiplier($t->getNode('T_MULTIPLIER'));
	  $this->multiplier *=  $this->number($number);
	} else {
      throw new Exception ('todo, figure error handling');
	}
  }

  private function precise_measure(ExpressionTree $pm)
  {
	  $this->is_precise = true;
      $precise_measure = $pm->getNode('T_PRECISE_MEASURE');
      if ( $precise_measure != false) {
			$this->precise_measure($precise_measure);
      } else {

      	$this->precise_unit($pm->getNode('T_PRECISE_UNIT'));
     
      	$number = $pm->getNode('T_NUMBER');

      	if ( $number != false) {
        	$this->measurement_quantity = $this->number($number);
      	}
      }
	  $imprecise_measure 	= $pm->getNode('T_IMPRECISE_MEASURE');
      if( false != $imprecise_measure )		$this->imprecise_measure($imprecise_measure);
  }

  private function container(ExpressionTree $t)
  { 
		if( ! $this->is_precise){
			$container = array_pop($t->arr);
			$this->container = $container;
			$this->$container();
		}
  }


  private function container_mult(ExpressionTree $t)
  {
	if(false != $t->getNode('T_NUMBER'))
		$this->multiplier *= $this->number($t->getNode('T_NUMBER'));
	if(false != $t->getNode('T_MULTIPLIER'))
		$this->multiplier *= $this->multiplier($t->getNode('T_MULTIPLIER'));
	
	$this->container($t->getNode('T_CONTAINER'));
  }
  

  private function imprecise_measure(ExpressionTree $im)
  {
      $number		= $im->getNode('T_NUMBER');
      $container	= $im->getNode('T_CONTAINER');

      if ( $number != false) {
        $this->measurement_quantity = $this->number($number);
      }
      $this->imprecise_unit($im->getNode('T_IMPRECISE_UNIT'));

      if ( $container != false) {
		$this->container($container);
      }
  }

  private function precise_unit(ExpressionTree $p)
  {
      $type = implode('',array_keys($p->arr[0]));
      $function = strtolower(substr($type,2));
	  if(!method_exists($this,$function)) 
			throw new MeasurementUnitNotFound($function);
      $this->$function($p->getNode($type));
  }

 private function imprecise_unit(ExpressionTree $p)
  {
      $type = implode('_',$p->arr);
      $type = preg_replace('/^(\ )?sm(\.)?$/i','small',$type);
      $type = preg_replace('/^(\ )?m(\.)?$/i','medium',$type);
      $type = preg_replace('/^(\ )?lg(\.)?$/i','large',$type);
      $function = strtolower($type);
	  if(!method_exists($this,$function)) {
			throw new MeasurementUnitNotFound($function,200);
	  }
      $this->$function();
  }



  private function cup(ExpressionTree $p = null)				{  $this->measurement_unit = 'cup';		}
  private function pint(ExpressionTree $p = null)				{  $this->measurement_unit = 'pt.';		}
  private function quart(ExpressionTree $p = null)				{  $this->measurement_unit = 'qt.';		}
  private function ounce(ExpressionTree $p = null)				{  $this->measurement_unit = 'oz.';		}
  private function gallon(ExpressionTree $p = null)				{  $this->measurement_unit = 'gal.';	}
  private function teaspoon(ExpressionTree $p = null)			{  $this->measurement_unit = 'tsp.';	}
  private function tablespoon(ExpressionTree $p = null)			{  $this->measurement_unit = 'tbsp.';   }
  private function pound(ExpressionTree $p = null)				{  $this->measurement_unit = 'lbs.';   }
  private function fluid_ounce(ExpressionTree $p = null)		{  $this->measurement_unit = 'fl. oz.'; }
  private function dessertspoon(ExpressionTree $p = null)		{  $this->measurement_unit = 'dsp.'; }

  private function liter(ExpressionTree $p)	
  {
    $input = strtolower($p->arr[0]); 
    switch ($input){
      case 'kl.':	case 'kl': case 'kls.':	case 'kls':  case 'kiloliter':   case 'kiloliters':
        $this->measurement_unit = 'kl.' ;
        break; 
      case 'ml.':  case 'ml':  case 'mls.':  case 'mls':  case 'milliliter': case 'milliliters':
        $this->measurement_unit = 'ml.' ;
        break;
      case 'dl.':  case 'dl':  case 'dls.':  case 'dls': case 'deciliter':   case 'deciliters':
        $this->measurement_unit = 'dl.' ;
        break;
      case 'cl.':  case 'cl':  case 'cls.':  case 'cls': case 'centiliter':  case 'centiliters':
        $this->measurement_unit = 'cl.' ;
        break;
      default:
        $this->measurement_unit = 'l.' ;
        break;
    }
  }

  private function gram(ExpressionTree $p)
  {
    $input = strtolower($p->arr[0]); 
    switch ($input){
      case 'kg.':   case 'kg':	case 'kgr': case 'kgr.': case 'kgrs': case 'kgrs': case 'kilogram': case 'kilograms':
        $this->measurement_unit = 'kg.' ;
        break;
      case 'mg.':   case 'mg':	case 'mgrs': case 'mgrs.': case 'mgr': case 'mgr.': case 'milligram': case 'milligrams':
        $this->measurement_unit = 'mg.' ;
        break;
      case 'dg.':   case 'dg':  case 'dgrs': case 'dgrs.':  case 'dgr': case 'dgr.': case 'decigram': case 'decigrams':
        $this->measurement_unit = 'dg.' ;
        break;
      case 'cg.':  case 'cg':  case 'cgrs': case 'cgrs.': case 'cgr': case 'cgr.': case 'centigram':   case 'centigrams':
        $this->measurement_unit = 'cg.' ;
        break;
      default:
        $this->measurement_unit = 'g.' ;
        break;
    }
    // ttake care of centi, deci, milli, kilo 
    //throw new Exception ('todo, figure error handling');
  }


  private function multiplier(ExpressionTree $p)
  {
			switch (strtolower($p->arr[0])){	
				case 'fifth':	case '.2x':
        	return .2;
				case 'quarter': case 'forth':	case '.25x':
        	return .25;
				case 'third':	case '.33x':
        	return .33;
				case 'half':	case '.5x':
        	return .5;
				case 'double': 	case '2x':
        	return 2;
				case 'triple': 	case '3x':
        	return 3;
				case 'quadruple': 	case '4x':
        	return 4;
			  default:
        return 1;
    	}

  }

  private function number(ExpressionTree $p)
  {
      $one  = $p->getNode('T_I_INSTEAD_1');
      $int  = $p->getNode('T_INTEGER');
      $dec  = $p->getNode('T_DECIMAL');

      if ( false != $int ) return $int->arr[0];
      if ( false != $dec ) return $dec->arr[0];
      if ( false != $one ) return 1;
      throw new Exception ('todo, figure error handling');
  }


  private function food(ExpressionTree $p)
  {
    foreach($p->arr as $k => $item){
      $key = implode(' ',array_keys($item));
      if($key == 'T_FOOD'){
        //$this->food($p->getNode('T_FOOD'));  
        $this->food($p->arr[$k][$key]);  
      } elseif($key == 'T_WORD'){
        //$this->word($p->getNode('T_WORD'));  
		$this->word($p->arr[$k][$key]);  
      } elseif($key == 'T_COMMA'){
        $this->word($p->arr[$k][$key]);  
      } elseif($key == 'T_DOUBLE_QUOTE'){
        $this->word($p->arr[$k][$key]);  
      } else {
        throw new Exception ('todo, figure error handling');
      }
    }
  }

  private function word(ExpressionTree $p)
  {
    if(false != $food = $p->getNode('T_HARD_CODED_FOOD')){
		$this->food .= ' '.implode(' ',$food->arr);
	} else {
		$food = ' '.implode(' ',array_reverse($p->arr));
		if(!in_array(strtolower(trim($food)),$this->ignore_words)){
			$this->food .= $food;
		}
	}
  }

  private function scant()
  {
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (.875 * $this->measurement_quantity);
  }

  private function tiny()			{$this->extra_small();}
  private function very_small()		{$this->extra_small();}
  private function extra_small()
  {
	$this->fuzzy_measurement_unit = 'x-sm.';
    $this->fuzzy_quantity			= $this->measurement_quantity;
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (.5 * $this->measurement_quantity);
  }

  private function little()			{$this->small();}
  private function small()
  {
	$this->fuzzy_measurement_unit = 'sm.';
    $this->fuzzy_quantity			= $this->measurement_quantity;

    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (.75 * $this->measurement_quantity);
  }

  private function medium() {
	$this->fuzzy_measurement_unit = 'md.';
    $this->fuzzy_quantity			= $this->measurement_quantity;
 }

  private function heaping()		{$this->heaped();}
  private function heaped()		
  {
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (1.125 * $this->measurement_quantity);
  }

  private function big() {$this->large();}
  private function large()
  {
	$this->fuzzy_measurement_unit = 'lg.';
    $this->fuzzy_quantity			= $this->measurement_quantity;
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (1.25 * $this->measurement_quantity);
  }

  // synonym to extra_large
  private function very_large() {$this->extra_large();}
  private function very_big()   {$this->extra_large();}
  private function really_big() {$this->extra_large();}
  private function extra_large()
  {			
	$this->fuzzy_measurement_unit = 'x-lg.';
    $this->fuzzy_quantity			= $this->measurement_quantity;
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (1.5 * $this->measurement_quantity);
  }

  // synonym to huge
  private function very_huge()		{$this->huge();}
  private function really_huge()	{$this->huge();}
  private function extra_huge()		{$this->huge();}
  private function huge()
  {			
	$this->fuzzy_measurement_unit = 'huge';
    $this->fuzzy_quantity			= $this->measurement_quantity;
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (1.5 * $this->measurement_quantity);
  }

	/* imprecise units */
  private function sprinkle() {   $this->teaspoon();		}
  private function sprinkles(){   $this->sprinkle();		}
  private function bite()	  {   $this->tablespoon();		}
  private function bites()	  {   $this->bite();		}
  private function sip()	  {   $this->mouthful();		$this->multiplier *= 0.5; }
  private function sips()	  {   $this->sip();				}
  private function gulp()	  {   $this->mouthful();		}
  private function gulps()	  {   $this->gulp();		}
  private function mouthful() {   $this->tablespoon();		}
  private function mouthfuls(){   $this->mouthful();		}
  private function dollop()	  {   $this->fluid_ounce();     }
  private function dollops()	  {   $this->dollop();     }
  private function knob()	  {   $this->fluid_ounce();		}
  private function knobs()	  {   $this->knob();		}
  private function smidgens() {   $this->smidgen();			}
  private function smidges()  {   $this->smidgen();			}
  private function smidge()	  {   $this->smidgen();			}
  private function smidgen()  {   $this->fluid_ounce();   $this->multiplier *= 0.00520833 ; }
  private function dash()	  {   $this->fluid_ounce();   $this->multiplier *= 0.0208333 ;	}
  private function dashes()	  {   $this->dash(); }
  private function handful()  {   $this->fluid_ounce();   $this->multiplier *= 2.67 ;		}
  private function handfuls() {   $this->handful(); }
  private function slice()    {   $this->ounce();	}
  private function slices()   {   $this->slice();	}
  private function rasher()    {   $this->ounce();	$this->multiplier *= 0.5; }
  private function rashers()   {   $this->rasher();	}


  private function can()	  {   $this->fluid_ounce();   $this->multiplier *= 15 ;			}
  private function cans()	  {   $this->can() ;		  }
  private function box()      {   $this->ounce();		  $this->multiplier *= 12.8 ;		}
  private function boxes()    {   $this->box();			  }
  private function jar()      {   $this->fluid_ounce();	  $this->multiplier *= 46 ;			}
  private function bag()      {   $this->pound();		  }
  private function bags()     {   $this->bag();		  }
  private function packet()   {   $this->ounce();		  $this->multiplier *= 2.5 ;	 }
  private function carton()   {   $this->fluid_ounce();	  $this->multiplier *= 16 ;	 }
  private function cartons()  {   $this->carton(); }
  private function glass()    {   $this->fluid_ounce();	  $this->multiplier *= 8 ;	 }
  private function glasses()  {   $this->glass(); }
  private function pot()      {   $this->fluid_ounce();	  $this->multiplier *= 20.2884 ;	 }
  private function pots()	  {   $this->pot(); }
  private function bottle()      {   $this->fluid_ounce();	  $this->multiplier *= 16 ;	 }
  private function bottles()	 {   $this->bottle(); }
  private function mug()      {   $this->fluid_ounce();	  $this->multiplier *= 12 ;	 }
  private function mugs()	  {   $this->mug(); }
  private function bowl()     {   $this->fluid_ounce();	  $this->multiplier *= 12 ;	 }
  private function bowls()    {   $this->bowl(); }
  private function flute()    {   $this->fluid_ounce();	  $this->multiplier *= 6 ;	 }
  private function flutes()   {   $this->flute() ; }
  private function scoop()    {   $this->fluid_ounce();	  $this->multiplier *= 3.19995 ;	 }
  private function scoops()   {   $this->scoop(); }
  private function jigger()   {   $this->fluid_ounce();	  $this->multiplier *= 1.5 ;	 }
  private function jiggers()  {   $this->jigger(); }
  private function shot()     {   $this->fluid_ounce();	  $this->multiplier *= 1.5 ;	 }
  private function shots()    {   $this->shot(); }
  private function drop()     {   $this->fluid_ounce();	  $this->multiplier *= 0.001690701 ;	 }
  private function drops()    {   $this->drop(); }
  
  private function pkg()  {   $this->package(); }
  private function pkgs()  {   $this->package(); }
  private function packages()  {   $this->package(); }
  private function package()  {   
	$this->fuzzy_measurement_unit	= 'pkg.';
    $this->fuzzy_quantity			= $this->measurement_quantity;
	$this->container				= '';
	/* dont do anything with conversion like you would w/ glass or bowl */ 
  }

  private function sachets()  {    $this->sachet('sachets'); }
  private function sachet($unit = 'sachet')  {   
	$this->fuzzy_measurement_unit	= $unit;
    $this->fuzzy_quantity			= $this->measurement_quantity;
	$this->container				= '';
	/* dont do anything with conversion like you would w/ glass or bowl */ 
  }

  /** Lifted right out of tummy. Was trying to get a litte tricker, and preg_replace fractions based on a regex and
     w/ their decimal value but this is fine and the preg_replace is a little more difficult than it sounds */
  private static function convert_fractions ($text) {

        // Replace all ¼ fractions with decimals to cleanse numbers for the db
        // Yes, the slashes in "1/8" are different than the slashes in "1/8" (Unicode) which is why they are repeated.
        $text = str_replace(array(           " 1/11", "-1/11", "1/11", " 1/11", "1/11", " 1⁄11", "1⁄11"), " .0909 ", $text);
        $text = str_replace(array(" ⅒ ","⅒ "," 1/10", "-1/10", "1/10", " 1/10", "1/10", " 1⁄10", "1⁄10"), " .1 ", $text);
        $text = str_replace(array(" ⅑ ","⅑ "," 1/9", "-1/9", "1/9", " 1/9", "1/9", " 1⁄9", "1⁄9"), " .111 ", $text);
        $text = str_replace(array(" ⅛","⅛",  " 1/8", "-1/8", "1/8", " 1/8", "1/8", " 1⁄8", "1⁄8"), " .125 ", $text);
        $text = str_replace(array(" ⅐","⅐ ", " 1/7", "-1/7", "1/7", " 1/7", "1/7", " 1⁄7", "1⁄7"), " .1428 ", $text);
        $text = str_replace(array(" ¼","¼",  " 1/4", "-1/4", "1/4", " 1/4", "1/4", " 1⁄4", "1⁄4"), " .25 ",  $text);
        $text = str_replace(array(" ⅕","⅕",  " 1/5", "-1/5", "1/5", " 1/5", "1/5", " 1⁄5", "1⁄5"), " .20 ",  $text);
        $text = str_replace(array(" ⅙","⅙",  " 1/6", "-1/6", "1/6", " 1/6", "1/6", " 1⁄6", "1⁄6"), " .166 ", $text);
        $text = str_replace(array(" ⅓", "⅓", " 1/3", "-1/3", "1/3", " 1/3", "1/3", " 1⁄3", "1⁄3"), " .33 ",  $text);
        $text = str_replace(array(" ⅜", "⅜", " 3/8", "-3/8", "3/8", " 3/8", "3/8", " 3⁄8", "3⁄8"), " .375 ", $text);
        $text = str_replace(array(" ⅖", "⅖", " 2/5", "-2/5", "2/5", " 2/5", "2/5", " 2⁄5", "2⁄5"), " .40 ",  $text);
        $text = str_replace(array(" ½", "½", " 1/2", "-1/2", "1/2", " 1/2", "1/2", " 1⁄2", "1⁄2"), " .5 ",   $text);
        $text = str_replace(array(" ⅗", "⅗", " 3/5", "-3/5", "3/5", " 3/5", "3/5", " 3⁄5", "3⁄5"), " .60 ",  $text);
        $text = str_replace(array(" ⅝", "⅝", " 5/8", "-5/8", "5/8", " 5/8", "5/8", " 5⁄8", "5⁄8"), " .625 ", $text);
        $text = str_replace(array(" ⅔", "⅔", " 2/3", "-2/3", "2/3", " 2/3", "2/3", " 2⁄3", "2⁄3"), " .67 ",  $text);
        $text = str_replace(array(" ¾", "¾", " 3/4", "-3/4", "3/4", " 3/4", "3/4", " 3⁄4", "3⁄4"), " .75 ",  $text);
        $text = str_replace(array(" ⅘", "⅘", " 4/5", "-4/5", "4/5", " 4/5", "4/5", " 4⁄5", "4⁄5"), " .80 ",  $text);
        $text = str_replace(array(" ⅞", "⅞", " 7/8", "-7/8", "7/8", " 7/8", "7/8", " 7⁄8", "7⁄8"), " .875 ", $text);
        $text = str_replace("* ", "", $text);

        // @todo - the above is causing issues with decimal replacement 1 1/2 is become 1.5, but "cheese in 1/2 inch cubes" is now "cheese in.5 inch cubes"
        // @todo should be looking for {number .number} and removing that space

        return $text;
    }


}
