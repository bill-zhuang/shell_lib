<?php

class Database_Table_Check
{
    public function __construct()
    {

    }

    public function run()
    {
        $target_adapter = new PDO("mysql:host=localhost;dbname=bill", 'root', '123456', []);
        $source_adapter = new PDO("mysql:host=localhost;dbname=bill", 'root', '123456', []);
        
        $target_tables_data = $target_adapter->query('show tables')->fetchAll();
        $source_tables_data = $source_adapter->query('show tables')->fetchAll();
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
        $sql_table_definition_statement = 'desc %s';
        foreach ($target_tables as $target_table)
        {
            $table_name = $target_table;
            //echo "\t" . 'Check table ' . $table_name . ':' . "\n\n";
            $sql_get_table_definition = sprintf($sql_table_definition_statement, $table_name);
            if (in_array($table_name, $source_tables))
            {
                $target_table_definition = $target_adapter->query($sql_get_table_definition)->fetchAll();
                $source_table_definition = $source_adapter->query($sql_get_table_definition)->fetchAll();

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