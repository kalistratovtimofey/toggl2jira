#!/usr/bin/env bash
GREEN="\e[32m"

echo -e "\e[1;97;49m Generating configuration files..."
cp .env .env.local
cp config/issue_key_map.yaml.dist config/issue_key_map.yaml
echo -e "\e[1;97;49m ✔ Done! \n"

#
echo -e "\e[1;97;49m Installing composer dependencies..."
composer install --ignore-platform-reqs
echo -e "\e[1;97;49m ✔ Done!"

echo -e "
Let's configure and start:
  - Add your credentials to .env.local file
  - Add aliases for your tasks in config/issue_key_map.yaml
  - Start transfer your logs with\e[0m \e[1;33;4;44mphp bin/console worklog:upload --from=2020-03-13 --to=2020-03-23\e[0m
"
