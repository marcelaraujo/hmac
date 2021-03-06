<?php

namespace Mardy\Hmac\Adapters;

/**
 * Hash Adapter
 *
 * @package Mardy\Hmac\Adapters
 * @author Michael Bardsley @mic_bardsley
 */
class Hash extends AbstractAdapter
{
    /**
     * Iterate and hash the data multiple times
     *
     * @param string $data the string of data that will be hashed
     * @param string $salt
     * @param int $iterations the number of iterations required
     * @return string
     */
    protected function hash($data, $salt = '', $iterations = 10)
    {
        $hash = $data;
        foreach (range(1, $iterations) as $i) {
            $hash = hash($this->algorithm, $hash . md5($i) . $salt);
        }

        return $hash;
    }

    /**
     * Sets the algorithm that will be used by the encoding process
     *
     * @param string $algorithm
     * @return \Mardy\Hmac\Adapters\Hash
     * @throws \InvalidArgumentException
     */
    protected function setAlgorithm($algorithm)
    {
        $algorithm = strtolower($algorithm);
        if (! in_array($algorithm, hash_algos())) {
            throw new \InvalidArgumentException("The algorithm ({$algorithm}) selected is not available");
        }
        $this->algorithm = $algorithm;

        return $this;
    }
}
