#!/bin/bash

echo "APP_ENV=`cat $APP_ENV_FILE`" >> .env
echo "APP_SECRET=`cat $APP_SECRET_FILE`" >> .env
echo "DATABASE_URL=`cat $DATABASE_URL_FILE`" >> .env
echo "NEWSAPI=`cat $NEWSAPI_FILE`" >> .env
echo "GOOGLE_API_KEY=`cat $GOOGLE_API_KEY_FILE`" >> .env
echo "GOOGLE_JSON=`cat $GOOGLE_JSON_FILE`" >> .env

echo "{" >> `cat $GOOGLE_JSON_FILE`
echo "\"type\"\: \"`cat $JSON_TYPE_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "}" >> `cat $GOOGLE_JSON_FILE`

exec "$@"
