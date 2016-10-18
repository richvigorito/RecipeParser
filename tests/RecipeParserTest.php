<?php


use rv\RecipeParser\RecipeParser;


class RecipeParserTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider lexemeMatches
     * @ dataProvider lexemeMatches1_off_Matches_to_test_specific
     */
    public function testRecipieMatching($user_string,$food,$measurement_quantity,$measurement_unit,$parse_string)
    {
      include __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.php";

      $parser = new RecipeParser($defaults['grammar']);
      $json = $parser->parse($user_string);
      $array = array(); 
      $array['user_string']           = $user_string;
      $array['food']                  = trim($food);
      $array['measurement_quantity']  = (float)$measurement_quantity;
      if (!empty($measurement_unit)){
        $array['measurement_unit']  = $measurement_unit;
      }
      $array['parse_string']          = $parse_string;  
     
      $json_assert = json_encode($array);
      $this->assertEquals($json,$json_assert);
    }

    /**
     */
    public function lexemeMatches()
    {
        return array(
          // array(<user_string>, <food>, <measurement_quantity>, <measurement_unit>,  <parsed_string>),
          array('3 tablespoons sugar', 'sugar', '3', 'tbsp.', '3 tbsp. sugar'),
          array("76 oz steak"," steak",76,"oz.","76 oz. steak"),
          array("7 6 ozs. steaks"," steaks",42,"oz.","42 oz. steaks"),
          array("2 tablespoon garlic"," garlic",2,"tbsp.","2 tbsp. garlic"),
          array("2 tbsp garlic"," garlic",2,"tbsp.","2 tbsp. garlic"),
          array("50 oz sword fish"," sword fish",50,"oz.","50 oz. sword fish"),
          array("50 OUNcEs sword fish"," sword fish",50,"oz.","50 oz. sword fish"),
          array("2 small cokes"," cokes",1,"","1 cokes"),
          array(" medium coke"," coke",1,"","1 coke"),
          array("2 large cokes"," cokes",2.5,"","2.5 cokes"),
          array("2 22.5 oz beers"," beers",45,"oz.","45 oz. beers"),
          array("2 22.5 oz tasty beers"," tasty beers",45,"oz.","45 oz. tasty beers"),
          array("2 22.5 oz sweet delicious beers"," sweet delicious beers",45,"oz.","45 oz. sweet delicious beers"),
          array("large coke"," coke",1.25,"","1.25 coke"),
          array("extra large coke"," coke",1.5,"","1.5 coke"),
          array("sm. soda"," soda",0.5,"","0.5 soda"),
          array("really big chocolate chip cookie"," chocolate chip cookie",1.5,"","1.5 chocolate chip cookie"),
          array("2 really big chocolate chip cookie"," chocolate chip cookie",3,"","3 chocolate chip cookie"),
          array("5 gram salt"," salt",5,"g.","5 g. salt"),
          array("5 grams salt"," salt",5,"g.","5 g. salt"),
          array("5.345 kilogram butter"," butter",5.345,"kg.","5.345 kg. butter"),
          array("5 kg water"," water",5,"kg.","5 kg. water"),
          array("5 liter water"," water",5,"l.","5 l. water"),
          array("5 liters water"," water",5,"l.","5 l. water"),
          array("5 kl water"," water",5,"kl.","5 kl. water"),
          array("5 kl. water"," water",5,"kl.","5 kl. water"),
          array("5 kiloliter water"," water",5,"kl.","5 kl. water"),
          array("5 kiloliters water"," water",5,"kl.","5 kl. water"),
          array("50 OunCes water"," water",50,"oz.","50 oz. water"),
          array("50 g pepper"," pepper",50,"g.","50 g. pepper"),
          array(" 50 teaspoon pepper "," pepper",50,"tsp.","50 tsp. pepper"),
          array(" 50 tsp. pepper "," pepper",50,"tsp.","50 tsp. pepper"),
          array("2 grams rice"," rice",2,"g.","2 g. rice"),
          array("2 large pumpkin crispbreads"," pumpkin crispbreads",2.5,"","2.5 pumpkin crispbreads"),
          array("1.5 c bone broth"," bone broth",1.5,"cup","1.5 cup bone broth"),
          array("7 g. butter"," butter",7,"g.","7 g. butter"),
          array("1 tbsp frozen butter"," frozen butter",1,"tbsp.","1 tbsp. frozen butter"),
          array("garlic 2 tablespoon"," garlic",2,"tbsp.","2 tbsp. garlic"),
          array("garlic salt 2 teaspoons"," garlic salt",2,"tsp.","2 tsp. garlic salt"),
          array("10 oz of almond milk"," almond milk",10,"oz.","10 oz. almond milk"),
          array("2 corn tortillas"," corn tortillas",2,"","2 corn tortillas"),
          array("1 greek yogurt"," greek yogurt",1,"","1 greek yogurt"),
          array("2 mcdonalds cheeseburgers"," mcdonalds cheeseburgers",2,"","2 mcdonalds cheeseburgers"),
          array("chocolate chip cookies"," chocolate chip cookies",1,"","1 chocolate chip cookies"),
          array("2 cups rice"," rice",2,"cup","2 cup rice"),
          array("garlic salt 2 teaspoons"," garlic salt",2,"tsp.","2 tsp. garlic salt"),
          array("5.4tsp tuna fish"," tuna fish",5.4,"tsp.","5.4 tsp. tuna fish"),
          array("50tbsp tuna fish"," tuna fish",50,"tbsp.","50 tbsp. tuna fish"),
          array("50mg pepper"," pepper",50,"mg.","50 mg. pepper"),
          array("50mg. pepper"," pepper",50,"mg.","50 mg. pepper"),
          array("50g pepper"," pepper",50,"g.","50 g. pepper"),
          array("50 teaspoons pepper"," pepper",50,"tsp.","50 tsp. pepper"),
          array("50 tsp pepper"," pepper",50,"tsp.","50 tsp. pepper"),
          array("1 tbsp almond butter"," almond butter",1,"tbsp.","1 tbsp. almond butter"),
          array("200ml full fat milk"," full fat milk",200,"ml.","200 ml. full fat milk"),
          array("1 tsp spread"," spread",1,"tsp.","1 tsp. spread"),
          array("150g smoked mackeral"," smoked mackeral",150,"g.","150 g. smoked mackeral"),
          array("160g broccoli"," broccoli",160,"g.","160 g. broccoli"),
          array("15g sesame seeds"," sesame seeds",15,"g.","15 g. sesame seeds"),
          array("1tbsp rapeseed oil"," rapeseed oil",1,"tbsp.","1 tbsp. rapeseed oil"),
          array("10g almonds"," almonds",10,"g.","10 g. almonds"),
          array("50g peas"," peas",50,"g.","50 g. peas"),
          array(".5 tsp mustard"," mustard",0.5,"tsp.","0.5 tsp. mustard"),
          array("25g quinoa"," quinoa",25,"g.","25 g. quinoa"),
          array("50g banana"," banana",50,"g.","50 g. banana"),
          array("100g greek yogurt"," greek yogurt",100,"g.","100 g. greek yogurt"),
          array("1tsp honey"," honey",1,"tsp.","1 tsp. honey"),
          array("Frozen berries .5 cup "," Frozen berries",0.5,"cup","0.5 cup Frozen berries"),
          array(" 0.5 c broccoli "," broccoli",0.5,"cup","0.5 cup broccoli"),
          array("8oz calli tea"," calli tea",8,"oz.","8 oz. calli tea"),
          array("2 tsp. honey mustard salad dressing"," honey mustard salad dressing",2,"tsp.","2 tsp. honey mustard salad dressing"),
          array("1/2 mango"," mango",0.5,"","0.5 mango"),
          array("1t honey"," honey",1,"tsp.","1 tsp. honey"),
          array("1 table spoon honey"," honey",1,"tbsp.","1 tbsp. honey"),
          array("11 table spoons honey"," honey",11,"tbsp.","11 tbsp. honey"),
          array("11 tblspoons honey"," honey",11,"tbsp.","11 tbsp. honey"),
          array("11 tblspn honey"," honey",11,"tbsp.","11 tbsp. honey"),
          array("6 spoonfuls honey"," honey",6,"tbsp.","6 tbsp. honey"),
          array("6 tb honey"," honey",6,"tbsp.","6 tbsp. honey"),
          array("6 tbl honey","honey",6,"tbsp.","6 tbsp. honey"),
          array("6 tbs honey"," honey",6,"tbsp.","6 tbsp. honey"),
          array("6 tbls honey"," honey",6,"tbsp.","6 tbsp. honey"),
          array("6 t sugar"," sugar",6,"tsp.","6 tsp. sugar"),
          array("6 ts azucar"," azucar",6,"tsp.","6 tsp. azucar"),
          array("6 tea spoon azucar"," azucar",6,"tsp.","6 tsp. azucar"),
          array("65 tea spoons azucar"," azucar",65,"tsp.","65 tsp. azucar"),
          array("65 tea. azucar"," azucar",65,"tsp.","65 tsp. azucar"),
          array("65 teas. azucar"," azucar",65,"tsp.","65 tsp. azucar"),
          array(".5 cup of frozen berries"," frozen berries",0.5,"cup","0.5 cup frozen berries"),
          array("1/4 cup salsa"," salsa",0.25,"cup","0.25 cup salsa"),
          array("6 tb amys refried beans"," amys refried beans",6,"tbsp.","6 tbsp. amys refried beans"),
          array("diced green chilies 3 tb"," diced green chilies",3,"tbsp.","3 tbsp. diced green chilies"),
          array("shredded cheddar cheese 1/3 c"," shredded cheddar cheese",0.33,"cup","0.33 cup shredded cheddar cheese"),
          array("wheat thins"," wheat thins",1,"","1 wheat thins"),
          array("15 golden raisin"," golden raisin",15,"","15 golden raisin"),
          array("1/8 c roast chicken"," roast chicken",0.125,"cup","0.125 cup roast chicken"),
          array("lemon juice 1tbsp "," lemon juice",1,"tbsp.","1 tbsp. lemon juice"),
          array("60 g. bread with a heavily seeded crust"," bread with heavily seeded crust",60,"g.","60 g. bread with heavily seeded crust"),
          array("hot and sour soup"," hot and sour soup",1,"","1 hot and sour soup"),
          array("1/2 red pepper"," red pepper",0.5,"","0.5 red pepper"),
	  array("1 can 14.5 oz. cream of mushroom soup", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup"), 
	  array("1 can (14.5 ounCes) cream of mushroom soup", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup"), 
	  array(" (14.5 oz.) bacon", "bacon", 14.5,"oz.","14.5 oz. bacon"), 
        ); 
    }

    public function lexemeMatches1_off_Matches_to_test_specific(){
	return array(
  		array("1 can (14.5 oz.) cream of mushroom soup", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup"), 
  		array(" (14.5 oz.) bacon", "bacon", 14.5,"oz.","14.5 oz. bacon"), 
        ); 
    }
}

