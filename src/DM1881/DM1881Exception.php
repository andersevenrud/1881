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

use GuzzleHttp\Exception\ClientException;

use Exception;

/**
 * Generic Exception for DM1881
 *
 * Has a reference to the Guzzle Http Client Exception
 * So you can get the contents etc.
 */
class DM1881Exception extends Exception
{
    protected $clientException;

    /**
     * Set the HTTP Client Exception
     *
     * @param ClientException $ce Exception
     * @return void
     */
    public function setClientException(ClientException $ce)
    {
        $this->clientException = $ce;
    }

    /**
     * Get the HTTP Client exception
     *
     * @return ClientException
     */
    public function getClientException()
    {
        return $this->clientException;
    }

}

