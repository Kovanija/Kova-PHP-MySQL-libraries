<?php

require "iCRUD.interface.php";
require "Log.class.php";
class MySQL implements iCRUD
{

    private $conn;
    private $servername;
    private $dbname;
    private $username;
    private $password;

    public static $instances = 0;
    public static $connections = 0;


    function __construct($servername, $dbname, $username, $password)
    {
        ++self::$instances;
        $filename = "log.txt";
        system('attrib +H ' . escapeshellarg($filename));
        $this->servername = $servername;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
    }

    function __destruct()
    {
        --self::$instances;
    }

    static function connection_status()
    {
        return "\nConnection status : \nYou have " . self::$instances . " active instances!\nYou have " . self::$connections . " active connections!";
    }

    function get_info($action)
    {
        switch ($action) {
            case "ERRMODE" :
                    $value = $this->conn->getAttribute(constant("PDO::ATTR_$action"));
                if ($value == 0)
                    return "ERROR_MODE_SILENT";
                else if ($value == 1)
                    return "ERROR_MODE_WARNING";
                else if ($value == 2)
                    return "ERROR_MODE_EXCEPTION";
                break;

            case "ERRMODE_ALL" :
                return (
                "ERROR_MODE_SILENT setting error codes\n
                 ERROR_MODE_WARNING raises warnings\n
                 ERROR_MODE_EXCEPTION throws exceptions\n
                 To check which one is active, use getInfo('ERRMODE')"
            );
                break;

            case "CASE" :
                $value = $this->conn->getAttribute(constant("PDO::ATTR_$action"));
                if ($value == 0)
                    return "CASE_NATURAL";
                else if ($value == 1)
                    return "CASE_UPPER";
                else if ($value == 2)
                    return "CASE_LOWER";
                break;

            case "CASE_ALL" :
                return ("
                    CASE_LOWER forces column names to lower case\n
                    CASE_NATURAL leaves column names as returned by the database driver\n
                    CASE_UPPER forces column names to upper case\n
                     To check which one is active, use getInfo('CASE')");
                break;

            case "CLIENT_VERSION" :
                return $this->conn->getAttribute(constant("PDO::ATTR_$action")) . "\n";
                break;

            case "CONNECTION_STATUS";
                return $this->conn->getAttribute(constant("PDO::ATTR_$action")) . "\n";
                break;

            case "SERVER_INFO" :
                return $this->conn->getAttribute(constant("PDO::ATTR_$action")) . "\n";
                break;

            case "SERVER_VERSION" :
                return $this->conn->getAttribute(constant("PDO::ATTR_$action")) . "\n";
                break;

            case "ACCESS_INFO" :
                return("
                       SERVER NAME : $this->servername\n
                       USER NAME : $this->username\n
                       PASSWORD : $this->password\n
                       DATABASE : $this->dbname
                        ");
                break;

            case "AUTHOR" :
                return("
                     Author : Nemanja Kovacevic\n
                     Facebook : https://www.facebook.com/nemanja.kovacevic.923\n
                     Licence : Open source\n
                     Created on : jan 2017
                        ");
                break;

            default :
                return("
                     Wrong action\n
                     Actions you can check : 
                       - ERRMODE
                       - CASE
                       - CLIENT_VERSION
                       - CONNECTION_STATUS
                       - SERVER_INFO
                       - SERVER_VERSION    
                        ");
        }
    }

    function set_info($key, $value)
    {
        $file_handler = new Log();
        switch ($key) {
            case "ERRMODE" :
                if ($value == "ERROR_MODE_SILENT") {
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $file_handler->write("!SUCCESS : ERROR MODE CHANGED TO SILENT MODE");

                } else if ($value == "ERROR_MODE_WARNING") {
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $file_handler->write("!SUCCESS : ERROR MODE CHANGED TO WARNING MODE");

                } else if ($value == "ERROR_MODE_EXCEPTION") {
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $file_handler->write("!SUCCESS : ERROR MODE CHANGED TO EXCEPTION MODE");

                } else
                    print_r("!ERROR : Error mode you want doesn't exist, please check getInfo('ERRMODE_ALL') for available error modes");
                break;

            case "CASE" :
                if ($value == "CASE_LOWER") {
                    $this->conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                    $file_handler->write("!SUCCESS : CASE MODE CHANGED TO LOWER MODE");

                } else if ($value == "CASE_NATURAL") {
                    $this->conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                    $file_handler->write("!SUCCESS : CASE MODE CHANGED TO NATURAL MODE");

                } else if ($value == "CASE_UPPER") {
                    $this->conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
                    $file_handler->write("!SUCCESS : CASE MODE CHANGED TO UPPER MODE");

                } else
                    return "Case mode you want doens't exist, please check getInfo('CASE_ALL') for available case modes";
        }

    }

    function database_close()
    {
        $file_handler = new Log();
        if ($this->conn != null) {
            $file_handler->write("!SUCCESS : You have closed database connection with " . $this->dbname . "");
            $this->conn = null;
            --self::$connections;
        } else if ($this->conn == null) {
            $file_handler->write("!ERROR : You can't close database connection that is already closed!");
        }
    }

    function database_connection()
    {
        if ($this->conn == null) {
            $file_handler = new Log();

            try {
                $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $file_handler->write("!SUCCESS : You have connected to database " . $this->dbname . " successfully");
                ++self::$connections;
            } catch (PDOException $e) {
                $file_handler->write("!ERROR : Connection failed to database " . $this->dbname . " " . $e->getMessage());
            }
        } else if ($this->conn != null) {
            $file_handler = new Log();
            $file_handler->write("!ERROR : You can't connect, you are already connected with this instance!");
        }

    }

        //SQL PREVENT ( helping hands along with PDO prepare statement )
        function is_safe($values)
        {
            $val = explode(" ", $values);
            $lower_val = array();
            foreach ($val as $toLower)
            {
                $lower_val[] = strtolower($toLower);
            }
            var_dump($lower_val);

            if(in_array("or", $lower_val) || in_array("delete", $lower_val) || in_array("drop", $lower_val)
                || in_array("truncate", $lower_val) || in_array("select", $lower_val) || in_array("insert", $lower_val)
                || in_array("where", $lower_val) || in_array("and", $lower_val) || in_array("between", $lower_val)
                || in_array("update", $lower_val) || in_array("create", $lower_val) || in_array("alter", $lower_val))
                    return true;
                else
                    return false;
        }


        function db_select($array_columns, $array_tables, $array_where)
        {
            $file_handler = new Log();

            if($this->conn != null)
            {
                $array_col = implode(",", $array_columns);
                $array_tab = implode(",", $array_tables);

                if(isset($array_where))
                    $array_wh = implode(" AND ", $array_where);


                if(isset($array_where))
                    $sql = $this->conn->prepare("SELECT $array_col FROM $array_tab WHERE $array_wh");
                else
                    $sql = $this->conn->prepare("SELECT $array_col FROM $array_tab");

                try{
                    $sql->execute();
                    $rows = $sql->fetchAll();
                    $file_handler->write("!SUCCESS : SELECT OK - rows returned : " . count($rows));
                    return $rows;
                } catch (PDOException $e)
                {
                    $file_handler->write("!ERROR : SELECT FAIL - ERROR : " .$e->getMessage());
                }

            } else
                $file_handler->write("!ERROR : Your SELECT query didn't pass because connection with database doesn't exist!");

        }

        function db_insert($array_values, $array_columns, $array_tables)
        {
            $file_handler = new Log();

            if($this->conn != null)
            {
                $array_val = implode(",", $array_values);
                $array_tab = implode(",", $array_tables);
                $array_col = implode(",", $array_columns);

                try{
                    $sql = $this->conn->prepare("INSERT INTO $array_tab($array_col) VALUES ($array_val)");
                    $sql->execute();
                    $file_handler->write("!SUCCESS : INSERT SUCCESS");
                } catch (PDOException $e)
                {
                    $file_handler->write("!ERROR : INSERT FAIL - ERROR : " .$e->getMessage());
                }

            } else
                $file_handler->write("!ERROR : Your INSERT query didn't pass because connection with database doesn't exist!");

        }

        function db_update($array_tables, $array_columns, $array_values, $array_where)
        {
            $file_handler = new Log();

			if($this->conn != null)
			{
				$array_tab = implode(",", $array_tables);
                $array_wh = implode(" AND ", $array_where);

                $newArraySet = array();

                for($i = 0; $i < count($array_columns); $i++)
                    $newArraySet[] = $array_columns[$i]."=".$array_values[$i];

                $makeString = implode(", ", $newArraySet);

                try{
                    $sql = $this->conn->prepare("UPDATE $array_tab SET $makeString WHERE $array_wh");
                    $sql->execute();
                    $file_handler->write("!SUCCESS : UPDATE SUCCESS");
                } catch (PDOException $e)
                {
                    $file_handler->write("!ERROR : UPDATE FAIL - ERROR : " .$e->getMessage());
                }

			} else
                $file_handler->write("!ERROR : Your UPDATE query didn't pass because connection with database doesn't exist!");
        }

		function db_delete($array_tables, $array_where)
		{
		    $file_handler = new Log();

			if($this->conn != null)
            {
                $array_tab = implode(",", $array_tables);
                $array_wh = implode(" AND ", $array_where);

                try{
                    $sql = $this->conn->prepare("DELETE FROM $array_tab WHERE $array_wh");
                    $sql->execute();
                    $file_handler->write("!SUCCESS : DELETE SUCCESS");
                }catch (PDOException $e)
                {
                    $file_handler->write("!ERROR : DELETE FAIL - ERROR : " .$e->getMessage());
                }

            } else
                $file_handler->write("!ERROR : Your DELETE query didn't pass because connection with database doesn't exist!");
		}

		function db_truncate($array_tables)
        {
            $file_handler = new Log();

            if($this->conn != null)
            {
                $array_tab = implode(",", $array_tables);

                try{
                    $sql = $this->conn->prepare("TRUNCATE TABLE $array_tab");
                    $sql->execute();
                    $file_handler->write("!SUCCESS : TRUNCATE SUCCESS");
                } catch (PDOException $e)
                {
                    $file_handler->write("!ERROR : TRUNCATE FAIL - ERROR : " .$e->getMessage());
                }

            } else
                $file_handler->write("!ERROR : Your TRUNCATE query didn't pass because connection with database doesn't exist!");

        }
}
?>