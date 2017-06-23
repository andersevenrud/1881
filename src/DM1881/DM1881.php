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

use DM1881\DM1881Exception;
use DM1881\DM1881Result;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use Firebase\JWT\JWT;

use DateTime;
use InvalidArgumentException;

/**
 * 1881 API Interface
 * @author Anders Evenrud <andersevenrud@gmail.com>
 * @link https://developer.1881.no/hc/no/sections/203250705-Dokumentasjon
 * @version 0.5
 */
class DM1881
{

    /**
     * @var The 1881 endpoints by env
     */
    static protected $environments = [
        'dev' => 'https://api-dev.1881.no/search/',
        'stage' => 'https://api-test.1881.no/search/',
        'prod' => 'https://api.1881.no/search/'
    ];

    /**
     * @var Valid request arguments
     */
    static protected $validArguments = [
        'QueryString',
        'ReturnAllContactPoints',
        'Rows',
        'Offset',
        'Filters',
        'Facets',
        'SortBy'
    ];

    /**
     * @var Configuration
     */
    protected $configuration = [
        'debug' => false,
        'version' => 'v1',
        'environment' => 'dev',
        'issuer_id' => 'VK1881Issuer',
        'audience_id' => 'VK1881Services',
        'base_uri' => '', // autoresolved
        'client_id' => null,
        'username' => null,
        'secret' => null
    ];

    /**
     * Create a new instance
     *
     * @throws InvalidArgumentException
     * @param Array $configuration The configuration
     */
    public function __construct(Array $configuration = [])
    {
        $this->configuration = array_merge($this->configuration,
            $configuration);

        // Autoresolve base uri if not set
        if ( empty($this->configuration['base_uri']) ) {
            $this->configuration['base_uri'] = static::$environments[
                $this->configuration['environment']
            ];
        }

        // Make sure no empty configurations
        foreach ( $this->configuration as $k => $v ) {
            if ( empty($v) && ($v !== false) ) {
                throw new InvalidArgumentException('Missing configuration: ' . $k);
            }
        }

        // Append API version if required
        $version = $this->configuration['version'];
        $this->configuration['base_uri'] .= $version . '/';
    }

    /**
     * Perform a request
     *
     * @since 0.5
     * @throws DM1881Exception
     * @param String $endpoint The Endpoint
     * @param Array $args Endpoint arguments
     * @throws TransferException
     * @return Response
     */
    protected function request($endpoint, Array $args = [])
    {
        // This is our JWT payload
        $now = new DateTime();
        $exp = (new DateTime())->modify('+30 days');

        $payload = [
            'VK1881Identity' => $this->configuration['username'],
            'iss' => $this->configuration['issuer_id'],
            'aud' => $this->configuration['audience_id'],
            'exp' => $exp->format('U'),
            'nbf' => $now->format('U')
        ];

        // Init HTTP client
        $client = new Client([
            'base_uri' => $this->configuration['base_uri'],
            'verify' => $this->configuration['environment'] === 'prod'
        ]);

        // Perform request
        try {
            $jwt = JWT::encode($payload, $this->configuration['secret']);

            $response = $client->request('GET', $endpoint, [
                'debug' => $this->configuration['debug'],
                'query' => $args ?: [],
                'headers' => [
                    'X-VK1881-API-CLIENT' => $this->configuration['client_id'],
                    'Authorization' => 'JWT ' . $jwt
                ]
            ]);

            $data = json_decode((string)$response->getBody());

            return DM1881Result::create($data);
        } catch ( ClientException $e ) {
            $ne = new DM1881Exception($e->getMessage());
            $ne->setClientException($e);

            throw $ne;
        }

        return null;
    }

    /**
     * Builds and checks final arguments array
     *
     * @since 0.5
     * @throws InvalidArgumentException
     * @param Array $args Arguments
     * @param String $q If string is given, append it to the result
     * @return Array
     */
    protected function checkArguments(Array $args, $q = null)
    {
        $finalArgs = $q ? ['QueryString' => $q] : [];
        $finalArgs = array_merge($finalArgs, $args);

        // Make sure no invalid arguments are present
        foreach ( array_keys($finalArgs) as $a ) {
            if ( !in_array($a, static::$validArguments) ) {
                throw new InvalidArgumentException('Invalid argument: ' . $a);
            }
        }

        return $finalArgs;
    }

    /**
     * Wrapper for performing searches
     *
     * @since 0.5
     * @throws DM1881Exception
     * @throws InvalidArgumentException
     * @param String $e Endpoint
     * @param String $q Query String
     * @param Array $args Arguments
     * @return DM1881Result
     */
    protected function _search($e, $q, Array $args = [])
    {
        $isPhone = $e === 'phonenumber';
        $finalArgs = $this->checkArguments($args, $isPhone ? null : $q);

        return $this->request($e, $finalArgs);
    }

    /**
     * Searches for persons and companies by query
     *
     * @since 0.5
     * @throws DM1881Exception
     * @throws InvalidArgumentException
     * @param String $q Query String
     * @return DM1881Result
     */
    public function search($q, Array $args = [])
    {
        return $this->_search('unit', $q, $args);
    }

    /**
     * Searches for persons by query
     *
     * @since 0.5
     * @throws DM1881Exception
     * @throws InvalidArgumentException
     * @param String $q Query String
     * @return DM1881Result
     */
    public function searchPerson($q, Array $args = [])
    {
        return $this->_search('person', $q, $args);
    }

    /**
     * Searches for companies by query
     *
     * @since 0.5
     * @throws DM1881Exception
     * @throws InvalidArgumentException
     * @param String $q Query String
     * @return DM1881Result
     */
    public function searchCompany($q, Array $args = [])
    {
        return $this->_search('company', $q, $args);
    }

    /**
     * Search by phone number
     *
     * @since 0.5
     * @throws DM1881Exception
     * @throws InvalidArgumentException
     * @param String $q Phone number
     * @return DM1881Result
     */
    public function searchPhone($q, Array $args = [])
    {
        $washed = preg_replace('/[^0-9]/', '', $q);

        return $this->_search('phonenumber', $q, $args);
    }

    /**
     * Gets all metadata
     *
     * @since 0.5
     * @throws DM1881Exception
     * @return DM1881Result
     */
    public function meta()
    {
        return [
            'Filter' => $this->metaFilters(),
            'SortBy' => $this->metaSorters(),
            'Facet' => $this->metaFacets()
        ];
    }

    /**
     * Gets a list of all Filter that can be used on a search
     *
     * @since 0.5
     * @throws DM1881Exception
     * @return DM1881Result
     */
    public function metaFilters()
    {
        return $this->request('info/filter');
    }

    /**
     * Gets a list of all SortBy that can be used on a search
     *
     * @since 0.5
     * @throws DM1881Exception
     * @return DM1881Result
     */
    public function metaSorters()
    {
        return $this->request('info/sortby');
    }

    /**
     * Gets a list of all Facet that can be used on a search
     *
     * @since 0.5
     * @throws DM1881Exception
     * @return DM1881Result
     */
    public function metaFacets()
    {
        return $this->request('info/facet');
    }
}

