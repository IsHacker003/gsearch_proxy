#!/bin/bash
openssl req -noenc -x509 -newkey rsa:4096 -sha512 -keyout key.pem -days 4920 -out cert.pem -subj "/CN=*.google.com" -addext "subjectAltName=DNS:google.com,DNS:*.google.com"
