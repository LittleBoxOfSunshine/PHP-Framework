<?php

namespace bmca\container {

    require 'vendor/autoload.php';

    abstract class Object implements \bmca\exception\CatchableException{

        /**
         * This generic constructor enforces behaviours that improve code readability and reduce the likelyhood
         * of errors caused by dynamic typing mismatches. It enforces a "c" style constructor that provides
         * some of the functionally that would normally be achieved by overloading the constructor.
         *
         * The $args parameter contains an array of key => value pairs where the keys correspond to NULL properties
         * of the child class and the values to initialize them to. Should a key be given that doesn't have a
         * corresponding property to store it in, code execution is halted. This prevents errorneous extra data from
         * being added to an instance.
         *
         * If the $required parameter is provided, it is an array of strings that correspond to instance properties
         * that MUST be initialized for the constructor to run correctly. If any of the required inputs are not found
         * in $args code execution is halted. By default/if no $required parameter is provided, the constructor will
         * require that ALL instance variables be instantiated. Multiple valid required patterns may be provided (this
         * allows the CRUD interface load function to be defined more flexibly).
         *
         * If a key in the array $required is a string, the corresponding value is will be enforced to match the type
         * denoted by the key.
         *
         * The $fromUserInput parameter is an optional flag that signals the data contained in args comes from some
         * form of user input, rather than class definitions. Consequently, it is possible and expected that the data
         * may be malformed, but the calling code is equipped to handle that exception. So, the constructor returns
         * false as an error message instead of halting code execution.
         */

        public function __construct(array & $args = array(), array $required = array(), & $fromUserInput=false){
            // Initialize instance properties to match the key => value pairs of $args
            foreach($args as $key => $val){
                // Ensure the key is a valid instance property
                if(property_exists($this, $key))
                    $this->$key = $val;//$this->__set($key, $val); // use magic method __set so that private properties in child may be set
                // Key is not valid, delegate to malformedArgs()
                else
                    return $this->malformedArgs("The key $key is not a valid property of class: " . get_class($this), $fromUserInput);
            }

            // Use the provided properties, if none are provided, load all instance properties
            $properties = count($required) == 0 ? get_object_vars($this) : $required;

            // Flags for testing arrays
            $hasValidArray = false;
            $hasArrays = false;

            // Ensure that all required properties were successfully initialized
            foreach($properties as $key => $prop){

                // Multiple valid required patterns exist
                if($hasValidArray === false && is_array($prop)){// hasValidArray check is an optimization to unnecessary parsing (consider adding && count($prop) <= count($args) as optimization)
                    $hasArrays = true;

                    // Flag for if current array is valid (assume yes, loop will fix this if it is untrue)
                    $tmpValidArray = true;

                    foreach($prop as $subprop){
                        // If the required property was not initialized, delegate to malformedArgs()
                        if(!isset($this->$subprop)){
                            $tmpValidArray = false;
                            break; // Data is invalid, no reason to continue checking
                        }
                    }

                    // Check if array requirements were met
                    if($tmpValidArray === true)
                        $hasValidArray = true;
                }
                // Only one valid required pattern exists
                else if(!is_array($prop)){
                    // If the required property was not initialized, delegate to malformedArgs()
                    // or if a type constraint was provided and not met
                    if(!isset($this->$prop) || (is_string($key) && getType($this->$prop) != $key))
                        return $this->malformedArgs("The required property {$prop} was not initialized for class: " . get_class($this), $fromUserInput);
                }
            }

            // Delegate error to malformedArgs() if arrays were used and none of them were valid
            if($hasArrays === true && $hasValidArray === false)
                $this->malformedArgs('The required properties were not initialized for class: ' . get_class($this) . ' requirements are: ' .  var_export($required, true) , $fromUserInput);
        }

        // Function is called when constructor arguments are in
        private function malformedArgs($errorMessage, $fromUserInput=false){

            $errorMessage = 'ERROR: ' . $errorMessage . ' VAR_DUMP -> ' . var_export($this, true);

            // Calling function intends on handling the error
            if($fromUserInput === true) {
                \bmca\exception\Handler::catchableFatalException(NULL, $errorMessage, $this);
                return false;
            }
            // No error handling signaled, halt code execution and display the given error message
            else {
                \bmca\exception\Handler::fatalException($errorMessage);
            }
        }

        /// Function may be overridden to supply handler enum to catch appropriate fatal exceptions
        public static function getHandlerEnum()
        {
            return new \bmca\container\Enum();
        }

    }

}