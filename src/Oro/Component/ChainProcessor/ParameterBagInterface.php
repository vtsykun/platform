<?php

namespace Oro\Component\ChainProcessor;

/**
 * Represents a container for key/value pairs.
 */
interface ParameterBagInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Checks whether a parameter with the given name exists in the bag.
     *
     * @param string $key The name of a parameter
     *
     * @return bool
     */
    public function has($key);

    /**
     * Gets a parameter by name from the bag.
     *
     * @param string $key The name of a parameter
     *
     * @return mixed|null The parameter value or NULL if it does not exist in the bag
     */
    public function get($key);

    /**
     * Sets a parameter by name into the bag.
     *
     * @param string $key   The name of a parameter
     * @param mixed  $value The value of a parameter
     */
    public function set($key, $value);

    /**
     * Removes a parameter from the bag.
     *
     * @param string $key The name of a parameter
     */
    public function remove($key);

    /**
     * Sets a resolver for a parameter value.
     * The resolver will be executed only once when the parameter is requested at the first time.
     * This can be helpful for rare used parameters with time consuming value resolving.
     *
     * @param string                               $key      The name of a parameter
     * @param ParameterValueResolverInterface|null $resolver The value resolver
     */
    public function setResolver($key, ?ParameterValueResolverInterface $resolver);

    /**
     * Gets a native PHP array representation of the bag.
     *
     * @return array [key => value, ...]
     */
    public function toArray();

    /**
     * Removes all parameters from the bag.
     *
     * @return void
     */
    public function clear();
}
