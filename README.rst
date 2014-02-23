=======
KaForkL
=======

About
=====

KaForkL is an image based turing complete interpretor language. The different
color channels define directions to walk, commands, values and variables. 

The language
============

Red
---

The red value defines the direction for the processor to take. If multiple 
directions are given the processor will fork and process all the given 
commands starting north and forking clockwise.

Green
-----

The green value refers to a position on the variable stack. There are 256 
possible variables.

Blue
----

Defines a byte value. Depending on the used command a value is interpreted as
an integer or char.

Alpha
-----

The alpha value of a pixel defines a command:

- Simple Operations

  - 0: Just fork
  
  - 1: Jump, use variable as jump width
  
  - 2: Jump, use value as jump width

- IO operations

  - 10: Write value
  
  - 11: Write variable content
  
  - 12: Copy variable to stack position
  
  - 15: Read to variable
  
  - 16: Read value into variable

- Conditional statements

  - 20: Only fork if value equals variable
  
  - 21: Only fork if value not equals variable
  
  - 22: Only fork if value is lower then variable
  
  - 23: Only fork if value is greater then variable
  
  - 24: Only fork if value is lower then or equal variable
  
  - 25: Only fork if value is greater then or equal variable

- Calculations

  - 30: Shift variable by value to left
  
  - 31: Shift variable by value to right
  
  - 32: Combine variable and value by logical and
  
  - 33: Combine variable and value by logical or
  
  - 34: Combine variable and value by logical xor
  
  - 35: Add value to variable
  
  - 36: Substract value from variable
  
  - 37: Multiply variable by value
  
  - 38: Modulo variable by value
  
  - 38: Divide variable by value

- Image manipulations

  - 40: Write value to red channel
  
  - 41: Write value to green channel
  
  - 42: Write value to blue channel
  
  - 43: Write value to alpha channel
  
  - 45: Write variable content to red channel
 
  - 46: Write variable content to green channel
 
  - 47: Write variable content to blue channel

  - 48: Write variable content to alpha channel

The interpretor
===============

You can call the interpretor on command line using::

	kaforkl image

The default stating point will be the top left coordinate 0,0, but you may
specify other starting points using the parameter -s::

	kaforkl -s 12,45 image

You may specify an offset which will be removed from all input values. This 
will not reduce the number of available variables::

	kaforkl -s 12,45 -o 43 image

