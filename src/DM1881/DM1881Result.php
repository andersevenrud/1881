<?php
/*!
 * 1881 PHP API Library
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Anders Evenrud <andersevenrud@gmail.com>
 */

namespace DM1881;

use stdClass;

/**
 * 1881 API Interface - Result Object
 * @author Anders Evenrud <andersevenrud@gmail.com>
 * @link https://developer.1881.no/hc/no/sections/203250705-Dokumentasjon
 * @version 0.5
 */
class DM1881Result
{
    protected $queryTime;
    protected $numberOfHits = 0;
    protected $profile;
    protected $results = [];

    /**
     * Constructor
     *
     * @since 0.5
     * @param Integer $qt Query time
     * @param Integer $num Number of results
     * @param String $prof Profile name
     * @param Array $hits Results
     */
    protected function __construct($qt, $num, $prof, $hits)
    {
        $this->queryTime = $qt;
        $this->numberOfHits = $num;
        $this->profile = $prof;
        $this->results = $hits;
    }

    /**
     * Create a new instance from JSON
     *
     * @since 0.5
     * @param stdClass $json JSON Data
     * @return DM1881Result
     */
    static public function create(stdClass $json)
    {
        return new static(
            $json->QueryTimeMilliSeconds,
            $json->NumberOfHits,
            $json->Profile,
            $json->Hits
        );
    }

    /**
     * Gets the query time
     *
     * @since 0.5
     * @return Integer
     */
    public function queryTime()
    {
        return $this->queryTime;
    }

    /**
     * Gets the number of results
     *
     * @since 0.5
     * @return Integer
     */
    public function count()
    {
        return $this->numberOfHits;
    }

    /**
     * Gets the profile name
     *
     * @since 0.5
     * @return Integer
     */
    public function profile()
    {
        return $this->profile;
    }

    /**
     * Gets the results
     *
     * @since 0.5
     * @return Array
     */
    public function all()
    {
        return $this->results;
    }

}
