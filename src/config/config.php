<?php /* vim:set filetype=php tabstop=4 softtabstop=4 shiftwidth=4 noexpandtab smartindent: */

$defaults =  array(

  'grammar' => array(

        "/^\($/"                                          => "T_LPAREN",
        "/^\)$/"                                          => "T_RPAREN",
        "/^,$/"                                           => "T_COMMA",
        "/^\"$/"                                          => "T_DOUBLE_QUOTE",

        "/^([0-9]+)?\.[0-9]+$/"                           => "T_DECIMAL",
        "/^([0-9])+$/"                                    => "T_INTEGER" ,
        
        "/^(\ )*(centi|milli|kilo|deci)?gram(s)?$/i"      => "T_GRAM",
        "/^(\ )*(c|d|k|m)?g(\.)?$/"                           => "T_GRAM",
        "/^(\ )*(c|d|k|m)?gr(\.)?$/"                          => "T_GRAM",
    
 //       "/^(\ )*pound(s)?$/i"                                                   => "T_POUND", TEST (be mindful of pound cake) 
 //       "/^(\ )*lbs(\.)?$/"                                                     => "T_POUND", TEST 
 
        "/^(\ )*(centi|milli|kilo|deci)?liter(s)?$/i"     => "T_LITER",
        "/^(\ )*(centi|milli|kilo|deci)?litre(s)?$/i"     => "T_LITER",
        "/^(\ )*(k|m|c)?l(\.)?$/"                         => "T_LITER",
 
        "/^(\ )?tablespoon(s)?$/i"                        => "T_TABLESPOON",
        "/^(\ )?table spoon(s)?$/i"                       => "T_TABLESPOON", 
        "/^(\ )?spoon(s)?$/i"							  => "T_TABLESPOON", 
        "/^(\ )?tablespn(s)?$/i"                          => "T_TABLESPOON",
        "/^(\ )?tblspoon(s)?$/i"                          => "T_TABLESPOON", 
        "/^(\ )?tblspn(s)?$/i"                            => "T_TABLESPOON", 
        "/^(\ )?spoonful(s)?$/i"                          => "T_TABLESPOON",
        "/^(\ )?tbsp(\.)?$/i"                             => "T_TABLESPOON",
        "/^(\ )?tb(s|l|ls|sps)?(\.)?$/i"                  => "T_TABLESPOON",
  
        "/^(\ )?teaspoon(s)?$/i"                          => "T_TEASPOON",
        "/^(\ )?tea spoon(s)?$/i"                         => "T_TEASPOON", 
        "/^(\ )?tsp(\.)?$/i"                              => "T_TEASPOON",
        "/^(\ )?tea(s)?(\.)$/i"                           => "T_TEASPOON",
        "/^(\ )?t(s)?(\.)?(\ )?$/i"                       => "T_TEASPOON", 

        "/^(\ )?dessertspoon(s)?$/i"					  => "T_DESSERTSPOON",
        "/^(\ )?dessert spoon(s)?$/i"					  => "T_DESSERTSPOON",
        "/^(\ )?(ds|dsp|dstspn)(\.)?$/i"                  => "T_DESSERTSPOON",

       "/^(\ )?gallon(s)?$/i"                             => "T_GALLON", 
       "/^(\ )?gal(\.)?$/i"                               => "T_GALLON", 
  
        "/^(\ )?pint(s)?$/i"                              => "T_PINT", 
        "/^(\ )?pt(s)?(\.)?$/i"                           => "T_PINT", 
      
         "/^(\ )?quart(s)?$/i"                            => "T_QUART", 
        "/^(\ )?qt(s)?(\.)?$/i"                           => "T_QUART", 
 
        "/^(\ )?fluid ounce(s)?$/i"                       => "T_FLUID_OUNCE",
        "/^(\ )?fl(\.)? oz(s)?(\.)?$/i"                   => "T_FLUID_OUNCE",
 
        "/^(\ )?ounce(s)?$/i"                             => "T_OUNCE",
        "/^(\ )?oz(s)?(\.)?$/i"                           => "T_OUNCE",
 
        "/^(\ )*cup(s)?$/i"                               => "T_CUP",
        "/^(\ )*c(\.)?$/"                                 => "T_CUP",
      
        "/^(T_INTEGER|T_DECIMAL)$/"                       => "T_NUMBER", 
      
        "/^(\ )*(half and half)( creamer)?(\ )*$/i"				  => "T_HARD_CODED_FOOD", // dont treat "half" as multipler here
        "/^(\ )*(half and half)(\ )*$/i"				  => "T_HARD_CODED_FOOD", // dont treat "half" as multipler here
        "/^(\ )*(big mac)(\ )*$/i"						  => "T_HARD_CODED_FOOD", // dont treat "half" as multipler here

        "/^(?<!half and )half(?! and half)$/i"			  => "T_MULTIPLIER",
        "/^(\ )*(third|quarter|forth|fifth)$/i"			  => "T_MULTIPLIER",
        "/^(\ )?(single|double|triple|quadruple)$/i"      => "T_MULTIPLIER", 
        "/^(\ )*(.50x|.5x|.25x|.33x|.20x|.2x)$/i"         => "T_MULTIPLIER", 
        "/^(\ )*(2x|3x|4x|5x)$/i"                         => "T_MULTIPLIER", 


        "/^(\ )?(can(s)?|mug(s)?|bottle(s)?|pot(s)?|glass(es)?|bowl(s)?)$/i"             => "T_CONTAINER" ,
        "/^(\ )?(jigger(s)?|scoop(s)?)$/i"												 => "T_CONTAINER" ,
        "/^(\ )?(carton(s)?|jar(s)?|bag(s)?|packet(s)?|flute(s)?|box(es)?)$/i"           => "T_CONTAINER" ,
        "/^(\ )?(package(s)?|pkg(s)?(\.)?)$/i"                                           => "T_CONTAINER" ,
        "/^(\ )?(T_NUMBER\ )(T_CONTAINER)$/i"                                            => "T_CONTAINER_MULT" ,

        "/^(extra |really |very )?(\ )*(tiny|scant|heaped|heaping|little|small|medium|large|big|huge)$/i"	=> "T_IMPRECISE_UNIT" ,
        "/^(\ )*(sm|m|lg|x-lg|)(\.)?$/i"																	=> "T_IMPRECISE_UNIT" , 

        "/^(\ )*(dash(es)?|slice(s)?|sprinkle(s)?|bite(s)?|handful(s)?|mouthful(s)?|dollop(s)?|knob(s)?|smidge(s)?|smidgen(s)?)(\.)?$/i"					=> "T_IMPRECISE_UNIT" , 
        "/^(\ )*(sip(s)?|gulp(s)?)(\.)?$/i"												=> "T_IMPRECISE_UNIT" , 

    //   "/^(\ )*(tall|venti|grande|tall)$/"                                       => "T_STARBUCKS_UNIT" ,
    //   "/^(\ )*(mouth(\ )?full)$/"                                               => "T_IMPRECISE_UNIT" ,
    //    "heaped" / "heaping" and "scant" => "T_IMPRECISE_UNIT" ,
    //    "servings"
    //    "package" "pkg"
    //    celery stalk ??
    //    pot | glass | bowl


      //   "/^(?!T_)with(\ )?$/"												  => "T_CONJUCT",
         "/^(?!T_)[a-zA-z-']*$/"                                              => "T_WORD",
         "/^(\ )?(T_HARD_CODED_FOOD(\ )+)*T_HARD_CODED_FOOD$/"                => "T_WORD", 

         "/^(\ )?(T_WORD(\ )+(T_COMMA(\ )+)?)*T_WORD$/"                       => "T_FOOD", 
         "/^(\ )?(T_WORD(\ )+)*T_WORD$/"                                      => "T_FOOD", 
         "/^(\ )?(T_DOUBLE_QUOTE)(\ )*T_WORD(\ )*(T_DOUBLE_QUOTE)$/"          => "T_FOOD",
         "/^(\ )?(T_DOUBLE_QUOTE)(\ )*T_FOOD(\ )*(T_DOUBLE_QUOTE)$/"          => "T_FOOD",
  
        //---may be a kludge??, 
        // can result in food -> food -> food -> word
        //  necessary for  '2 cups rice', 'chocolate chip cookies', & 'garlic salt 2 teaspoons'  to all work, w/o 1 o4 2 work not all 3 
        "/^T_FOOD$/"                                                          => "T_FOOD", 
        /// --- end (potential) kludge

        "/^(T_FLUID_OUNCE|T_OUNCE|T_DESSERTSPOON|T_TABLESPOON|T_TEASPOON)$/"  => "T_PRECISE_UNIT", 
        "/^(T_GALLON|T_PINT|T_QUART|T_LITER|T_GRAM|T_CUP)$/"                  => "T_PRECISE_UNIT", 
        
        "/^(\ )?(T_NUMBER\ )?(\ )?(T_IMPRECISE_UNIT)(\ )+T_CONTAINER$/"       => "T_IMPRECISE_MEASURE",
        "/^(\ )?(T_NUMBER\ )?(\ )?(T_IMPRECISE_UNIT)$/"                       => "T_IMPRECISE_MEASURE",

        "/^(T_CONTAINER_MULT|T_CONTAINER)(\ )+T_RECIPE_INGREDIENT$/"          => "T_RECIPE_INGREDIENT",  
        "/^(T_CONTAINER_MULT|T_CONTAINER)(\ )+T_FOOD$/"						  => "T_RECIPE_INGREDIENT",  
        "/^(\ )?T_NUMBER(\ )*T_PRECISE_UNIT$/"                                => "T_PRECISE_MEASURE",
        
		"/^(\ )?T_NUMBER(\ )*T_IMPRECISE_MEASURE(\ )*T_PRECISE_UNIT$/"        => "T_PRECISE_MEASURE",   /// This is a known 'precise measure' w/ an imprecse adjective: 'heaping tbsp'

        "/^(\ )?(T_LPAREN)(\ )*T_PRECISE_MEASURE(\ )*(T_RPAREN)$/"            => "T_PRECISE_MEASURE",
        
        "/^(\ )?T_PRECISE_MEASURE(\ )*T_FOOD(\ )?$/"                          => "T_RECIPE_INGREDIENT",
        "/^(\ )?T_FOOD(\ )*T_PRECISE_MEASURE(\ )?$/"                          => "T_RECIPE_INGREDIENT",
        "/^(\ )?(T_NUMBER)?T_IMPRECISE_MEASURE(\ )*T_FOOD(\ )?$/"             => "T_RECIPE_INGREDIENT",
        "/^(\ )?(T_NUMBER)?(\ )?T_FOOD(\ )*T_IMPRECISE_MEASURE(\ )?$/"        => "T_RECIPE_INGREDIENT",

        "/^(\ )?(T_NUMBER\ )(\ )?T_FOOD(\ )?$/"                               => "T_RECIPE_INGREDIENT",  
    
        "/^(\ )?(T_NUMBER(\ )+)?(T_MULTIPLIER)(\ )+T_FOOD(\ )?$/"             => "T_RECIPE_INGREDIENT_MULT",   
        "/^(\ )?T_NUMBER(\ )+T_RECIPE_INGREDIENT(\ )?$/"                      => "T_RECIPE_INGREDIENT_MULT",

        //"/^(\ )?(T_RECIPE_INGREDIENT|T_RECIPE_INGREDIENT_MULT)T_CONJUST(T_RECIPE_INGREDIENT|T_RECIPE_INGREDIENT_MULT)(\ )?$/"        	=> "T_TERM",
        "/^(\ )?T_RECIPE_INGREDIENT|T_RECIPE_INGREDIENT_MULT(\ )?$/"        	=> "T_TERM",
  ),



);


return $defaults;
