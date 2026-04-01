#!/bin/bash

# Copyright (C) 2026  IsHacker
#
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU Affero General Public License as published
#  by the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU Affero General Public License for more details.
#
#  You should have received a copy of the GNU Affero General Public License
#  along with this program.  If not, see <https://www.gnu.org/licenses/>.


openssl req -noenc -x509 -newkey rsa:4096 -sha512 -keyout key.pem -days 4920 -out cert.pem -subj "/CN=*.google.com" -addext "subjectAltName=DNS:google.com,DNS:*.google.com"
