#!/bin/sh
read -p 'Input commits numbers to generate patch: ' num
regex='^[1-9][0-9]*$'
if echo $num | grep -E $regex > /dev/null
then
	git format-patch --stdout -$num > all.patch
	echo 'patch generate successfully'
	#cp all.patch ../petbackend/
	#rm all.patch
	#echo 'patch generate successfully and copy to ../petbackend/ directory'
else 
	echo 'Error number input!'
fi