<?php
/**
 * Magento profiler for requests to database
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Model\Resource\Db;

class Profiler extends \Magento\DB\Profiler
{
    /**
     * Default connection type for timer name creation
     */
    const TIMER_PREFIX = 'DB_QUERY';

    /**
     * Default connection type for timer name creation
     */
    const DEFAULT_CONNECTION_TYPE = 'database';

    /**
     * @var array Allowed query types
     */
    protected $_queryTypes = array('select', 'insert', 'update', 'delete');

    /**
     * Form and return timer name
     *
     * @param string $operation
     * @return string
     */
    protected function _getTimerName($operation)
    {
        // default name of connection type
        $timerName = \Magento\Model\Resource\Db\Profiler::DEFAULT_CONNECTION_TYPE;

        // connection type to database
        if (!empty($this->_type)) {
            $timerName = $this->_type;
        }

        // sql operation
        $timerName .= '_' . $operation;

        // database host
        if (!empty($this->_host)) {
            $timerName .= '_' . $this->_host;
        }

        return \Magento\Model\Resource\Db\Profiler::TIMER_PREFIX . ':' . $timerName;
    }

    /**
     * Parse query type and return
     *
     * @param string $queryText
     * @return string
     */
    protected function _parseQueryType($queryText)
    {
        $queryTypeParsed = strtolower(substr(ltrim($queryText), 0, 6));

        if (!in_array($queryTypeParsed, $this->_queryTypes)) {
            $queryTypeParsed = 'query';
        }

        return $queryTypeParsed;
    }

    /**
     * Starts a query. Creates a new query profile object (\Zend_Db_Profiler_Query)
     *
     * @param string $queryText SQL statement
     * @param integer $queryType OPTIONAL Type of query, one of the \Zend_Db_Profiler::* constants
     * @return integer|null
     */
    public function queryStart($queryText, $queryType = null)
    {
        $result = parent::queryStart($queryText, $queryType);

        if ($result !== null) {
            $queryTypeParsed = $this->_parseQueryType($queryText);
            $timerName = $this->_getTimerName($queryTypeParsed);

            $tags = array();

            // connection type to database
            $typePrefix = '';
            if ($this->_type) {
                $tags['group'] = $this->_type;
                $typePrefix = $this->_type . ':';
            }

            // sql operation
            $tags['operation'] = $typePrefix . $queryTypeParsed;

            // database host
            if ($this->_host) {
                $tags['host'] = $this->_host;
            }

            \Magento\Profiler::start($timerName, $tags);
        }

        return $result;
    }

    /**
     * Ends a query. Pass it the handle that was returned by queryStart().
     *
     * @param int $queryId
     * @return string|void
     */
    public function queryEnd($queryId)
    {
        $result = parent::queryEnd($queryId);

        if ($result != self::IGNORED) {
            /** @var \Zend_Db_Profiler_Query $queryProfile */
            $queryProfile = $this->_queryProfiles[$queryId];
            $queryTypeParsed = $this->_parseQueryType($queryProfile->getQuery());
            $timerName = $this->_getTimerName($queryTypeParsed);

            \Magento\Profiler::stop($timerName);
        }

        return $result;
    }
}
