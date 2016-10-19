<?php

namespace rv\RecipeParser;


use rv\Lexer\LexicalScanner;
use rv\Lexer\ExpressionTree;


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
  private $multiplier;

  public function __construct($grammar) 
  {
    $this->grammar  = $grammar;
    $this->scanner    = new LexicalScanner($grammar);
    $this->multiplier = 1 ;
    $this->measurement_quantity = 1;
  }
 
  
  public function prep($string) 
  {
    $string = str_replace("(", " ( ",$string);
    $string = str_replace(")", " ) ",$string);
    $string = trim($string);
    $pattern =  "/(\d)(mg|cg|dg|g|kg|ml|cl|dl|l|kl|oz|tbsp|tsp|ts|t|c|lg|sm|m)(\.|\w*)/i";
    $string =  trim(preg_replace($pattern,"$1 $2",$string));
    
    $string =  trim(preg_replace("/^(.*)( of | a )(.*)$/i","$1 $3",$string));
    $string =  trim(preg_replace("/^(a )(.*)/i","$2",$string));
    $string = self::convert_fractions($string);
    if( ! preg_match("/\d/", $string) > 0) 
      $string = "1 $string";
    $string = preg_replace('/(\ )+/',' ',$string);
    return trim($string);
  }
 
  public function parse($string) 
  {
    $return = array();
    $return['user_string'] = $this->user_string = $string;

    $string = $this->prep($string);

    $l = $this->scanner;
    $tree = $l::parse($string);

    $this->expr($tree->getNode('T_TERM'));
  
    $return['food'] = trim($this->food);

    if(isset($this->measurement_quantity))   $return['measurement_quantity'] = $this->measurement_quantity;
    if(isset($this->measurement_unit))       $return['measurement_unit']     = $this->measurement_unit;
    if(isset($this->whole_food_quantity))    $return['whole_food_quantity']  = $this->whole_food_quantity;

    $this->parse_string =   $this->measurement_quantity  
                    . " " . $this->measurement_unit 
                    . " " . $return['food'];

    $this->parse_string = preg_replace('/(\ )+/',' ',$this->parse_string);
    $return['parse_string'] = $this->parse_string ;
    return json_encode($return);
  }

  private function expr(ExpressionTree $expr){
    $recipe_ingredient_mult = $expr->getNode('T_RECIPE_INGREDIENT_MULT');
    if ( $recipe_ingredient_mult != false) {
      $recipe_ingredient  = $recipe_ingredient_mult->getNode('T_RECIPE_INGREDIENT');
      $this->multiplier         *= $this->number($recipe_ingredient_mult->getNode('T_NUMBER'));
    } else {
      $recipe_ingredient = $expr->getNode('T_RECIPE_INGREDIENT');
    }

    $this->recipe_ingredient($recipe_ingredient);
    $this->measurement_quantity = ($this->measurement_quantity * $this->multiplier);
    // apply multipler
  }


  private function recipe_ingredient(ExpressionTree $ri){
    $food 		= $ri->getNode('T_FOOD');
    $number 		= $ri->getNode('T_NUMBER');
    $precise_measure 	= $ri->getNode('T_PRECISE_MEASURE');
    $imprecise_measure 	= $ri->getNode('T_IMPRECISE_MEASURE');
    
    $container_mult 	= $ri->getNode('T_CONTAINER_MULT');
    $container 		= $ri->getNode('T_CONTAINER');
    $recipe_ingredient 	= $ri->getNode('T_RECIPE_INGREDIENT');


    if (  $recipe_ingredient != false &&  $container_mult != false) {
      $this->container_mult($container_mult);
      $this->recipe_ingredient($recipe_ingredient);
    } elseif (  $recipe_ingredient != false &&  $container != false) {
      $this->container($container);
      $this->recipe_ingredient($recipe_ingredient);
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
    } else {
      throw new Exception ('todo, figure error handling');
    } 
  }  


  private function precise_measure(ExpressionTree $pm)
  {
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
  }

  private function container(ExpressionTree $t)
  {  /* do nothing, we have a precise measurement */ }
  private function container_mult(ExpressionTree $t)
  { /* do nothing, we have a precise measurement */  }
  

  private function imprecise_measure(ExpressionTree $im)
  {
      $number = $im->getNode('T_NUMBER');

      if ( $number != false) {
        $this->measurement_quantity = $this->number($number);
      }

      $this->imprecise_unit($im->getNode('T_IMPRECISE_UNIT'));
  }

  private function precise_unit(ExpressionTree $p)
  {
      $type = implode('',array_keys($p->arr[0]));
      $function = strtolower(substr($type,2));
      $this->$function($p->getNode($type));
  }

 private function imprecise_unit(ExpressionTree $p)
  {
      $type = implode('_',$p->arr);
      $type = preg_replace('/^(\ )?sm(\.)?$/i','small',$type);
      $type = preg_replace('/^(\ )?m(\.)?$/i','medium',$type);
      $type = preg_replace('/^(\ )?lg(\.)?$/i','large',$type);
      $function = strtolower($type);
      $this->$function();
  }



  private function pint(ExpressionTree $p)
  {
    $this->measurement_unit = 'pt.'; 
  }

  private function quart(ExpressionTree $p)
  {
    $this->measurement_unit = 'qt.'; 
  }

  private function gallon(ExpressionTree $p)
  {
    $this->measurement_unit = 'gal.'; 
  }

  private function teaspoon(ExpressionTree $p)
  {
    $this->measurement_unit = 'tsp.'; 
  }

  private function tablespoon(ExpressionTree $p)
  {
    $this->measurement_unit = 'tbsp.'; 
  }

  private function ounce(ExpressionTree $p)
  {
    $this->measurement_unit = 'oz.'; 
  }

  private function cup(ExpressionTree $p)
  {
    $this->measurement_unit = 'cup'; 
  }

  private function liter(ExpressionTree $p)
  {
    $input = strtolower($p->arr[0]); 
    switch ($input){
      case 'kl.':
      case 'kl':
      case 'kiloliter':
      case 'kiloliters':
        $this->measurement_unit = 'kl.' ;
        break;
      case 'ml.':
      case 'ml':
      case 'milliliter':
      case 'milliliters':
        $this->measurement_unit = 'ml.' ;
        break;
      case 'dl.':
      case 'dl':
      case 'deciliter':
      case 'deciliters':
        $this->measurement_unit = 'dl.' ;
        break;
      case 'cl.':
      case 'cl':
      case 'centiliter':
      case 'centiliters':
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
      case 'kg.':
      case 'kg':
      case 'kilogram':
      case 'kilograms':
        $this->measurement_unit = 'kg.' ;
        break;
      case 'mg.':
      case 'mg':
      case 'milligram':
      case 'milligrams':
        $this->measurement_unit = 'mg.' ;
        break;
      case 'dg.':
      case 'dg':
      case 'decigram':
      case 'decigrams':
        $this->measurement_unit = 'dg.' ;
        break;
      case 'cg.':
      case 'cg':
      case 'centigram':
      case 'centigrams':
        $this->measurement_unit = 'cg.' ;
        break;
      default:
        $this->measurement_unit = 'g.' ;
        break;
    }
    // ttake care of centi, deci, milli, kilo 
    //throw new Exception ('todo, figure error handling');
  }

  private function number(ExpressionTree $p)
  {
      $int  = $p->getNode('T_INTEGER');
      $dec  = $p->getNode('T_DECIMAL');

      if ( false != $int ) return $int->arr[0];
      if ( false != $dec ) return $dec->arr[0];
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
      } else {
        throw new Exception ('todo, figure error handling');
      }
    }
  }

  private function word(ExpressionTree $p)
  {
    $this->food .= ' '.implode(' ',array_reverse($p->arr));
  }


  private function extra_small()
  {
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (.25 * $this->measurement_quantity);
  }

  // synonym to very_small
  private function very_small() {$this->extra_small();}
  private function small()
  {
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (.5 * $this->measurement_quantity);
  }

  private function medium() { /*  times by one, ie dont do shit  */ }

  private function big() {$this->large();}
  private function large()
  {
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (1.25 * $this->measurement_quantity);
  }

  // synonym to extra_large
  private function very_large() {$this->extra_large();}
  private function very_big()   {$this->extra_large();}
  private function really_big() {$this->extra_large();}
  private function extra_large()
  {
    if(!empty($this->measurement_quantity)) 
      $this->measurement_quantity = (1.5 * $this->measurement_quantity);
  }


  /** Lifted right out of tummy. Was trying to get a litte tricker, and preg_replace fractions based on a regex and
     w/ their decimal value but this is fine and the preg_replace is a little more difficult than it sounds */
  private static function convert_fractions ($text) {

        // Replace all ¼ fractions with decimals to cleanse numbers for the db
        // Yes, the slashes in "1/8" are different than the slashes in "1/8" (Unicode) which is why they are repeated.
        $text = str_replace(array(" ⅛", "⅛", " 1/8", "-1/8", "1/8", " 1/8", "1/8", " 1⁄8", "1⁄8"), " .125 ", $text);
        $text = str_replace(array(" ¼", "¼", " 1/4", "-1/4", "1/4", " 1/4", "1/4", " 1⁄4", "1⁄4"), " .25 ",  $text);
        $text = str_replace(array(" ⅕", "⅕", " 1/5", "-1/5", "1/5", " 1/5", "1/5", " 1⁄5", "1⁄5"), " .20 ",  $text);
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
