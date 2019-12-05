#!/bin/bash

echo "GOOGLE_API_KEY=`cat $GOOGLE_API_KEY_FILE`" >> .env

exec "$@"
