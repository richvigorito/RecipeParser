# RecipeParser

**RecipeParser** is a lightweight natural-language compiler that converts cooking instructions into structured representations. Inspired by compiler design principles, it uses a recursive parsing strategy to analyze recipe steps and extract meaningful data like quantities, units, ingredients, and modifiers.

## Features

- Converts free-form recipe text into structured data
- Understands quantities, units, ingredient descriptors, and container references
- Designed to operate like a compiler front-end for natural language
- Easily extensible grammar

## Example

Input: ``Add 2 cups of chopped carrots to the pot.``

Parsed Output:
```json
{
  "action": "Add",
  "quantity": 2,
  "unit": "cups",
  "modifier": "chopped",
  "ingredient": "carrots",
  "container": "pot"
}
```

> more examples in tests directory

## How It Works
Lexer: Loads (compiler)[https://github.com/richvigorito/Lexer], tokenizes the sentence using a context-aware lexer (currently PHP, moving to C for FFI compatibility)

Parser: Recursively parses tokens into a domain-specific expression tree using a Pratt-style or recursive descent approach

Mapper: Outputs structured data usable by recipe apps, voice assistants, or nutrition systems

## Motivation
While traditional NLP techniques often rely on statistical models or LLMs, this project takes a compiler-oriented approach. It treats cooking instructions as a language with grammar and rules. This is not necessarily the best fit for natural language in general, but it offers high precision for well-bounded input like recipes.


