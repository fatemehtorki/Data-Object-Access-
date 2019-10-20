<?php
/**
 * Created by PhpStorm.
 * User: Fatima
 * Date: 12/15/2018
 * Time: 1:39 PM
 
 */

class Dbase
{
    const HOST = 'localhost';
    const DB_NAME = 'dataBaseName';   #dataBaseName
    const USER = 'root';              #USERName
    const PASS = '';				  #PASS

    private $connection = null;
    private static $instance = null;

    private function __construct()
    {
        $DSN = 'mysql:host=' . self::HOST . ';dbname=' . self::DB_NAME;

        try
        {
            $this->connection = new PDO($DSN, self::USER, self::PASS);
            $this->connection->query("SET NAMES UTF8");
        } catch (PDOException $e)
        {
            echo $e;
        }

    }


    /*
     * this method returns the connections if other classes want to use it
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new Dbase();
            return self::$instance;
        } else
        {
            return self::$instance;

        }
    }

    public function getCon()
    {
        return $this->connection;
    }


    /*
     * close connection manually for more efficiency
     */

    public function closeConnection()
    {
        $this->connection = null;
    }

}

 /*
     *This method creates a modelObject file for each table in the database
     */
function productFileModel(){

    $conn=Dbase::getInstance()->getCon();
    $queryone="SELECT TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES  
                WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='pro_doc'";

    if( !$result1=$conn->query($queryone))
        echo $result1->errorInfo();
    if ($result1->rowCount()){

        foreach ($result1->fetchAll(PDO::FETCH_NUM) as $row1){

            $i=0;
            $forWrite= fopen($row1[$i].'Model.php',"w");


            $querytwo="select column_name from INFORMATION_SCHEMA.COLUMNS
                                    where TABLE_SCHEMA='pro_doc'  AND TABLE_NAME = '$row1[$i]' ";

            if( $result2=$conn->query($querytwo)){
                fwrite($forWrite,"<?php class ". $row1[$i] ."{");
                foreach ($result2->fetchAll(PDO::FETCH_NUM) as $row2){
                    $j=0;
                    fwrite($forWrite,"\n public $"."$row2[$j]".";\n");

                    $j++;
                }
                fwrite($forWrite,"} \n ?>");

            }

            $i++;
        }
        fclose($forWrite);
        Dbase::getInstance()->closeConnection();
		echo "your Model Object files successfully created";'


    }else die("eeerrrrroooorrr");
}

/*
     *This method creates a modelAccess file for each table in the database
*/
	 
function productFileAccess(){
	
    $conn=Dbase::getInstance()->getCon();
	
    $queryone="SELECT TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES  
                WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='pro_doc'";
				
    $result1=$conn->query($queryone);
    //if( !$result1=$conn->query($queryone))
    //  echo $result1->errorInfo();
    if ($result1->rowCount()) {

        foreach ($result1->fetchAll(PDO::FETCH_NUM) as $row1) {

            $i=0;

            $forWriteFA = fopen($row1[$i] .'Access.php', "w");

            $querytwo = "select column_name from INFORMATION_SCHEMA.COLUMNS
                                    where TABLE_SCHEMA='pro_doc'  AND TABLE_NAME = '$row1[$i]' ";

            if( $result2=$conn->query($querytwo)) {
                $items = null;
                $questions = null;
                $mark = null;
                $q = 1;
                foreach ($result2->fetchAll(PDO::FETCH_NUM) as $a) {

                    $s = 0;

                    if ($items == null) {

                        $items =  $a[$s];
                        $questions = "?";
                        $first_coul=$items;


                    }else{

                        $items = $items .", ".  $a[$s];
                        $questions = $questions . ", ?";
                    }

                    $mark = $mark . "\$q->bindValue(".$q ." ,\$model->".$a[$s].");\n\t\t";
                    $s++;
                    $q++;
                }


                fwrite($forWriteFA ,"
<?php \n\nclass ". $row1[$i] ."Access { 

	private \$database;

	public function __construct(\$db){
	
	\$this->database=\$db; 
	}

					");

                // for delete

                fwrite($forWriteFA ,"

	public function delete(\$model){

	        \$query=\"DELETE FROM ".$row1[$i]." WHERE ".$first_coul."= ?\";

	        \$q = \$this->database->prepare(\$query);

	        \$q->bindValue(1,\$model->".$first_coul.");

	        if(\$q->execute())
	        {
	            return true;
	        }else{
	            echo \"failure\";
	        }

	    }

		            	");

                // for select
                fwrite($forWriteFA ,"
	public function selectById(\$model)
	    {
	        \$query = \"SELECT * FROM ".$row1[$i]." WHERE ".$first_coul."= ?\";

	        \$q = \$this->database->prepare(\$query);

	        \$q->bindValue(1,\$model-> ".$first_coul.");
	        \$q->execute();

	        \$out = \$q->fetch(PDO::FETCH_ASSOC);
	        return \$out;
	    }
		            	\n\n");




                //for get latest
                fwrite($forWriteFA ,"
    public function getLatest".$row1[$i]."(\$start=0,\$end=20)
    {
                \$q=DB::getInstance()->getCon()->query(\"SELECT * FROM  $row1[$i] ORDER BY $first_coul DESC limit \$start,\$end\");
                
                \$out=\$q->fetchAll(PDO::FETCH_ASSOC);
                
                return \$out;
                
                }");


                // for insert

                fwrite($forWriteFA ,"

	public function insert(\$model)
	    {
	        \$query = \"INSERT INTO ".$row1[$i]." (".$items.") VALUES (".$questions.") \";

	        \$q = \$this->database->prepare(\$query);


	       ".$mark."


	        \$q->execute();

	        \$m = new " . $row1[$i] . "Model();
	        \$m->$first_coul = \$this->database->lastInsertId();

	        \$out = \$this->selectById(\$m);
	        return \$out;
	    }

		            	");


                fwrite($forWriteFA ,"} \n?>");
            }

            $i++;


        }
        fclose($forWriteFA);
        Dbase::getInstance()->closeConnection();
		echo "your Model Access files successfully created";'
    }else  echo $result1->errorInfo();


}





var_dump(productFileModel());
var_dump(productFileAccess());