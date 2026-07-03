#!/bin/bash

set -a
source .env
set +a

DATE=$(date +%F)

mysqldump \
-u farmlink_user \
-p"$DB_PASS" \
farmlink_db \
> backups/db_$DATE.sql