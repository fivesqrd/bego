<?php

namespace Bego\Query;

class Paginator
{
    protected $_conduit;

    protected $_state = [
        'offset' => null, 
        'trips'  => 0
    ];

    protected $_pageLimit = 1;

    protected $_trace = [];

    public function __construct($conduit, $limit = 1, $offset = null)
    {
        $this->_conduit = $conduit;
        $this->_state['offset'] = $offset;
        $this->_pageLimit = $limit;
    }

    public function query()
    {
        $aggregator = new Aggregator();

        $start = microtime(true);

        do {
            /* Execute query */
            $result = $this->_conduit->execute($this->_state['offset']);

            /* Add this trip to the trace */
            $this->_trace[] = $this->_conduit->getLastLog();

            /* Update the paginator's state */
            $this->_state['offset'] = $this->_getNewOffset($result);
            $this->_state['trips'] += 1;

            /* Append last result to previous */
            $aggregator->append($result);

        } while (
            $this->_isAnotherTripRequired($this->_state) === true
        );

        $meta = [
            'X-Query-Time'  => microtime(true) - $start,
            'X-Query-Count' => $this->_state['trips']
        ];

        return array_merge(
            $aggregator->result(), $meta
        );
    }

    protected function _isAnotherTripRequired($state)
    {
        /* If no more results available, don't attempt another trip */
        if ($this->_isPageLimitReached($state['trips'])) {
            return false;
        }

        /* If no more results available, don't attempt another trip */
        if ($state['offset'] === false) {
            return false;
        }

        return true;
    }

    protected function _isPageLimitReached($trips)
    {
        if (!$this->_pageLimit) {
            return false;
        }

        /* If a page limit is definedd ensure that we don't exceed it */
        if ($trips < $this->_pageLimit) {
            return false;
        }

        return true;
    }

    protected function _getNewOffset($result)
    {
        if (isset($result['LastEvaluatedKey'])) {
            return $result['LastEvaluatedKey'];
        } 

        /* No offset */
        return false;
    }

    public function getTrace()
    {
        return $this->_trace;
    }

    /**
     * The last key reported by DyanmoDb. Important for pagination
     */ 
    public function getLastEvaluatedKey()
    {
        return $this->_state['offset'];
    }
}
