<?php namespace Alquemedia_PostREST;
use Alquemedia_PostREST\Components\Database\Database;
use Alquemedia_PostREST\Components\Models\Model;
use Alquemedia_PostREST\Components\SQL\Select_Statement;

/**
 * Class RESTful_API
 * @package Alquemedia_PostREST
 *
 * A RESTful API
 */
class RESTful_API {

    /**
     * @var JSON_File configuration
     */
    private $config;

    /**
     * @var \PDO
     */
    private $db;

    /**
     * @var array result from API operation
     */
    private $result = [];

    /**
     * @var Time when this class starts to run
     */
    private $startTime;

    /**
     * RESTful_API constructor.
     */
    public function __construct() {

        $this->initTimer();

        // if Connected to database
        if ( ($this->config = $this->configure()) &&

            ( $this->db = $this->connect() ) )

                $this->processRequest();

    }

    /**
     * @return bool true if configured
     */
    private function configure(){

        return $this->loadJSON('postrest');

    }

    /**
     * process the request
     */
    private function processRequest(){

        $apiRoot = $this->config->get('api-root');

        $uriPart1 = $this->uriPart(1);

        if ( $uriPart1 != $this->config->get('api-root'))

            $this->setError("Expected API root /$apiRoot, but got $uriPart1");

        else {

            $this->processModel( $this->uriPart(2) );

            $this->result['metaData'] = $this->metaData();

        }

    }

    /**
     * @param string $modelName
     */
    private function processModel( $modelName )
    {
        try
        {
            if ( ! $modelName ){

                $this->setError("Expected a model name in the URL");

                return;
            }


            $recordId = $this->uriPart(3);

            if ( !$recordId)
            {
                if ( ! is_null(Request_URI::getParam("pageSize")))
                {
                    $errorMsg = "You must use pageSize with pageNumber, and both must be positive integers";

                    Validator::notBlank(Request_URI::getParam("pageSize"), $errorMsg);
                    Validator::isPositiveInt(Request_URI::getParam("pageSize"), $errorMsg);
                    Validator::notNull(Request_URI::getParam("pageNumber"), $errorMsg);
                    Validator::notBlank(Request_URI::getParam("pageNumber"), $errorMsg);
                    Validator::isPositiveInt(Request_URI::getParam("pageNumber"), $errorMsg);

                    $this->result['models'] = $this->getModels(
                        $modelName,
                        Request_URI::getParam("pageSize"),
                        Request_URI::getParam("pageNumber")
                    );
                }
                else
                {
                    $this->result['models'] = $this->getModels($modelName);
                }
            }
            else
            
                $this->result[ $modelName ] = (new Model($modelName,$recordId))->asArray();

        }
        catch (\Exception $e)
        {
            $this->setError($e->getMessage());
        }
    }

    /**
     * @param $modelName
     * @return array
     */
    private function getModels( $modelName, $pageSize = false, $pageNumber = false ){

        if ($pageSize)
        {
            $sql = (string) (new Select_Statement($modelName))->limit($pageSize, $pageNumber);
        }
        else
        {
           $sql = (string) (new Select_Statement($modelName))->defaultLimit();
        }

        if ( ! ($result = (new Database())->query($sql)) )

            $this->setError("$modelName: No such Model found");

        return $result ? $this->addCreatedSince($result->fetchAll( \PDO::FETCH_ASSOC )):[];

    }

    /**
     * @return array of meta data
     */
    private function metaData(){

        $metaData = [ 'api-root' => $this->config->get('api-root'),];

        if ( $this->config->get('show-server-vars'))

            $metaData['server-vars'] = $_SERVER;

        $metaData["execution-time"] = $this->getExecutionTime();

        return $metaData;
    }

    /**
     * @return \PDO
     */
    private function connect(){

        $dbConfig = $this->loadJSON('database');

        $db = new \PDO((string) new Data_Source_Name($dbConfig),$dbConfig->username,$dbConfig->password);

        return $db;

    }


    /**
     * @param $jsonKey
     * @return JSON_File|null
     */
    private function loadJSON( $jsonKey ){

        $jsonFile = new JSON_File($jsonKey);

        if ( ! $jsonFile->exists() ){

            $this->result = [

                'result' => 'error',

                'error' => $jsonFile->filePath(). ": JSON file not found."

            ];

            return null;
        }

        return $jsonFile;

    }

    /**
     * Show as JSON
     */
    public function toJSON(){

        header('Content-Type: application/json');

        echo json_encode( $this->result );

    }

    /**
     * @param $partNumber
     * @return string
     */
    private function uriPart( $partNumber ){

        return (new Request_URI())->part( $partNumber );

    }

    /**
     * Set API error
     * @param string $error
     */
    private function setError( $error ){

        $this->result['result'] = 'error';

        $this->result['error'] = $error;

    }

    /**
    * Initialize the start time
    */
    private function initTimer()
    {
        $this->startTime = microtime(true); 
    }

    /**
    * Get the elapsed execution time
    *
    * @param int $precision
    * @return string
    */
    private function getExecutionTime($precision = 4)
    {
        return round((microtime(true) - $this->startTime), $precision)."s";
    }

    /**
    * Add field 'created_since' to the result set
    * 
    * @param array $data
    * @return array
    */
    private function addCreatedSince($data)
    {
        if (count($data))
        {
            for ($i = count($data) - 1; $i >= 0; $i--)
            {
                if (isset($data[$i]["created_date"]))
                {
                    $data[$i]["created_since"] = (string) new TimeSince($data[$i]["created_date"]);
                }
            }
        }

        return $data;
    }
}