<?php

$defaults =  array(


  'grammar' => array(

        "/^\($/"                                             		    => "T_LPAREN",
        "/^\)$/"                                             		    => "T_RPAREN",

        "/^([0-9]+)?\.[0-9]+$/"                                             => "T_DECIMAL",
        "/^([0-9])+$/"                                                      => "T_INTEGER" ,
        
        "/^(\ )*(centi|milli|kilo|deci)?gram(s)?$/i"                        => "T_GRAM",
        "/^(\ )*(k|m)?g(\.)?$/"                                             => "T_GRAM",
    
 //       "/^(\ )*pound(s)?$/i"                                                   => "T_POUND", TEST 
 //       "/^(\ )*lbs(\.)?$/"                                                     => "T_POUND", TEST 
 
        "/^(\ )*(centi|milli|kilo|deci)?liter(s)?$/i"                       => "T_LITER",
        "/^(\ )*(k|m|c)?l(\.)?$/"                                           => "T_LITER",
 
        "/^(\ )?tablespoon(s)?$/i"                                          => "T_TABLESPOON",
        "/^(\ )?table spoon(s)?$/i"                                         => "T_TABLESPOON", 
        "/^(\ )?tablespn(s)?$/i"                                            => "T_TABLESPOON",
        "/^(\ )?tblspoon(s)?$/i"                                            => "T_TABLESPOON", 
        "/^(\ )?tblspn(s)?$/i"                                              => "T_TABLESPOON", 
        "/^(\ )?spoonful(s)?$/i"                                            => "T_TABLESPOON",
        "/^(\ )?tbsp(\.)?$/i"                                               => "T_TABLESPOON",
        "/^(\ )?tb(s|l|ls|sps)?(\.)?$/i"                                    => "T_TABLESPOON",
  
        "/^(\ )?teaspoon(s)?$/i"                                            => "T_TEASPOON",
        "/^(\ )?tea spoon(s)?$/i"                                           => "T_TEASPOON", 
        "/^(\ )?tsp(\.)?$/i"                                                => "T_TEASPOON",
        "/^(\ )?tea(s)?(\.)$/i"                                             => "T_TEASPOON",
        "/^(\ )?t(s)?(\.)?(\ )?$/i"                                         => "T_TEASPOON", 


      //  "/^(\ )?fluid ounce(s)?$/i"                                                     => "T_OUNCE", ?fluid ounce ? 
      //  "/^(\ )?fl(\.)? oz(\.)?$/i"                                                     => "T_OUNCE", ?fluid ounce ?
 
      //  "/^(\ )?gallon(s)?$/i"                               => "T_GALLON", 
      //  "/^(\ )*(k|m)?g(\.)?$/"                              => "T_GALLON",
 

        "/^(\ )?ounce(s)?$/i"                                               => "T_OUNCE",
        "/^(\ )?oz(s)?(\.)?$/i"                                             => "T_OUNCE",
 
        "/^(\ )*cup(s)?$/i"                                                 => "T_CUP",
        "/^(\ )*c(\.)?$/"                                                   => "T_CUP",
      
        "/^(T_INTEGER|T_DECIMAL)$/"                                         => "T_NUMBER", 
        
     //   "/^(\ )*(single|double|triple|quadruple)$/"                       => "T_MULTIPLER", TEST
     //   "/^(\ )*(2x|3x|4x|5x)$/"                                          => "T_MULTIPLER", TEST


        "/^(\ )?(can|glass|bowl)$/i"   					    => "T_CONTAINER" ,
        "/^(\ )?(T_NUMBER\ )(T_CONTAINER)$/i" 		    		    => "T_CONTAINER_MULT" ,

        "/^(extra |really |very )?(\ )*(tiny|little|small|medium|large|big|huge)$/i"   => "T_IMPRECISE_UNIT" ,
        "/^(\ )*(sm|m|lg|x-lg|)(\.)?$/i"                                               => "T_IMPRECISE_UNIT" , 


    //   "/^(\ )*(tall|venti|grande|tall)$/"                                       => "T_STARBUCKS_UNIT" ,
    //   "/^(\ )*(mouth(\ )?full)$/"                                               => "T_IMPRECISE_UNIT" ,
    //    "heaped" / "heaping" and "scant" => "T_IMPRECISE_UNIT" ,
    //    "servings"
    //    "package" "pkg"
    //    celery stalk ??
    //    pot | glass | bowl


         "/^(?!T_)[a-zA-z]*$/"                                              => "T_WORD",
         "/^(\ )?(T_WORD(\ )+)*T_WORD$/"                                    => "T_FOOD", 
  
        //---may be a kludge??, 
        // can result in food -> food -> food -> word
        //  necessary for  '2 cups rice', 'chocolate chip cookies', & 'garlic salt 2 teaspoons'  to all work, w/o 1 o4 2 work not all 3 
        "/^T_FOOD$/"                                                        => "T_FOOD", 
        /// --- end (potential) kludge

        "/^(T_OUNCE|T_GRAM|T_CUP|T_LITER|T_TABLESPOON|T_TEASPOON)$/"        => "T_PRECISE_UNIT", 
        
        "/^(\ )?(T_NUMBER\ )?(\ )?T_IMPRECISE_UNIT$/"                       => "T_IMPRECISE_MEASURE",
       // "/^(\ )?T_NUMBER(\ )*T_PRECISE_UNIT$/"                              => "T_PRECISE_MEASURE",
        "/^(\ )?T_NUMBER(\ )*T_PRECISE_UNIT$/"                              => "T_PRECISE_MEASURE",

        "/^(\ )?(T_LPAREN)(\ )*T_PRECISE_MEASURE(\ )*(T_RPAREN)$/"     => "T_PRECISE_MEASURE",
        
        "/^(\ )?T_PRECISE_MEASURE(\ )*T_FOOD(\ )?$/"                        		=> "T_RECIPE_INGREDIENT",
        "/^(\ )?T_FOOD(\ )*T_PRECISE_MEASURE(\ )?$/"                        		=> "T_RECIPE_INGREDIENT",
        "/^(\ )?(T_NUMBER)?T_IMPRECISE_MEASURE(\ )*T_FOOD(\ )?$/"           		=> "T_RECIPE_INGREDIENT",

        "/^(T_CONTAINER_MULT|T_CONTAINER)(\ )+T_RECIPE_INGREDIENT$/"   			=> "T_RECIPE_INGREDIENT",  
        "/^(\ )?(T_NUMBER\ )(\ )?T_FOOD(\ )?$/"                            		=> "T_RECIPE_INGREDIENT",  
    
        "/^(\ )?T_NUMBER(\ )+T_RECIPE_INGREDIENT(\ )?$/"                    => "T_RECIPE_INGREDIENT_MULT",
//        "/^(\ )?T_MULTIPLER(\ )+T_RECIPE_INGREDIENT(\ )?$/"                    => "T_RECIPE_INGREDIENT_MULT",    TEST 

        "/^(\ )?T_RECIPE_INGREDIENT|T_RECIPE_INGREDIENT_MULT(\ )?$/"        => "T_TERM",
  ),



);


return $defaults;
