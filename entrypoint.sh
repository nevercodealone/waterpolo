#!/bin/bash

echo "APP_ENV=`cat $APP_ENV_FILE`" >> .env
echo "APP_SECRET=`cat $APP_SECRET_FILE`" >> .env
echo "DATABASE_URL=`cat $DATABASE_URL_FILE`" >> .env
echo "NEWSAPI=`cat $NEWSAPI_FILE`" >> .env
echo "GOOGLE_API_KEY=`cat $GOOGLE_API_KEY_FILE`" >> .env
echo "GOOGLE_JSON=`cat $GOOGLE_JSON_FILE`" >> .env

echo "{" >> `cat $GOOGLE_JSON_FILE`
echo "\"type\": \"`cat $JSON_TYPE_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"project_id\": \"`cat $JSON_PROJECT_ID_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"private_key_id\": \"`cat $JSON_PRIVATE_KEY_ID_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"private_key\": \"`cat $JSON_PRIVATE_KEY_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"client_email\": \"`cat $JSON_CLIENT_EMAIL_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"client_id\": \"`cat $JSON_CLIENT_ID_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"auth_uri\": \"`cat $JSON_AUTH_URI_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"token_uri\": \"`cat $JSON_TOKEN_URI_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"auth_provider_x509_cert_url\": \"`cat $JSON_AUTH_PROVIDER_X509_CERT_URL_FILE`\"," >> `cat $GOOGLE_JSON_FILE`
echo "\"client_x509_cert_url\": \"`cat $JSON_CLIENT_X509_CERT_URL_FILE`\"" >> `cat $GOOGLE_JSON_FILE`
echo "}" >> `cat $GOOGLE_JSON_FILE`

exec "$@"
