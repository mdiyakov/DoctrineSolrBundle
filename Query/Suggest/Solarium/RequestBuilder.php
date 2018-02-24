<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium;

use Solarium\Core\Client\Request;
use Solarium\Core\Query\RequestBuilder as BaseRequestBuilder;
use Solarium\Core\Query\QueryInterface;

class RequestBuilder extends BaseRequestBuilder
{

    /**
     * @param QueryInterface|Query $query $query
     * @return Request
     */
    public function build(QueryInterface $query)
    {
        $request = parent::build($query);
        $request->addParam('suggest', 'true');
        $request->addParam('suggest.q', $query->getQuery());

        foreach ($query->getDictionaries() as $dictionary) {
            $request->addParam('suggest.dictionary', $dictionary);
        }

        $request->addParam('suggest.count', $query->getCount());
        $request->addParam('suggest.cfq', $query->getContextFieldQuery());

        return $request;
    }
}