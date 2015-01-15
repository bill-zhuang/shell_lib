<?php

class SynTable
{
    private $_argv;
    private $_delete_sql;
    private $_create_sql;
    private $_infile_sql;
    private $_outfile_sql;

    private $_db_option;
    private $_target_adapter;
    private $_source_adapter;

    public function __construct($argv)
    {
        $this->_argv = $argv;
        $this->_delete_sql = 'DROP TABLE IF EXISTS `%s`';
        $this->_create_sql = 'SHOW CREATE TABLE `%s`';
        $this->_infile_sql = "LOAD DATA LOCAL INFILE '%s' INTO TABLE `%s` FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n'";
        $this->_outfile_sql = "SELECT * FROM `%s` INTO OUTFILE '%s' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n'";

        $this->_db_option = [
            PDO::MYSQL_ATTR_LOCAL_INFILE => true,
        ];
        $this->_target_adapter = new PDO("mysql:host=localhost;dbname=bill", 'root', '123456', $this->_db_option);
        $this->_source_adapter = new PDO("mysql:host=localhost;dbname=bill", 'root', '123456', $this->_db_option);
    }

    public function run()
    {
        echo 'Table syn start.' . "\n\n";

        $table_names = [];
        for ($i = 1, $count = count($this->_argv); $i < $count; $i++)
        {
            $table_names[] = $this->_argv[$i];
        }

        foreach ($table_names as $table_name)
        {
            $create_table_sql = sprintf($this->_create_sql, $table_name);
            $delete_table_sql = sprintf($this->_delete_sql, $table_name);
            $create_table_data = $this->_source_adapter->query($create_table_sql)->fetch();
            if (!empty($create_table_data))
            {
                echo "\t" . 'Syn table ' . $table_name . ' start: ' . "\n";

                try
                {
                    $this->_target_adapter->beginTransaction();

                    //delete & create table schema
                    $this->_target_adapter->exec($delete_table_sql);
                    $this->_target_adapter->exec($create_table_data['Create Table']);
                    //transfer table data by mysql outfile & infile command
                    $sql_data_path = $this->_getSaveDir() . $table_name . '.csv';
                    if (file_exists($sql_data_path))
                    {
                        unlink($sql_data_path);
                    }
                    $outfile_sql = sprintf($this->_outfile_sql, $table_name, $sql_data_path);
                    $this->_source_adapter->exec($outfile_sql);
                    if (file_exists($sql_data_path))
                    {
                        $infile_sql = sprintf($this->_infile_sql, $sql_data_path, $table_name);
                        $result = $this->_target_adapter->exec($infile_sql);
                        if ($result === false)
                        {
                            echo "\t" . 'Syn table ' . $table_name . ' failed, info: load data infile exec.' . "\n";
                        }
                    }
                    else
                    {
                        echo "\t" . 'Syn table ' . $table_name . ' failed, info: export table data failed.' . "\n";
                    }

                    $this->_target_adapter->commit();
                }
                catch (Exception $e)
                {
                    echo "\t" . 'Syn table ' . $table_name . ' error: ' . $e->getMessage() . "\n";
                    $this->_target_adapter->rollBack();
                }

                echo "\t" . 'Syn table ' . $table_name . ' finished.' . "\n\n";
            }
        }

        echo 'Table syn finished.';
    }

    private function _getSaveDir()
    {
        if (PHP_OS == 'WINNT')
        {
            $save_dir = 'C:' . DIRECTORY_SEPARATOR . 'Windows' . DIRECTORY_SEPARATOR . 'Temp' . DIRECTORY_SEPARATOR;
        }
        else
        {
            $save_dir = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        }

        return $save_dir;
    }
}

$syn_table = new SynTable($argv);
$syn_table->run();