<?php


use rv\RecipeParser\RecipeParser;


class RecipeParserTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider lexemeMatches
     * @ dataProvider lexemeMatches1_off_Matches_to_test_specific
     */
    public function testRecipieMatching($user_string,$food,$measurement_quantity,$measurement_unit,$parse_string,$is_precise,$fuzzy_string = null)
    {
      $parser = new RecipeParser();
      $json = $parser->parse($user_string);
      $return_decoded = json_decode($json);

      $array = array(); 
      $array['user_string']           = $user_string;
      $array['food']                  = trim($food);
      $array['measurement_quantity']  = (float)$measurement_quantity;
      if (!empty($measurement_unit)){
        $array['measurement_unit']  = $measurement_unit;
      }
      $array['parse_string']          = $parse_string;  
      $array['is_precise']          	= $is_precise;

			if($fuzzy_string) 
      	$array['fuzzy_parse_string']= $fuzzy_string;
     
      $json_assert = json_encode($array);

      $this->assertEquals($json,$json_assert);
      $this->assertTrue(!isset($return_decoded->error));
    }

    /**
     * @dataProvider lexemeFuzzyMatchesWithErrors
     * 
     *  just make sure that we could at least grab a food
     */
    public function testFuzzyRecipieMatching($user_string,$food,$measurement_quantity,$measurement_unit,$parse_string)
    {
      include __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.php";
      $parser = new RecipeParser($defaults['grammar']);
      $json = $parser->parse($user_string);
      $result_array = json_decode($json);
      $this->assertTrue(isset($result_array->error));
      $this->assertTrue(isset($result_array->food));
    } 

    /**
     */
    public function lexemeMatches()
    {
        return array(
          // array(<user_string>, <food>, <measurement_quantity>, <measurement_unit>,  <parsed_string>, <fuzzy-can-be-null),

          array("1 sprinkle sugar","sugar",1,"tsp.","1 tsp. sugar","false"),
          array("1 sprinkles sugar","sugar",1,"tsp.","1 tsp. sugar","false"),
          array("1 bite sugar","sugar",1,"tbsp.","1 tbsp. sugar","false"),
          array("1 bites sugar","sugar",1,"tbsp.","1 tbsp. sugar","false"),
          array("1 gulp sugar","sugar",1,"tbsp.","1 tbsp. sugar","false"),
          array("1 gulps sugar","sugar",1,"tbsp.","1 tbsp. sugar","false"),
          array("1 sip sugar","sugar",.5,"tbsp.","0.5 tbsp. sugar","false"),
          array("1 sips sugar","sugar",.5,"tbsp.","0.5 tbsp. sugar","false"),
          array("1 mouthful sugar","sugar",1,"tbsp.","1 tbsp. sugar","false"),
          array("1 mouthfuls sugar","sugar",1,"tbsp.","1 tbsp. sugar","false"),
          array("1 dollop sugar","sugar",1,"fl. oz.","1 fl. oz. sugar","false"),
          array("1 dollops sugar","sugar",1,"fl. oz.","1 fl. oz. sugar","false"),
          array("1 knob sugar","sugar",1,"fl. oz.","1 fl. oz. sugar","false"),
          array("1 knobs sugar","sugar",1,"fl. oz.","1 fl. oz. sugar","false"),
          array("1 smidge sugar","sugar",0.00520833,"fl. oz.","0.00520833 fl. oz. sugar","false"),
          array("1 smidges sugar","sugar",0.00520833,"fl. oz.","0.00520833 fl. oz. sugar","false"),
          array("1 smidgen sugar","sugar",0.00520833,"fl. oz.","0.00520833 fl. oz. sugar","false"),
          array("1 smidgens sugar","sugar",0.00520833,"fl. oz.","0.00520833 fl. oz. sugar","false"),
          array("1 dash sugar","sugar",0.0208333,"fl. oz.","0.0208333 fl. oz. sugar","false"),
          array("1 dashes sugar","sugar",0.0208333,"fl. oz.","0.0208333 fl. oz. sugar","false"),
          array("1 handful sugar","sugar",2.67,"fl. oz.","2.67 fl. oz. sugar","false"),
          array("1 handfuls sugar","sugar",2.67,"fl. oz.","2.67 fl. oz. sugar","false"),


          array("1 heaping tablespoon sugar","sugar",1.125,"tbsp.","1.125 tbsp. sugar","true"),
          array("1 scant cup sugar","sugar",.875,"cup","0.875 cup sugar","true"),
          array("1 large cup sugar","sugar",1.25,"cup","1.25 cup sugar","true"),

          array("76 oz steak"," steak",76,"oz.","76 oz. steak","true"),
          array("7 6 ozs. steaks"," steaks",42,"oz.","42 oz. steaks","true"),
          array("2 tablespoon garlic"," garlic",2,"tbsp.","2 tbsp. garlic","true"),
          array('3 tablespoons sugar', 'sugar', '3', 'tbsp.', '3 tbsp. sugar',"true"),
          array("2 tbsp garlic"," garlic",2,"tbsp.","2 tbsp. garlic","true"),
          array("2 dsp garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dsp. garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 ds. garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 ds garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dstspn garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dstspn. garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dessert spoon garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dessert spoons garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dessertspoon garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("2 dessertspoons garlic"," garlic",2,"dsp.","2 dsp. garlic","true"),
          array("50 oz sword fish"," sword fish",50,"oz.","50 oz. sword fish","true"),
          array("50 OUNcEs sword fish"," sword fish",50,"oz.","50 oz. sword fish","true"),
          array("2 small cokes"," cokes",1.5,null,"1.5 cokes","false","2 sm. cokes"),
          array("little apple","apple",.75,null,"0.75 apple","false", "1 sm. apple"),
          array(" medium coke"," coke",1,null,"1 coke","false", "1 md. coke"),
          array("rice","rice",1,null,"1 rice","false"),
          array("2 large cokes"," cokes",2.5,null,"2.5 cokes","false", "2 lg. cokes"),
          array("2 22.5 oz beers"," beers",45,"oz.","45 oz. beers","true"),
          array("2 22.5 oz tasty beers"," tasty beers",45,"oz.","45 oz. tasty beers","true"),
          array("2 22.5 oz sweet delicious beers"," sweet delicious beers",45,"oz.","45 oz. sweet delicious beers","true"),
          array("large coke"," coke",1.25,null,"1.25 coke","false", "1 lg. coke"),
          array("extra large coke"," coke",1.5,null,"1.5 coke","false", "1 x-lg. coke"),
          
				  array("huge banana"," banana",1.5,null,"1.5 banana","false", "1 huge banana"),
				  array("2 huge banana"," banana",3,null,"3 banana","false", "2 huge banana"),
				  array("extra huge banana"," banana",1.5,null,"1.5 banana","false", "1 huge banana"),
				  array("really huge banana"," banana",1.5,null,"1.5 banana","false", "1 huge banana"),
				  array("very huge banana"," banana",1.5,null,"1.5 banana","false", "1 huge banana"),

          array("sm. soda"," soda",0.75,null,"0.75 soda","false", "1 sm. soda"),

          array("really big chocolate chip cookie"," chocolate chip cookie",1.5,null,"1.5 chocolate chip cookie","false","1 x-lg. chocolate chip cookie"),
          array("2 really big chocolate chip cookie"," chocolate chip cookie",3,null,"3 chocolate chip cookie","false","2 x-lg. chocolate chip cookie"),
          array("5 gram salt"," salt",5,"g.","5 g. salt","true"),
          array("5 grams salt"," salt",5,"g.","5 g. salt","true"),
          array("5.345 kilogram butter"," butter",5.345,"kg.","5.345 kg. butter","true"),
          array("5 kg water"," water",5,"kg.","5 kg. water","true"),
          array("5 kgr water"," water",5,"kg.","5 kg. water","true"),
          array("5 kgr. water"," water",5,"kg.","5 kg. water","true"),
          array("5 liter water"," water",5,"l.","5 l. water","true"),
          array("5 liters water"," water",5,"l.","5 l. water","true"),
          array("5 kl water"," water",5,"kl.","5 kl. water","true"),
          array("5 kl. water"," water",5,"kl.","5 kl. water","true"),
          array("5 kiloliter water"," water",5,"kl.","5 kl. water","true"),
          array("5 kiloliters water"," water",5,"kl.","5 kl. water","true"),
          array("50 OunCes water"," water",50,"oz.","50 oz. water","true"),
          array("50 g pepper"," pepper",50,"g.","50 g. pepper","true"),
          array(" 50 teaspoon pepper "," pepper",50,"tsp.","50 tsp. pepper","true"),
          array(" 50 tsp. pepper "," pepper",50,"tsp.","50 tsp. pepper","true"),
          array("2 grams rice"," rice",2,"g.","2 g. rice","true"),
          array("2 large pumpkin crispbreads"," pumpkin crispbreads",2.5,null,"2.5 pumpkin crispbreads","false","2 lg. pumpkin crispbreads"),
          array("1.5 c bone broth"," bone broth",1.5,"cup","1.5 cup bone broth","true"),
          array("7 g. butter"," butter",7,"g.","7 g. butter","true"),
          array("7 gr butter"," butter",7,"g.","7 g. butter","true"),
          array("7 gr. butter"," butter",7,"g.","7 g. butter","true"),
          array("1 tbsp frozen butter"," frozen butter",1,"tbsp.","1 tbsp. frozen butter","true"),
          array("garlic 2 tablespoon"," garlic",2,"tbsp.","2 tbsp. garlic","true"),
          array("garlic salt 2 teaspoons"," garlic salt",2,"tsp.","2 tsp. garlic salt","true"),
          array("10 oz of almond milk"," almond milk",10,"oz.","10 oz. almond milk","true"),
          array("2 corn tortillas"," corn tortillas",2,null,"2 corn tortillas","false"),
          array("1 greek yogurt"," greek yogurt",1,null,"1 greek yogurt","false"),
          array("2 mcdonalds cheeseburgers"," mcdonalds cheeseburgers",2,null,"2 mcdonalds cheeseburgers","false"),
          array("chocolate chip cookies"," chocolate chip cookies",1,null,"1 chocolate chip cookies","false"),
          array("2 cups rice"," rice",2,"cup","2 cup rice","true"),
     			array("garlic salt 2 teaspoons"," garlic salt",2,"tsp.","2 tsp. garlic salt","true"),
          array("5.4tsp tuna fish"," tuna fish",5.4,"tsp.","5.4 tsp. tuna fish","true"),
          array("50tbsp tuna fish"," tuna fish",50,"tbsp.","50 tbsp. tuna fish","true"),
          array("50mg pepper"," pepper",50,"mg.","50 mg. pepper","true"),
          array("50mg. pepper"," pepper",50,"mg.","50 mg. pepper","true"),
          array("50g pepper"," pepper",50,"g.","50 g. pepper","true"),
          array("50 teaspoons pepper"," pepper",50,"tsp.","50 tsp. pepper","true"),
          array("50 tsp pepper"," pepper",50,"tsp.","50 tsp. pepper","true"),
          array("1 tbsp almond butter"," almond butter",1,"tbsp.","1 tbsp. almond butter","true"),
          array("200ml full fat milk"," full fat milk",200,"ml.","200 ml. full fat milk","true"),
          array("1 tsp spread"," spread",1,"tsp.","1 tsp. spread","true"),
          array("150g smoked mackeral"," smoked mackeral",150,"g.","150 g. smoked mackeral","true"),
          array("160g broccoli"," broccoli",160,"g.","160 g. broccoli","true"),
          array("15g sesame seeds"," sesame seeds",15,"g.","15 g. sesame seeds","true"),
          array("1tbsp rapeseed oil"," rapeseed oil",1,"tbsp.","1 tbsp. rapeseed oil","true"),
          array("10g almonds"," almonds",10,"g.","10 g. almonds","true"),
          array("50g peas"," peas",50,"g.","50 g. peas","true"),
          array(".5 tsp mustard"," mustard",0.5,"tsp.","0.5 tsp. mustard","true"),
          array("25g quinoa"," quinoa",25,"g.","25 g. quinoa","true"),
          array("50g banana"," banana",50,"g.","50 g. banana","true"),
          array("100g greek yogurt"," greek yogurt",100,"g.","100 g. greek yogurt","true"),
          array("1tsp honey"," honey",1,"tsp.","1 tsp. honey","true"),
          array("Frozen berries .5 cup "," Frozen berries",0.5,"cup","0.5 cup Frozen berries","true"),
          array(" 0.5 c broccoli "," broccoli",0.5,"cup","0.5 cup broccoli","true"),
          array("8oz calli tea"," calli tea",8,"oz.","8 oz. calli tea","true"),
          array("2 tsp. honey mustard salad dressing"," honey mustard salad dressing",2,"tsp.","2 tsp. honey mustard salad dressing","true"),
          array("1/2 mango"," mango",0.5,null,"0.5 mango","false"),
          array("1t honey"," honey",1,"tsp.","1 tsp. honey","true"),
          array("1 table spoon honey"," honey",1,"tbsp.","1 tbsp. honey","true"),
          array("11 table spoons honey"," honey",11,"tbsp.","11 tbsp. honey","true"),
          array("11 tblspoons honey"," honey",11,"tbsp.","11 tbsp. honey","true"),
          array("11 tblspn honey"," honey",11,"tbsp.","11 tbsp. honey","true"),
          array("6 spoonfuls honey"," honey",6,"tbsp.","6 tbsp. honey","true"),
          array("6 tb honey"," honey",6,"tbsp.","6 tbsp. honey","true"),
          array("6 tbl honey","honey",6,"tbsp.","6 tbsp. honey","true"),
          array("6 tbs honey"," honey",6,"tbsp.","6 tbsp. honey","true"),
          array("6 tbls honey"," honey",6,"tbsp.","6 tbsp. honey","true"),
          array("6 t sugar"," sugar",6,"tsp.","6 tsp. sugar","true"),
          array("6 ts azucar"," azucar",6,"tsp.","6 tsp. azucar","true"),
          array("6 tea spoon azucar"," azucar",6,"tsp.","6 tsp. azucar","true"),
          array("65 tea spoons azucar"," azucar",65,"tsp.","65 tsp. azucar","true"),
          array("65 tea. azucar"," azucar",65,"tsp.","65 tsp. azucar","true"),
          array("65 teas. azucar"," azucar",65,"tsp.","65 tsp. azucar","true"),
          array(".5 cup of frozen berries"," frozen berries",0.5,"cup","0.5 cup frozen berries","true"),
          array("1/4 cup salsa"," salsa",0.25,"cup","0.25 cup salsa","true"),
          array("6 tb amys refried beans"," amys refried beans",6,"tbsp.","6 tbsp. amys refried beans","true"),
          array("diced green chilies 3 tb"," diced green chilies",3,"tbsp.","3 tbsp. diced green chilies","true"),
          array("shredded cheddar cheese 1/3 c"," shredded cheddar cheese",0.33,"cup","0.33 cup shredded cheddar cheese","true"),
          array("wheat thins"," wheat thins",1,null,"1 wheat thins","false"),
          array("15 golden raisin"," golden raisin",15,null,"15 golden raisin","false"),
          array("1/8 c roast chicken"," roast chicken",0.125,"cup","0.125 cup roast chicken","true"),
          array("lemon juice 1tbsp "," lemon juice",1,"tbsp.","1 tbsp. lemon juice","true"),
          array("60 g. bread with a heavily seeded crust"," bread with heavily seeded crust",60,"g.","60 g. bread with heavily seeded crust","true"),
          array("hot and sour soup"," hot and sour soup",1,null,"1 hot and sour soup","false"),
          array("1/2 red pepper"," red pepper",0.5,null,"0.5 red pepper","false"),
	  array("1 can 14.5 oz. cream of mushroom soup", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup","true"), 
	  array("1 can (14.5 ounCes) cream of mushroom soup", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup","true"), 
	  array(" (14.5 oz.) bacon", "bacon", 14.5,"oz.","14.5 oz. bacon","true"), 
	  array("tea" , "tea", 1,null,"1 tea","false"), 

	  array("1 nakd bar" , "nakd bar", 1,null,"1 nakd bar","false"), 
	  array("6g Marmite" , "Marmite", 6,"g.","6 g. Marmite","true"), 
	  array("1c spaggetti squash" , "spaggetti squash", 1,"cup","1 cup spaggetti squash","true"), 
	  array("kale spinach and mango smoothie" , "kale spinach and mango smoothie", 1,null,"1 kale spinach and mango smoothie","false"), 
	  array("1 box (50 ounCes) mac n cheese", "mac n cheese", 50,"oz.","50 oz. mac n cheese","true"), 
	  array("1 jar (6 ounCes) moonshine", "moonshine", 6,"oz.","6 oz. moonshine","true"), 
	  array("1 bag (4 teaspoons) tea", "tea", 4,"tsp.","4 tsp. tea","true"), 
	  array("1 packet (2 mg) koolaid", "koolaid", 2,"mg.","2 mg. koolaid","true"), 
	  array("1 packet (2 mg) kool-aid", "kool-aid", 2,"mg.","2 mg. kool-aid","true"), 
	  array("1 carton (1/2 gallon) milk", "milk", .5,"gal.","0.5 gal. milk","true"), 
	  array("2 cartons (1/2 gallon) milk", "milk", 1,"gal.","1 gal. milk","true"), 
	  array("7 cartons (1/2 gallon) milk", "milk", 3.5,"gal.","3.5 gal. milk","true"), 
	  array("kale, spinach and mango smoothie" , "kale, spinach and mango smoothie", 1,null,"1 kale, spinach and mango smoothie","false"), 
	  array("Spinach, edamame beans and pickles" , "Spinach, edamame beans and pickles", 1,null,"1 Spinach, edamame beans and pickles","false"), 
	  array("16 fluid ounce apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"), 
	  array("16 fl oz apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"), 
	  array("16 fl. oz apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"), 
	  array("16 fl oz. apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"), 
	  array("16 fl. oz. apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"), 
	  array("16 fl. ozs. apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"),
	  array("16 fl ozs apple juice", "apple juice", 16,"fl. oz.","16 fl. oz. apple juice","true"), 
	  array("1 fl ozs frank's red hot sauce", "frank's red hot sauce", 1,"fl. oz.","1 fl. oz. frank's red hot sauce","true"), 
	  array("2 packet (3.5 mg) kool-aid", "kool-aid", 7,"mg.","7 mg. kool-aid","true"), 
	  array("2 boxes (50 ounCes) mac n cheese", "mac n cheese", 100,"oz.","100 oz. mac n cheese","true"), 
	 	array("1/2. Grated carrot", "Grated carrot", .5,null,"0.5 Grated carrot","false"), 
	 	array("1egg", "egg", 1,null,"1 egg","false"), 

	 	array("half and half creamer", "half and half creamer", 1,null,"1 half and half creamer",'false'), 
	 	array("1 big mac", "big mac", 1,null,"1 big mac",'false'), 
	 	array("1green onion", "green onion", 1,null,"1 green onion","false"), 
	 	array("coke cola 150 ml", "coke cola", 150,"ml.","150 ml. coke cola","true"), 
	 	array("1 bowl soup", "soup", 12,"fl. oz.","12 fl. oz. soup","false"), 
	 	array("2 bowls white bean chili", "white bean chili", 24,"fl. oz.","24 fl. oz. white bean chili","false"), 
	 	array("1 flute champagne", "champagne", 6,"fl. oz.","6 fl. oz. champagne","false"), 
	 	array("4.5 flutes champagne", "champagne", 27,"fl. oz.","27 fl. oz. champagne","false"), 

	 	array("1 scant bowl pears", "pears",10.5,"fl. oz.","10.5 fl. oz. pears","false"), 
	 	array("1 heaped bowl pears", "pears", 13.5,"fl. oz.","13.5 fl. oz. pears","false"), 
	 	array("1 heaping bowl pears", "pears", 13.5,"fl. oz.","13.5 fl. oz. pears","false"), 
	 	array("2 tiny pears", "pears", 1,null,"1 pears","false",'2 x-sm. pears'), 
	 	array("1 very small glass of white wine", "white wine", 4,"fl. oz.","4 fl. oz. white wine","false",'1 x-sm. glass white wine'), 


	 	array("butternut squash (350 mg)", "butternut squash", 350,"mg.","350 mg. butternut squash","true"), 
	 	array("double shot latte", "shot latte", 2,null,"2 shot latte","false"), 
	 	array("4 triple shot latte", "shot latte", 12,null,"12 shot latte","false"), 
	 	array("2 quadruple shot latte", "shot latte", 8,null,"8 shot latte","false"), 
	 	array("half beer", "beer", .5,null,"0.5 beer","false"), 
	 	array("quarter beer", "beer", .25,null,"0.25 beer","false"), 
	 	array("forth beer", "beer", .25,null,"0.25 beer","false"), 
	 	array("fifth beer", "beer", .2,null,"0.2 beer","false"), 
	 	array("third beer", "beer", .33,null,"0.33 beer","false"), 
	 	array("5 half beer", "beer", 2.5,null,"2.5 beer","false"), 
	 	array("2x latte", "latte", 2,null,"2 latte","false"), 
	 	array("3x latte", "latte", 3,null,"3 latte","false"), 
	 	array("4x latte", "latte", 4,null,"4 latte","false"), 
	 	array("2 4x latte", "latte", 8,null,"8 latte","false"), 
	 	array(".2x latte", "latte", .2,null,"0.2 latte","false"), 
	 	array(".25x latte", "latte", .25,null,"0.25 latte","false"), 
	 	array(".33x latte", "latte", .33,null,"0.33 latte","false"), 
	 	array(".5x latte", "latte", .5,null,"0.5 latte","false"), 
	 	array("5 .5x latte", "latte", 2.5,null,"2.5 latte","false"), 
	 	array("150ml coke cola", "coke cola", 150,"ml.","150 ml. coke cola","true"), 
	 	array("300 ml. pasteurized grapefruit juice", "pasteurized grapefruit juice", 300,"ml.","300 ml. pasteurized grapefruit juice","true"), 
	 	array("2 tablespoons.chia seeds", "chia seeds", 2,"tbsp.","2 tbsp. chia seeds","true"),
    array("2 glasses of milk"," milk",16,"fl. oz.","16 fl. oz. milk","false"),
	 	array("1 pot of coffee", " coffee", 20.2884,"fl. oz.","20.2884 fl. oz. coffee",'false'), 

 		array("1 turkey slice", "turkey", 1,"oz.","1 oz. turkey",'false'), 
 		array("1 slice turkey", "turkey", 1,"oz.","1 oz. turkey",'false'), 

 		array("1 mug coffee", "coffee", 12,"fl. oz.","12 fl. oz. coffee",'false'), 
 		array("2 mugs coffee", "coffee", 24,"fl. oz.","24 fl. oz. coffee",'false'), 
 		array("1 scoop ice cream", "ice cream", 3.19995,"fl. oz.","3.19995 fl. oz. ice cream",'false'), 
 		array("2 scoop ice cream", "ice cream", 6.3999,"fl. oz.","6.3999 fl. oz. ice cream",'false'), 
 		array("1 jigger tea", "tea", 1.5,"fl. oz.","1.5 fl. oz. tea",'false'), 
 		array("2 jiggers tea", "tea", 3,"fl. oz.","3 fl. oz. tea",'false'), 

 		array("1 spoon sugar", "sugar", 1,"tbsp.","1 tbsp. sugar",'true'), 
 		array("2 spoons sugar", "sugar", 2,"tbsp.","2 tbsp. sugar",'true'), 

	 	array("1 package quaker oats", "quaker oats", 1,null,"1 quaker oats",'false',"1  package quaker oats"),
	 	array("1 packages quaker oats", "quaker oats", 1,null,"1 quaker oats",'false',"1  package quaker oats"),
	 	array("1 pkg quaker oats", "quaker oats", 1,null,"1 quaker oats",'false',"1  pkg quaker oats"),
	 	array("1 pkg. quaker oats", "quaker oats", 1,null,"1 quaker oats",'false',"1  pkg quaker oats"),
	 	array("1 pkgs quaker oats", "quaker oats", 1,null,"1 quaker oats",'false',"1  pkg quaker oats"),
	 	array("1 pkgs. quaker oats", "quaker oats", 1,null,"1 quaker oats",'false',"1  pkg quaker oats"),

	// 	array("1pkg \"Recover\"", "milk", 1,"gal.","1 gal. milk"),  /// not fuzzy any more

	//  array("2 cartons (1/2 gallon each) milk", "milk", 1,"gal.","1 gal. milk"), 
	  //array("1 pint (1/2 quart) beer", "beer", .5,"qt.",".5 qt. beer"), 
	  //array("1 pint (quart 1/2 quart) beer", "beer", .5,"qt.",".5 qt. koolaid"), 
	  //array("2 packet (3 1/2 mg) kool-aid", "kool-aid", 7,"mg.","7 mg. kool-aid"), 

        ); 
    }

    public function lexemeFuzzyMatchesWithErrors(){
	return array(
 // 		array(" (14.5 oz.) bacon", "bacon", 14.5,"oz.","14.5 oz. bacon"), 
//	  	array("1pkg \"Recover\"", "milk", 1,"gal.","1 gal. milk"),  /// not fuzzy any more
	  	//array("1000 g 3 beers", "mac n cheese", 50,"oz.","50 oz. mac n cheese",'true'), 
	  	array("(Spinach, edamame beans and pickles)" , "Spinach, edamame beans and pickles", 1,null,"1 Spinach, edamame beans and pickles"), 

	  //	array("kale, spinach and mango smoothie" , "kale, spinach and mango smoothie", 1,"","1 kale, spinach and mango smoothie"), 
	  //	array("kale spinach and mango- smoothie)" , "kale spinach and mango smoothie", 1,"","1 kale spinach and mango smoothie"), 
        ); 
    }


    public function lexemeMatches1_off_Matches_to_test_specific(){
	return array(

	 	array("half and half creamer", "half and half creamer", 1,null,"1 half and half creamer",'false'), 
	 	//array("1 package quaker oats", "quaker oats", 1,null,"1 quaker oats",'false'),
	 //	array("half and half creamer", "half and half creamer", 1,"fl. oz.","20.2884 fl. oz. coffee",'false'), 
	//  array("1 can (14.5 ounCes) cream of mushroom soup", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup","true"), 
//  		array("1 turkey slice", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup",'false'), 
 // 		array("1 slice turkey", "cream mushroom soup", 14.5,"oz.","14.5 oz. cream mushroom soup",'false'), 
 // 		array(" (14.5 oz.) bacon", "bacon", 14.5,"oz.","14.5 oz. bacon"), 
//	  	array("Spinach, edamame beans and pickles" , "Spinach, edamame beans and pickles", 1,"","1 Spinach, edamame beans and pickles"), 
	 // 	array("kale, spinach and mango smoothie" , "kale, spinach and mango smoothie", 1,"","1 kale, spinach and mango smoothie"), 
	  	//array("1/2. Grated carrot", "milk", 1,"gal.","1 gal. milk"), 
//	  	array("1egg", "milk", 1,"gal.","1 gal. milk"), 
//	  	array("Fennel tea with heaped teaspoon on collagen", "milk", 1,"gal.","1 gal. milk"), 
	  ///	array("1 pot of yogurt", "milk", 1,"gal.","1 gal. milk"), 

	 //		array("double shot latte", "white wine", .25,null,"0.25 white wine"), 
	 		//array("2x latte", "sugar", 2,"tbsp.","2 tbsp. sugar"), 
          //array("2 glasses of milk"," coke",2,"serving","1 glasses milk"),
          //array("2 glasses of milk"," coke",2,"serving","1 glasses milk"),
//array("6 oz. coffee with 2 tbsp. creamer","coke",2,"serving","xx",'truxe'), 
	 	//array("2 tablespoons.chia seeds", "chia seeds", 2,"tbsp.","2 tbsp. chia seeds"),
	 		//array("0.5 tbsp sugar", "sugar", .5,"tbsp.","0.5 tbsp. sugar"), 
          array("1 heaping tablespoon sugar","sugar",1.125,"tbsp.","1.125 tbsp. sugar",'true'),
	 // array("1 box (50 ounCes) mac n cheese", "mac n cheese", 50,"oz.","50 oz. mac n cheese"), 
	 	//array("1 pot of coffee", " coffee", 1,"fl. oz.","20.2884 fl. oz. coffee",'false'), 
	//  array("1000 g 3 beers", "mac n cheese", 50,"oz.","50 oz. mac n cheese",'true'), 
	// 	array("1 pot of coffee", " coffee", 20.2884,"fl. oz.","20.2884 fl. oz. coffee",'false'), 
	  //array("1 beer", "mac n cheese", 50,null,"50 oz. mac n cheese"), 
	 	//array("1 very small glass of white wine", "white wine", 4,"fl. oz.","4 fl. oz. white wine","false",'1 x-sm. glass white wine'), 
	 	//array("1 very small glass of white wine", "white wine", 4,"fl. oz.","4 fl. oz. white wine","false",'1 x-sm. glass white wine'), 
   //       array("2 small cokes"," cokes",1.5,null,"1.5 cokes","false","2 sm. cokes"),
    //      array("76 oz steak"," steak",76,"oz.","76 oz. steak","true"),
          //array("1 dollop sprite","sugar",1.125,"serving","2 serving popcorn"),
         
	  	//array("coke cola 150 ml", "milk", 1,"gal.","1 gal. milk"), 
	  	//array("i cup of coffee", "milk", 1,"gal.","1 gal. milk"), 
	 // 	array("1pkg \"Recover\"", "milk", 1,"gal.","1 gal. milk"), 
	  	//array("2 cartons (1/2 gallon each) milk", "milk", 1,"gal.","1 gal. milk"), 
	  //	array("Frittata (egg, potato, cheese, onion & tomato)" , "kale, spinach and mango smoothie", 1,"","1 kale, spinach and mango smoothie"), 
	  	//array("kale spinach and mango- smoothie)" , "kale spinach and mango smoothie", 1,"","1 kale spinach and mango smoothie"), 
        ); 
    }


 
//array("1 to 2 cans( 14.5 oz each) tomatoes","coke",2,"serving","xx"), TODO to_quantity
//array("1 cup shredded cheese","coke",2,"serving","xx"), 							TODO to_quantity

//array("1 cup cubed cheese","coke",2,"serving","xx"), 									TODO ingredient_preparation:preparation (adjective)
//array("1 cup lettuce, shredded","coke",2,"serving","xx"), 						TODO ingredient_preparation:preparation (adjective)
// --- look in recipies table in foodgeeks DB for more examples


//array("1 tbsp yellow Aji, Amarillo or Chile power","coke",2,"serving","xx"), 		TODO substutions
/// dont match "7 or 8 beers" hahaha


/// TODO: THat big if/else function in recipe_ingredients have a param to know which application: foodgeeks or tummy
}

