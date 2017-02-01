<?php

    interface iCRUD
    {
        function db_select($array_values, $array_tables, $array_where);
        function db_insert($array_values, $array_columns, $array_tables);
        function db_update($array_tables, $array_columns, $array_values, $array_where);
        function db_delete($array_tables, $array_where);
        function db_truncate($array_tables);
    }

?>