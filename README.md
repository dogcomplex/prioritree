# PrioriTree
WIP Tinkering of a language-agnostic config-based HMVC REST API CMS that uses lazy config merging as its main way of handling complexity. 

Implemented here in PHP, but meant to use the same namespace and config data across a wide variety of contexts (URL, .env, DB, frontend, backend, Request, Response, configs, files, etc).  Each module and folder is meant to be self-encapsulated, defined only by itself and its children (who are likewise defined). Any endpoint can be defined with the following levels of complexity and interpretted (lazily) by the compiler: 
  - `__value`: single value (string, int, function, array, etc), e.g. `$some->endpoint->__value = "Some value"` 
  - `__array`: a directory of sub-values, with `__value` key reserved for invoking self e.g. $some->endpoint = [ '__value' => "Some value", 'name' => "Some Endpoint"].  Numerical arrays, and simple types may be auto-interpretted as `__value`s.
  - `__function`: a function that is auto-called by the compiler, with its params as child endpoints (sharing named and indexed aliases) and return value as `__value` keyword. e.g. 
      - `$some->endpoint->__function = function($name) { return "Endpoint '". $name ."' returns: ". rand() }`
      - Calling `$some->endpoint()` would now yield `"Endpoint 'Some Endpoint' returns: 1234"` or similar.
  - `__object`: treated as a black box that hopefully implements our technique of having callable sub-values.  The idea is we should hopefully be able to use 3rd party classes and wrap them in our interpreter to get the most reuse out of them as we can (WIP).  Otherwise, classes are just a different way to write an array of endpoints with inline syntax for `__private`, `__static`, `__function`, `__value` etc keywords.  e.g.:
      
```
$some->endpoint = new class { 
  private $count = 0;  
  function inc = function() { return ++$this->count; }  
}
```
Is the same as:  
```
$some->endpoint = [
    'count' => [
      '__private' => true,
      '__value' => 0
    ],
    'inc' => [
      '__function' => function() { return ++$this->count; }
      '__private' => false \\ but this is implicit anyway
    ]
]
```

That's the basics.  From there it's about breaking down fundamentally-common operations into keywords (prefixed by `__`, but maybe by `.` - still deciding), and defining them in the root namespace (e.g. `/function` defines how all `__function` tags are interpreted).  Bootloading this is tricky, and is the main WIP here.

## Some Example "Magic" Keywords
- `__alias`: the string namespace of an endpoint this one acts as (for all operations e.g. GET, POST, PUT etc)
- `__extends`: the string namespace of an endpoint this one uses for default values (layering over the old one).  Indistinquishable from `__alias` for GET, but does not modify underlying directory for SET-like commands (e.g. POST/PUT).
- `__file`: if string, indicator of the location of the file or folder defining this endpoint.  File is auto-loaded (lazily) and interpretted like a `__function`, with any definitions treated as functions/values within its namespace. Directories are treated like `__array`s, with files as named values (with `__file` keywords), and they are interpretted lazily as needed. (WIP).
- `__template`: like `__file`, essentially just treated as a function, with child values auto-loaded in and interpretted lazily (WIP! Might not be possible with PHP).  Every endpoint has a default implicit template (defined at `/template`) which is just a to-string interpreter, so filling in this keyword means just customizing it.
- `__layers`: an ordered (numeric) list of the endpoints making up this one (default is just self `'__layers' => ['.']`, `__extend` would make it `'__layers' => ['.', '__extend']`. Complex endpoints might mix in multiple pieces e.g. `$sandwich->__layers = ['/bread/slice', '/lettuce', '/tomato/slice', '.', '/meats/turkey', '/bread/slice' ]`, where `'.'` would be where the current endpoint can customize itself while using all the merged values from other layers.  Treats visibility photoshop-style, where upper layers (lower numeric index) can override lower ones.  This composition is lazy-recursive. (Major WIP, but the core to this whole concept).
- `__parent`: an implicitly-generated way for an endpoint to reference its parent container (e.g. `/some/endpoint/__parent = "/some"`)
- `__key`: an implicitly-generated way for an endpoint to reference its own container (e.g. `/some/endpoint/__key = "endpoint")
- `__path`: ... `/some/endpoint/__path = "/some/endpoint"`
- It just gets more specialized from here. `__controller`, `__router`, `__model`, `__service` etc - all helper keywords/concepts using these building blocks.  

The theory is all modern web apps all implicitly use this kind of structure anyway, just in varying degrees of messiness, consistency, and "magic" implicitness.  Our aim is to provide the minimum building blocks for an initial kernel, then build all the rest of the concepts people are used to in - aliasing as needed, so all ways of writing are supported.  From there it's about being able to interpret and incorporate existing 3rd party implementations out of the box (wrapping them however we need to) and seeing if the result is workable.  Probably not - probably too many styles of writing things and complex layering that the compiler gets overwhelmed and whatever simplicity/elegance we had is lost, but then again maybe not.  The win is if this is in fact a very readable and language-independent namespaceable format that any existing code can be converted into and quickly understood by our system.  (and therefore would act as a really good benchmark of the convolutedness of most code these days).

Primarily, this method of programming encourages functional programming encapsulation of endpoints, while being flexible to handle any old garbage - making it suitable for hacky MVP building but gravitating coding style towards elegant consistent namespacing of everything, and breaking down black-box functions into easily-configurable endpoints.  The end joy is being able to have one consistent method of defining an endpoint that "just works", while still having that magic itself easily-definable (and configurable) across platforms.  e.g. something like this:

```
'users' => [
  'mr_burns' => [
    '__extends' => '/user',
    'title' => ["Mister",
      'abbr' => "Mr."
    ],
    'name' => [function($first, $last) { $first ." ". $last; },
      'first' => "Montgomery",
      'last' => "Burns"
    ],
    '__locales' => [
      'es' => [
        'title' => ["Señor"
          'abbr' => "Sr."
        ]
      ],
    ],
    '__template' => [function() { return '<font color=". $color .">'.$name.'</font>' }
      'color' => ['gold'
        '__one_of' => ['gold', 'burgundy', 'dalmation']
      ]
    ]
  ]
]
```
^ As user input, that should all "just work", although we can get even deeper with it, and split things into layers. (e.g. the `__one_of` validation might want to be in a separate "users/mr_burns/__validation" layer, and we might want to separate data from functions for portability reasons.  (That could even be done automatically by the compiler). Point is, it all works together and we can define things quickly without losing customizability (Burns has a custom validation type even! Most systems would have to do this in the general /user model or make a custom model.  We're technically doing that too, just putting the extension here at /users/mr_burns, but it's organic and makes sense.)  The eventual big trick is finding an elegant way to distinguish between the data that can be saved statically (in code and configs layers), that which can go in the db, and that which is user input from requests which needs validation.  You'll probably see all the standard "model", "view", "controller", etc terminology pop up, but it will be generalized down to the barebones - as an all-encompassing theory of all modern framework design, once hierarchical namespacing is taken to its logical conclusions.

Well that's the dream, at least.  And probably one that won't get much use even if it works.  But worth tinkering.
