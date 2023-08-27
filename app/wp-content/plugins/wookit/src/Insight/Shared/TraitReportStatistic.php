<?php


namespace WooKit\Insight\Shared;


use Exception;
use WooKit\Insight\Shared\Query\QueryBuilder;

trait TraitReportStatistic
{
    /**
     * @throws Exception
     */
    public function getReport(string $queryFilter, array $aAdditionalData = [], string $postID = ''):
    array
    {
        $responseClassName = $this->generateResponseClass($queryFilter);
        $queryClassName = $this->generateQueryClass($queryFilter);
        if (class_exists($queryClassName) && class_exists($responseClassName)) {
            $oResponse = new $responseClassName;
            $oResponse->setAdditional($aAdditionalData);

            /**
             * @var $oQueryClass QueryBuilder
             */

            $oQueryClass = (new $queryClassName);
            $aData = $oQueryClass->setTable($this->getTable())
                ->setAdditional($aAdditionalData)
                ->setPostID($postID)
                ->setSummary($this->getSummary())
                ->select()
                ->setPostType($this->getPostType())
                ->query($oResponse);

        } else {
            throw new Exception(esc_html__('Oops! this filter does not support by us', 'wookit'));
        }

        return $aData;
    }

}
