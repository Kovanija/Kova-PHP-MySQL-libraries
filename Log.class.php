<?php


class Log{

    private $file_handler;

    function __construct()
    {
        $filename = "log.txt";
        $this->file_handler = fopen($filename, "a");

        if($this->file_handler == false)
        {
            print_r ("ERROR! System can't open file, check permissions!");
            exit();
        }
    }

    function __destruct()
    {

    }

    function write($statement)
    {

        if($this->file_handler != false)
        {
            $datum = (string)date('y-m-d')." | ".date("h:i:sa");

            //write datum and statement in log.txt
            fwrite($this->file_handler, "[" . $datum . "] " .$statement . "\n");
            fclose($this->file_handler);

            //read what you have written
            $this->file_handler = fopen("log.txt", "r");
            $array = array();
            while(!feof($this->file_handler))
                $array[] = fgets($this->file_handler);
            fclose($this->file_handler);

            //open new file, reverse data, and write in a database_log from top to bottom
            $this->file_handler = fopen("database_log.txt", "w");


            for($i = count($array)-2; $i >= 0; $i--)
            {
                fwrite($this->file_handler, $array[$i]. "\n");
            }

            fclose($this->file_handler);

        }
    }



}