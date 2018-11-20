#!/bin/bash

DATE=`/bin/date '+%Y%m%d'`
mysqldump -u sion -h 127.0.0.1 -p --no-data sion > sion_${DATE}.sql

