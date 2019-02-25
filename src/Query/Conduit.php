<?php

namespace Bego\Query;

class Conduit
{
    protected $_db;

    protected $_options = [];

    protected $_log = [];

    public function __construct($db, $options)
    {
        $this->_db = $db;
        $this->_options = $options;
    }

    public function execute($offset = null)
    {
        $options = $this->_getOptions($offset);

        $result = $this->_db->client()->query($options);

        $this->_log = [
            'offset'    => isset($options['ExclusiveStartKey']) ? $options['ExclusiveStartKey'] : null,
            'evaluated' => isset($result['LastEvaluatedKey']) ? $result['LastEvaluatedKey'] : null,
            'count'     => $result['Count']
        ];

        return $result;
    }

    public function option($key)
    {
        if (!array_key_exists($key, $this->_options)) {
            return null;
        }

        return $this->_options[$key];
    }

    public function getLastLog()
    {
        return $this->_log;
    }

    protected function _getOptions($offset)
    {
        if (!$offset) {
            return $this->_options;
        }

        return array_merge(
            $this->_options, ['ExclusiveStartKey' => $offset]
        );
    }
}
