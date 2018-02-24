<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium;

use Solarium\Core\Query\Query as BaseQuery;

class Query extends BaseQuery
{
    /**
     * Default options
     *
     * @var array
     */
    protected $options = array(
        'handler'       => 'suggest',
        'resultclass'   => 'Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Result\Result',
        'termclass'     => 'Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Result\Term',
        'omitheader'    => true,
    );


    /**
     * Get type for this query
     *
     * @return string
     */
    public function getType()
    {
        return 'suggester';
    }

    /**
     * Get a requestbuilder for this query
     *
     * @return RequestBuilder
     */
    public function getRequestBuilder()
    {
        return new RequestBuilder;
    }

    /**
     * Get a response parser for this query
     *
     * @return ResponseParser
     */
    public function getResponseParser()
    {
        return new ResponseParser;
    }

    /**
     * Set query option
     *
     * Query to spellcheck
     *
     * @param  string $query
     * @return self   Provides fluent interface
     */
    public function setQuery($query)
    {
        return $this->setOption('query', $query);
    }

    /**
     * Get query option
     *
     * @return string|null
     */
    public function getQuery()
    {
        return $this->getOption('query');
    }

    /**
     * Set dictionary option
     *
     * The name of the dictionary to use
     *
     * @param  string $dictionary
     * @return self   Provides fluent interface
     */
    public function addDictionary($dictionary)
    {
        $dictionaries = $this->getOption('dictionaries');
        if (!$dictionaries) {
            $dictionaries = [];
        }
        $dictionaries[] = $dictionary;

        return $this->setOption('dictionaries', $dictionaries);
    }

    /**
     * Get dictionary option
     *
     * @return string[]|null
     */
    public function getDictionaries()
    {
        return $this->getOption('dictionaries');
    }

    /**
     * Set count option
     *
     * The maximum number of suggestions to return
     *
     * @param  int  $count
     * @return self Provides fluent interface
     */
    public function setCount($count)
    {
        return $this->setOption('count', $count);
    }

    /**
     * Get count option
     *
     * @return int|null
     */
    public function getCount()
    {
        return $this->getOption('count');
    }

    /**
     * @param string $contextFilterQuery
     * @return \Solarium\Core\Configurable
     */
    public function setContextFieldQuery($contextFilterQuery)
    {
        return $this->setOption('context_filter_query', $contextFilterQuery);
    }

    /**
     * Get count option
     *
     * @return string|null
     */
    public function getContextFieldQuery()
    {
        return $this->getOption('context_filter_query');
    }
}