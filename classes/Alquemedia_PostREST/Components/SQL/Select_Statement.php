<?php
/**
 * Created by PhpStorm.
 * User: dgreenberg
 * Date: 10/6/16
 * Time: 10:06 PM
 */

namespace Alquemedia_PostREST\Components\SQL;


use Alquemedia_PostREST\String_Object;

/**
 * Class Select_Statement
 * @package Alquemedia_PostREST\Components\SQL
 *
 * A SELECT Statement
 */
class Select_Statement extends String_Object{

    /**
     * Select_Statement constructor.
     * @param $modelName
     * @param int $recordId
     */
    public function __construct( $modelName, $recordId = 0 ) {

        $whereClause = (string) new WHERE_Clause($modelName,$recordId);

        $this->stringRepresentation = "SELECT * FROM $modelName WHERE $whereClause";

    }

    /**
    * Set a limit for rows in queries
    *
    * @param int $pageSize
    * @param int $pageNumber
    * @return Select_Statement instance
    */
    public function limit($pageSize, $pageNumber = 1)
    {
        $this->stringRepresentation .= " LIMIT $pageSize OFFSET ". ($pageSize * --$pageNumber);

        return $this;
    }

    /**
    * Set a default limit for rows in queries
    *
    * @return Select_Statement instance
    */
    public function defaultLimit()
    {
        return $this->limit( (new SQL_Settings())->selectLimit() );
    }
}