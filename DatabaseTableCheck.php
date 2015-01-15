<?php

class Database_Table_Check
{
    private $_show_tables_sql;
    private $_describe_sql;

    private $_db_option;
    private $_target_adapter;
    private $_source_adapter;

    public function __construct()
    {
        $this->_show_tables_sql = 'SHOW TABLES';
        $this->_desribe_sql = 'DESC %s';

        $this->_db_option = [

        ];
        $this->_target_adapter = new PDO("mysql:host=localhost;dbname=bill", 'root', '123456', $this->_db_option);
        $this->_source_adapter = new PDO("mysql:host=localhost;dbname=bill", 'root', '123456', $this->_db_option);
    }

    public function run()
    {
        $target_tables_data = $this->_target_adapter->query($this->_show_tables_sql)->fetchAll();
        $source_tables_data = $this->_source_adapter->query($this->_show_tables_sql)->fetchAll();
        $target_tables = [];
        $source_tables = [];
        foreach ($target_tables_data as $target_tables_value)
        {
            $target_tables[] = $target_tables_value[0];
        }
        foreach ($source_tables_data as $source_tables_value)
        {
            $source_tables[] = $source_tables_value[0];
        }

        echo 'Table syn check start:' . "\n\n";
        foreach ($target_tables as $target_table)
        {
            $table_name = $target_table;
            //echo "\t" . 'Check table ' . $table_name . ':' . "\n\n";
            $sql_get_table_definition = sprintf($this->_desribe_sql, $table_name);
            if (in_array($table_name, $source_tables))
            {
                $target_table_definition = $this->_target_adapter->query($sql_get_table_definition)->fetchAll();
                $source_table_definition = $this->_source_adapter->query($sql_get_table_definition)->fetchAll();

                if (count($target_table_definition) != count($source_table_definition))
                {
                    echo "\t" . 'Table ' . $table_name . ' not syned!!!' . "\n\n";
                }
                else
                {
                    $flag = false;
                    foreach ($target_table_definition as $definition_key => $definition_content)
                    {
                        foreach ($definition_content as $field_key => $file_content)
                        {
                            if ($file_content != $source_table_definition[$definition_key][$field_key])
                            {
                                echo "\t" . 'Table ' . $table_name . ' not syned!!!' . "\n\n";
                                $flag = true;
                                break;
                            }
                        }

                        if ($flag)
                        {
                            break;
                        }
                    }

                    //echo "\t" . 'Check table ' . $table_name . ' finished.' . "\n\n";
                }
            }
            else
            {
                echo "\t" . 'Table ' . $table_name . ' not exist in source!!!' . "\n\n";
            }
        }

        echo 'Table syn check finished.';
    }
}

$check_table = new Database_Table_Check();
$check_table->run();