#!/bin/sh
read -p 'Input table name to syn(names seperate by space):' names
regex='^\s+$'
if echo $names | grep -E $regex > /dev/null
then
	echo 'Error no names input!'
else 
	php -f SynTable.php $names
fi