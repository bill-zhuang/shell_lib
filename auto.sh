#!/bin/sh
echo '------------------git status-------------------'
git status
echo '------------------git stash--------------------'
git stash
echo '------------------git pull---------------------'
git pull
echo '------------------git push---------------------'
git push origin master
echo '------------------git stash pop----------------'
git stash pop